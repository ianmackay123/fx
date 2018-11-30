<?php

namespace App\Http\Controllers;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use DB;
use App\Trade;
use App\Portfolio;
use App\Http\Controllers\InstrumentController;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{


  public function create(Trade $trade)
  {
//plan here is to separate portfolio by leverage.
//find the row with the same Instrument pair and leverage
// adjust lot_size by adding new trade



    if($trade->order_type=='sell'){
      $trade->lot_size=$trade->lot_size*-1;
    }
    if( $portfolioUpdate=Portfolio::where('leverage',$trade->leverage)
        ->where('instrument_id',$trade->instrument_id)
        ->where('user_id',$trade->user_id)
        ->first())
        {

        $portfolioUpdate->increment('lot_size',$trade->lot_size);
        if($portfolioUpdate->lot_size==0){
          Portfolio::where('id', $portfolioUpdate->id)->delete();
        }
    }else{
      //create new record
      $request=new Request(['user_id' => $trade->user_id,
      'instrument_id' => $trade->instrument_id,
      'lot_size'=> $trade->lot_size,
      'open_price_local'=> $trade->open_price_local,
      'leverage'=> $trade->leverage,
      ]);

      $this->validate($request, [
        'user_id' => 'required',
        'instrument_id' => 'required',
        'lot_size' => 'numeric',
        'open_price_local' => 'numeric',
        'leverage' => 'required | in:5,10,20,50',
      ]);
      $portfolio = Portfolio::create($request->all());
    }

}

public function delete($id)
{
  Portfolio::findOrFail($id)->delete();
  return response('Deleted Successfully', 200);
}

public function getTotalForRow($id)
{
  $total= Portfolio::findOrFail($id) ;
  return $total->lot_size * $total->leverage;
}

public function getTotalForUser($userId)
{
  $allRows=Portfolio::where('user_id',$userId)->get();
  $total=0;
  foreach($allRows as $k=>$row){
    $total+=$row->lot_size * $row->leverage;
  }
  return $total;
}

}
