<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            //$table->foreign('user_id')->references('id')->on('users');
            $table->char('instrument_id',6);
            $table->char('order_id',6);
            //$table->foreign('instrument_id')->references('id')->on('instruments');
            $table->enum('order_type',array('buy', 'sell'));
            $table->decimal('open_price_local',15,8);
            $table->decimal('close_price_local',15,8)->nullable();
            $table->decimal('lot_size',15,8);
            $table->enum('leverage',array(5,10,20,50));
            $table->decimal('used_margin',15,8)->nullable();
            $table->decimal('stop_loss',15,8)->nullable();
            $table->decimal('take_profit',15,8)->nullable();
            $table->enum('closed',array('yes','no'))->default('no');
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
        Schema::dropIfExists('trades');
    }
}
