<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use App\Data;
use App\Trade;
use App\User;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\InstrumentController;

class CheckTrades extends Command
{
  protected $signature = 'check:trades';
  protected $description = "check any trades that have hit stop or limit";

  public function handle()
  {
    try {

      $tradeController= new TradeController();
      $latestPrices=DB::select('SELECT t1.* FROM data t1
        WHERE t1.id = (SELECT t2.id FROM data t2
          WHERE t2.instrument_id = t1.instrument_id
          ORDER BY t2.id DESC LIMIT 1)');

          foreach($latestPrices as $k=>$instrument){
            $ask=$instrument->ask;
            $bid=$instrument->bid;
            //get orders to action for buy (ask)
            $allTrades=Trade::
            where('closed','no')
            ->where('instrument_id', $instrument->instrument_id)
            ->where(function ($query)
            use ($ask,  $bid)
            {
              $query->where(function ($query)
              use ( $bid)
              {
                $query->where('order_type', '=', 'buy')
                ->where(function ($query)
                use ($bid)
                {
                  $query->where('stop_loss', '>=', $bid )
                  ->orwhere('take_profit', '<=', $bid);
                });
              })

              ->orwhere(function ($query)
              use ($ask)
              {
                $query->where('order_type', '=', 'sell')
                ->where(function ($query)
                use ($ask)
                {
                  $query->where('stop_loss','<=',$ask)
                  ->orwhere('take_profit','>=', $ask);
                });
            });
          })
            ->get();

            foreach($allTrades->all() as $i=>$trade){
              //trade limit has been hit, we need to close this now
              echo $trade->id;
                $tradeController->close($trade->id);
              }
            }

          return 'ok';
        } catch (Exception $e) {
          $this->error("An error occurred");

        }
      }

    }
