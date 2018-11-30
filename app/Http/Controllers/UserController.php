<?php

namespace App\Http\Controllers;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\User;
use App\Trade;
use Illuminate\Http\Request;

class UserController extends Controller
{


  public function create(Request $request)
  {
      $this->validate($request, [
          'name' => 'required',
          'email' => 'required'
      ]);
      $data = User::create($request->all());

      return response()->json($data, 201);
  }

  public function showOneUser($id)
  {
      return response()->json(User::find($id));
  }

  //lot_size should be in USD
  public function userHasEnoughCash($id,$lot_size)
  {
      $user= User::find($id)->first();
      $userFunds=$user->balance-$user->used_margin;
      if($userFunds>=$lot_size){
        return true;
      }else{
        return false;
      }
  }

  public function getEquity($id){
    //Equity=balance +Credit +(floating Profit -floating losses)
    return User::where('id', $id)
      ->select('id','balance', 'used_margin')
      ->get();

  }



  public function getFloatingProfit($id){
    //This works out whether the user is positive or negative on open positions
    $dataController=new DataController();
    $latestPrices=$dataController->getLatestRates();
    //print_r( $latestPrices);
    //return;
    $openTrades=Trade::where('closed', 'no')
                ->where('user_id', $id)
                ->get();

      $profit=array();
     foreach($openTrades as $trade){
       if($trade->order_type=='buy'){
         $profit[]=100000 * $trade->lot_size * $trade->leverage * ($latestPrices[$trade->instrument_id]['bid'] - $trade->open_price_local );
       }else{
         $profit[]=-100000 * $trade->lot_size * $trade->leverage * ($latestPrices[$trade->instrument_id]['ask'] - $trade->open_price_local );

       }
     }
    return round(array_sum($profit),2);
  }

}
