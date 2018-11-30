<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function upIGNORE()
     {
         Schema::create('data', function (Blueprint $table) {
             $table->increments('id');
             $table->char('instrument_id',6);
            //$table->foreign('instrument_id')->references('id')->on('instruments');
             $table->decimal('bid', 10, 6);
             $table->decimal('ask', 10, 6);
             $table->decimal('price', 10, 6);
             $table->timestamps();
         });
     }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function downIGNORE()
    {
      //Currently I want to keep the data table always
        Schema::dropIfExists('data');
    }
}
