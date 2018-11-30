<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use App\Data;
use App\Order;
use App\Http\Controllers\TradeController;

class CheckOrders extends Command
{
  protected $signature = 'check:orders';
  protected $description = "check any orders that should be triggered";

  public function handle()
  {
    try {
      //2) get current price for all instruments
      //3)for each instrument,
      //loops through orders
      //if trigger price is lower than current and order type = buy -> implement order

      //if trigger price is higher than current and order type = sell - implement order
      //  next instrument

      $trade= new TradeController();
      $latestPrices=DB::select('SELECT t1.* FROM data t1
        WHERE t1.id = (SELECT t2.id FROM data t2
          WHERE t2.instrument_id = t1.instrument_id
          ORDER BY t2.id DESC LIMIT 1)');

          foreach($latestPrices as $k=>$instrument){
            $b=$instrument->ask;
            $d=$instrument->bid;
            //get orders to action for buy (ask)
            $allOrders=Order::
            where('state','pending')
            ->where('instrument_id', $instrument->instrument_id)
            ->where(function ($query)
            use ($b,  $d)
            {
              $query->where(function ($query2)
              use ( $b)
              {
                $query2->where('order_type', '=', 'buy')
                ->where('trigger_price', '>=', $b)
                ->where('trigger', '=', 'limit');
              })
              ->orwhere(function ($query3)
              use ($d)
              {
                $query3->where('order_type', '=', 'sell')
                ->where('trigger_price', '<=', $d)
                ->where('trigger', '=', 'limit');
              })
              ->orwhere(function ($query3)
              use ($b)
                {
                  $query3->where('order_type', '=', 'buy')
                  ->where('trigger_price', '<=', $b)
                  ->where('trigger', '=', 'stop');
              })
              ->orwhere(function ($query3)
              use ( $d)
              {
                $query3->where('order_type', '=', 'sell')
                ->where('trigger_price', '>=', $d)
                ->where('trigger', '=', 'stop');
              });
            })
            ->get();

            foreach($allOrders->all() as $i=>$order){
              //check if expired - if yes, update state
              //check order hasn't expired
              if(isset($order->expires_at) && strtotime($order->expires_at)<strtotime("now")){
                //expired

                Order::where('id',$order->id)
                  ->update(['state' =>'expired']);
              }else{

                DB::beginTransaction();
                //Make the trade
                $trade->create($order);
                DB::commit();
              }
            }

          }

          return 'ok';
        } catch (Exception $e) {
          $this->error("An error occurred");

        }
      }

    }
