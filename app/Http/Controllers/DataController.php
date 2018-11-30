<?php

namespace App\Http\Controllers;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\Data;
use DB;
use Illuminate\Http\Request;

class DataController extends Controller
{


  public function create(Request $request)
  {
      $this->validate($request, [
          'instrument_id' => 'required',
          'bid' => 'required',
          'ask' => 'required',
          'price' => 'required',
      ]);
      $data = Data::create($request->all());

      return response()->json($data, 201);
  }

  public function getRates($instrumentID){
    return Data::where('instrument_id',$instrumentID)
            ->select('bid','ask', 'price')
            ->orderBy('id', 'desc')->first();;
  }

  public function getLatestRates(){
    $results= DB::select('SELECT t1.* FROM data t1
      WHERE t1.id = (SELECT t2.id FROM data t2
        WHERE t2.instrument_id = t1.instrument_id
        ORDER BY t2.id DESC LIMIT 1)');

        foreach($results as $k=>$result){
          $toReturn[$result->instrument_id]=array('bid'=>$result->bid,'ask'=>$result->ask);
        }

        return $toReturn;
  }

}
