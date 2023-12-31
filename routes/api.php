<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AffOfferController;
use App\Http\Controllers\Api\AffReportController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/',[AffOfferController::class,'index']);
Route::get('/offers/{aff_id}/{key}',[AffOfferController::class,'offer']);
Route::get('/transaction/{aff_id}/{key}',[AffOfferController::class,'transaction']);
