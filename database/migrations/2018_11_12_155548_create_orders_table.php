<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('user_id')->unsigned();
          //$table->foreign('user_id')->references('id')->on('users');
          $table->char('instrument_id',6);
          //$table->foreign('instrument_id')->references('id')->on('instruments');
          $table->enum('order_type',array('buy', 'sell'));
          $table->enum('trigger',array('market', 'limit', 'stop'));
          $table->decimal('trigger_price',15,8)->nullable();
          $table->decimal('lot_size',15,8);
          $table->enum('leverage',array(5,10,20,50));
          $table->decimal('stop_loss',15,8)->nullable();
          $table->decimal('take_profit',15,8)->nullable();
          $table->dateTime('expires_at')->nullable();
          $table->enum('state',array('complete','pending','expired'))->default('pending');
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
        Schema::dropIfExists('orders');
    }
}
