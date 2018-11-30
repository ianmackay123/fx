<?php

namespace App\Http\Controllers;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use DB;
use App\Order;

use App\User;
use App\Http\Controllers\InstrumentController;
use Illuminate\Http\Request;

class OrderController extends Controller
{


  public function create(Request $request)
  {
    //check it's allowed to place the order
    // buy limit -> lower than current ASK price
    //sell limit -> higher than current BID price
    //buy Stop -> higher than current ask price
    //sell stop -> lower than current Bid price
    $instrument = new DataController();

    $currentPrices=$instrument->getRates($request->instrument_id);
    if($request->order_type=='buy'){
      $currentPrice=$currentPrices->ask;
    }else{
      $currentPrice=$currentPrices->bid;
    }
    if($request->trigger=="market"){
      $request['trigger_price']=$currentPrice;
    }

    $ok=0;

    if(
      ($request->trigger_price<=$currentPrice && $request->trigger=='limit' && $request->order_type=='buy')
      || ($request->trigger_price>=$currentPrice && $request->trigger=='limit' && $request->order_type=='sell')
      || ($request->trigger_price>=$currentPrice && $request->trigger=='stop' && $request->order_type=='buy')
      || ($request->trigger_price<=$currentPrice && $request->trigger=='stop' && $request->order_type=='sell')
      || ($request->trigger=='market')
      ){
      $ok=1;
    }

    //Check stop_loss and take_profit are valid
    //if buying, stop_loss  < trigger/current price < take profit
    //if selling stop_loss  > trigger/current price > take profit
    if (isset($request->stop_loss)){
      if($request->order_type=='buy'){
        if($request->stop_loss  >= $request->trigger_price){
          return response('Order cannot be added: stop_loss exceeds trigger price', 406);
        }
      }else{
        if($request->stop_loss  <= $request->trigger_price){
          return response('Order cannot be added: stop_loss exceeds trigger price', 406);
        }
      }
    }
    if (isset($request->take_profit)){
      if($request->order_type=='buy'){
        if($request->take_profit  <= $request->trigger_price){
          return response('Order cannot be added: take_profit exceeds trigger price', 406);
        }
      }else{
        if($request->take_profit  >= $request->trigger_price){
          return response('Order cannot be added: take_profit exceeds trigger price', 406);
        }
      }
    }



  if($ok==1){
    //order ok to be placed
    $instrument = new InstrumentController();

        $this->validate($request, [
          'user_id' => 'required',
          'instrument_id' => 'required',
          'order_type' => 'required | in:buy,sell',
          'trigger' => 'required | in:market,limit,stop',
          'trigger_price' => 'numeric | nullable',
          'take_profit' => 'numeric | nullable',
          'stop_loss' => 'numeric | nullable',
          'lot_size' => 'numeric',
          'leverage' => 'required | in:5,10,20,50',
          'expires_at' => 'date|after:now|nullable'
        ]);

        //transaction to add order, and subsequent trade etc if market order. if any fail, roll back and fail the order
        DB::beginTransaction();
        $order = Order::create($request->all());
        //if it's a market order, action immediately
        if($request->trigger=='market'){
          $request->trigger_price=$currentPrice;
          $trade=new TradeController();
          $tradeMade=$trade->create($order);
          //return response()->json($tradeMade, 201);
        }

        DB::commit();
        $orderReturned = Order::where('id',$order->id)->first();
        return response()->json($orderReturned, 201);
    }else{
      return response('Order cannot be added: Current price exceeds trigger price', 406);
    }
  }

  public function delete($id)
  {
    Order::findOrFail($id)->delete();
    return response('Deleted Successfully', 200);
  }

}
