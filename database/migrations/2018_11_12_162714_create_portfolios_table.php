<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortfoliosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('portfolios', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('user_id')->unsigned();
          //$table->foreign('user_id')->references('id')->on('users');
          $table->char('instrument_id',6);
          //$table->foreign('instrument_id')->references('id')->on('instruments');
          $table->decimal('open_price_local',15,8);
          $table->decimal('lot_size',15,8);
          $table->enum('leverage',array(5,10,20,50));
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portfolios');
    }
}
