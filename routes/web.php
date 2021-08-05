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
    Route::get('deskripsiokezone',[WelcomeController::class, 'deskripsiokezone']);
    Route::get('deskripsisindonews', [WelcomeController::class, 'deskripsisindonews']);
    Route::get('deskripsiinewsid', [WelcomeController::class, 'deskripsiinewsid']);
    Route::get('getdataidntimes', [WelcomeController::class, 'getdataidntimes']);
    Route::get('idntimesnih', [WelcomeController::class, 'idntimesnih']);
    Route::get('liputan6', [WelcomeController::class, 'liputan6']);
    Route::get('detik', [WelcomeController::class, 'detik']);
    Route::get('kompas', [WelcomeController::class, 'kompas']);
    Route::get('idxchannel', [WelcomeController::class, 'idxchannel']);
    
    Route::get('tokped', [WelcomeController::class, 'tokped']);
    Route::get('tokped-detail-produk', [WelcomeController::class, 'tokped_detail_produk']);
    Route::get('tokped-produk', [WelcomeController::class, 'tokped_produk']);

    Route::get('homepagedetik', [WelcomeController::class, 'homepagedetik']);
    Route::get('homepagekompas', [WelcomeController::class, 'homepagekompas']);
    Route::get('homepagetribun', [WelcomeController::class, 'homepagetribun']);
    Route::get('homepageliputan6', [WelcomeController::class, 'homepageliputan6']);
    Route::get('homepageinewsid', [WelcomeController::class, 'homepageinewsid']);
    Route::get('homepagesindonews', [WelcomeController::class, 'homepagesindonews']);
    Route::get('homepageokezone', [WelcomeController::class, 'homepageokezone']);
});