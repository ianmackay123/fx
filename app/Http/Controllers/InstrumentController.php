<?php

namespace App\Http\Controllers;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\Instrument;
use Illuminate\Http\Request;

class InstrumentController extends Controller
{


  public function isOpen($id)
  {
      if( Instrument::where('id',$id)->value('active')>0){
        return true;
      }else{
        return false;
      }
  }

  public function convertToUSD($currency, $buyOrSell, $amount=1){
    if($currency!='USD'){
      //have to work out if USD is primary or secondary (primary except list set)
      $peckOrder=array('EUR','GBP' ,'AUD' ,'NZD' );
      $inverter=0; //i.e. we need the inverse of the amount returned
      if(in_array($currency,$peckOrder)){
        $lookupInstrumentID=$currency.'USD';
      }else{
        $lookupInstrumentID='USD'.$currency;
        $inverter=1;
      }

      $data= new DataController();
      $rates= $data->getRates($lookupInstrumentID);

      if($buyOrSell=='sell'){
        if(  $inverter==1){
          return 1/$rates->bid*$amount;
        }else{
          return $rates->bid*$amount;
        }
      }else{
        if(  $inverter==1){
          return 1/$rates->ask*$amount;
        }else{
          return $rates->ask*$amount;
        }
      }
    }else{
      return $amount;
    }
  }



}
