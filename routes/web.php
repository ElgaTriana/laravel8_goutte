<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers;

use App\Http\Controllers\WelcomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix'=>'scrapping'], function(){
    Route::get('idntimes', [WelcomeController::class, 'idntimes']);
    Route::get('idntimestahap2', [WelcomeController::class, 'idntimestahap2']);
    Route::get('antaranews', [WelcomeController::class, 'antaranews']);
    Route::get('okezone', [WelcomeController::class, 'okezone']);
    Route::get('sindonews', [WelcomeController::class, 'sindonews']);
    Route::get('inewsid', [WelcomeController::class, 'inewsid']);
    Route::get('tes', [WelcomeController::class, 'tes']);
    Route::get('suara', [WelcomeController::class, 'suara']);
});