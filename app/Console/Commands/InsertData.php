<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use App\Data;
use App\Instrument;
use GuzzleHttp\Client;
use OneForge\ForexQuotes\ForexDataClient;

class InsertData extends Command
{
  protected $signature = 'insert:data';
  protected $description = "insert latest instrument data regularly";

  public function handle()
  {
    try {
      //    $insert = Data::create();
      $client         = new ForexDataClient(env('FORGE_API', ''));
      //quotes         = $client->getQuotes(['GBPUSD']);
      //  $symbols        = $client->getSymbols();
      //  $conversion     = $client->convert('EUR', 'USD', 100);
      //  $quota          = $client->quota();
      //$market_is_open = $client->marketIsOpen();

        $quotes = $client->getQuotes([
                   'AUDCAD', 'AUDCHF',
                   'AUDJPY',
                   'AUDNZD',   'AUDUSD',
                   'CADCHF',   'CADJPY',
                   'CHFJPY',   'EURAUD',
                   'EURCAD',   'EURCHF',
                   'EURGBP',   'EURJPY',
                   'EURNZD',   'EURUSD',
                   'GBPAUD',   'GBPCAD',
                   'GBPCHF',   'GBPJPY',
                   'GBPNZD',   'GBPUSD',
                   'NZDCAD',   'NZDCHF',
                   'NZDJPY',   'NZDUSD',
                   'USDCAD',   'USDCHF',
                   'USDJPY']);

        foreach($quotes as $quote){
          $item = array(
          'instrument_id' => $quote['symbol'],
          'bid' => $quote['bid'],
          'ask' => $quote['ask'],
          'price' => $quote['price'],
        );
        Data::create($item);
        }
      return 'ok';
    } catch (Exception $e) {
      $this->error("An error occurred");

    }
  }

}
