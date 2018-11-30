<?php

namespace App\Http\Controllers;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use DB;
use App\Trade;
use App\Order;
use App\User;
use App\Data;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\DataController;

use Illuminate\Http\Request;

class TradeController extends Controller
{


  public  function create(Order $order)
  {
    if($order->state=='pending'){
      // check market is open
      $instrument = new InstrumentController();
      if($instrument->isOpen($order->instrument_id)){
        /* check user has enough funds
            Info I need:
              lot_size, (1 lot=100,000 in local currency)
              leverage
              User balance

            1) lot_size/leverage*100,000 = amount of local currency the broker holds
            2) convert this to USD
                buy=ask, sell=bid
            3) check if this is more than current balance held - if it is, cancel trade
          */
          $localAmt=$order->lot_size/$order->leverage*100000;
          $rootCurrency=substr($order->instrument_id, 0, 3);
          $totalUSD=$instrument->convertToUSD($rootCurrency, $order->order_type, $localAmt);

        $user=new UserController();

        if($user->userHasEnoughCash($order->user_id,$totalUSD)){

          //create a request object with appropriate info
          $request=new Request(['user_id' => $order->user_id,
          'instrument_id' => $order->instrument_id,
          'order_id' => $order->id,
          'order_type' => $order->order_type,
          'lot_size'=> $order->lot_size,
          'open_price_local' => $order->trigger_price,
          'leverage'=> $order->leverage,
          'take_profit'=> $order->take_profit,
          'stop_loss'=> $order->stop_loss,
          'used_margin'=> $totalUSD,
          ]);

        $this->validate($request, [
          'user_id' => 'required',
          'instrument_id' => 'required',
          'order_type' => 'required | in:buy,sell',
          'open_price_local' => 'numeric | nullable',
          'lot_size' => 'numeric',
          'leverage' => 'required | in:5,10,20,50',
          'take_profit' => 'numeric | nullable',
          'stop_loss' => 'numeric | nullable',
          'used_margin'=>'numeric | nullable',
        ]);

        $trade = Trade::create($request->all());

        //update order state to completed
        if(true==Order::find($order->id)->update(['state' =>'complete'])){


          //add margin to user
          $marginDetails=$this->findMarginUsed($order->instrument_id, $order->leverage);

          //get all other open trades for this instrument and leverage level
          //work out the amount of overall margin, and whether sell>buy

          if($trade->order_type=='buy' && $marginDetails['buyOrSell']=='buy'){
            User::find($trade->user_id)->increment('used_margin', min($marginDetails['marginDelta'], $trade->used_margin));
          }elseif($trade->order_type=='sell' && $marginDetails['buyOrSell']=='sell'){
              User::find($trade->user_id)->increment('used_margin',min($marginDetails['marginDelta'], $trade->used_margin));
          }


          if(0<User::find($trade->user_id)->balance){
            //now add to portfolio
            $portfolio = new PortfolioController();
            $portfolio->create($trade);

            return response()->json($trade, 201);
          }else{
            DB::rollback();
            return response('insufficient funds', 406);
          }

        }else{
          DB::rollback();
          return response('failed to set order as complete', 406);
        }
      }else{
        DB::rollback();
        return response('insufficient funds', 406);
      }
    }else{
      //market not open
      DB::rollback();
      return response('market closed', 406);
    }
  }else{
    DB::rollback();
    return response('order already filled');
  }
}


public function close($id)
{

  $trade=Trade::where('id',$id)
  ->where('closed','no')
  ->firstOrFail();

//print_r($trade->all());

  $dataController=new DataController();
  $prices=$dataController->getRates($trade->instrument_id);
  if($trade->order_type=='buy'){
    $price=$prices->bid;
    $closeType='sell';
    $plusMinus=1;  //use to swap Sell orders to negative if price goes up
  }else{
      $price=$prices->ask;
      $closeType='buy';
      $plusMinus=-1;  //use to swap Sell orders to negative if price goes up
  }

  DB::beginTransaction();
  //Close the trade:

  $localAmt=$plusMinus*$trade->leverage*$trade->lot_size*100000*($price-$trade->open_price_local);
  //^if positive, made a profit in the local currency
  //convert to USD
  $rootCurrency=substr($trade->instrument_id, 3, 6);
  $instrument = new InstrumentController();
  $totalUSD=$instrument->convertToUSD($rootCurrency, $closeType, $localAmt);
//die();

//TODO: Large Sell positions are making money when they should be losing it

  User::where('id',$trade->user_id)
    ->increment('balance' ,$totalUSD);








  //2)check if we need to update used margin

  $tradeController=new TradeController();
  $marginDetails=$tradeController->findMarginUsed($trade->instrument_id, $trade->leverage);
  if($trade->order_type=='buy' && $marginDetails['buyOrSell']=='buy'){
    User::find($trade->user_id)->increment('used_margin', -min($marginDetails['marginDelta'], $trade->used_margin));
  }elseif($trade->order_type=='sell' && $marginDetails['buyOrSell']=='sell'){
      User::find($trade->user_id)->increment('used_margin',-min($marginDetails['marginDelta'], $trade->used_margin));
  }


  //close Trade:
  Trade::where('id',$trade->id)
    ->update(['closed' =>'yes', 'close_price_local' => $price]);

//die();
  //check if we need to update used margin

  DB::commit();
  return 'ok';
}

public function findMarginUsed($instrument, $leverage)
{
  /*Note for when I come back here in the future and monder what the hell this code is doing:
  The used margin for a user is the larger of the sum of all buys v sells for a given leverage
  if I keep buying, the used margin goes up each time.
  If I then start opening sell positions (rather than closing) at the same leverage, the used marging doesn't change
  until the total of all sell positions> buy positions.
  Margin is always positive (i.e. sell margins do not cancel out buy margins).
*/

  $openTrades=Trade::selectRaw('sum(used_margin) as sum, order_type')
    ->where('closed','no')
    ->where('instrument_id',$instrument)
      ->where('leverage',$leverage)
    ->groupBy('order_type')
    ->orderBy('order_type', 'asc')
    ->get();

    $returnData=array();
    //if there are two rows, buy is 0th row and sell is 1st row
    //otherwise just send back the single result

    //marginDelta is the difference between buy and sell margin pots (we use this when closing a trade
    // to see if we need to reduce it)
    switch (count($openTrades)){
      case 0:
        //nominally return buy, but 0 margin
        $returnData['buyOrSell']='buy';
        $returnData['marginDelta']=0;
        return $returnData;
        break;
      case 1;
        //return the solitary sum of either buy or sell
        $returnData['buyOrSell']=$openTrades[0]->order_type;
        $returnData['marginDelta']=$openTrades[0]->sum;
        return $returnData;
        break;
      case 2:
        //we have buy and sell trades, need to work out which has more margin, and return diff in amts
        if($openTrades[0]->sum>=$openTrades[1]->sum){
          //if buy>=sell
          $returnData['buyOrSell']='buy';
          $returnData['marginDelta']=$openTrades[0]->sum-$openTrades[1]->sum;
        }else{
          $returnData['buyOrSell']='sell';
          $returnData['marginDelta']=$openTrades[1]->sum-$openTrades[0]->sum;
        }
        return $returnData;
    }
}




public function delete($id)
{
  Trade::findOrFail($id)->delete();
  return response('Deleted Successfully', 200);
}

}
