<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Instrument;
use App\Order;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $this->call('OrdersTableSeeder');
         $this->call('UsersTableSeeder');
         $this->call('InstrumentsTableSeeder');

    }
}

class UsersTableSeeder extends Seeder {

    public function run()
    {
        DB::table('users')->delete();

        User::create(
          ['name' => str_random(10),
          'email' => str_random(10).'gmail.com']);
    }
}

class InstrumentsTableSeeder extends Seeder {



    public function run()
    {
      $allPairs=array('EURGBP','EURAUD','EURNZD','EURUSD','EURCAD','EURCHF','EURJPY',
                      'GBPAUD','GBPNZD','GBPUSD','GBPCAD','GBPCHF','GBPJPY',
                      'AUDNZD','AUDUSD','AUDCAD','AUDCHF','AUDJPY',
                      'NZDUSD','NZDCAD','NZDCHF','NZDJPY',
                      'USDCAD','USDCHF','USDJPY',
                      'CADCHF','CADJPY',
                      'CHFJPY'
                    );
        DB::table('instruments')->delete();
        foreach($allPairs as $k=>$pair){
          Instrument::create(
            ['id'=>$pair,
              'primary' => substr($pair, 0, 3),
              'secondary' => substr($pair, 3, 6),
              'active' => 1,
          ]
        );
      }
    }
}

class OrdersTableSeeder extends Seeder {

    public function run()
    {
        DB::table('orders')->delete();

        Order::create(
          ['user_id' => 1,
          'instrument_id' => 'GBPUSD',
          'order_type' => 'buy',
          'trigger' => 'market',
          'trigger_price' =>1,
          'lot_size' =>12,
          'leverage' => '5',
        ]);
    }
}
