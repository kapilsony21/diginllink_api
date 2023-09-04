<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AffOfferController;
use App\Http\Controllers\Api\AffReportController;
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

Route::get('/', function () {
    return 'ok';
});

Route::get('/offers/{aff_id}/{key}',[AffOfferController::class,'offer']);
