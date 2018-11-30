<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('data', 'DataController@index');


$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
  //users section
  $router->get('user/{id}', ['uses' => 'UserController@showOneUser']);
  $router->get('user/getFloatingProfit/{id}', ['uses' => 'UserController@getFloatingProfit']);
  $router->get('user/getEquity/{id}', ['uses' => 'UserController@getEquity']);

  //$router->post('fx', ['uses' => 'FxController@create']);
  //$router->delete('fx/{id}', ['uses' => 'FxController@delete']);
  //$router->put('fx/{id}', ['uses' => 'FxController@update']);

  //orders section
  $router->post('order', ['uses' => 'OrderController@create']);
  $router->delete('order/{id}', ['uses' => 'OrderController@delete']);


  $router->get('instrument/open/{id}', ['uses' => 'InstrumentController@isOpen']);


  $router->post('data', ['uses' => 'DataController@create']);


  $router->post('trade/close/{id}', ['uses' => 'TradeController@close']);

  $router->get('portfolio/totalForUser/{userId}', ['uses' => 'PortfolioController@getTotalForUser']);
  $router->get('portfolio/totalForRow/{id}', ['uses' => 'PortfolioController@getTotalForRow']);


});
