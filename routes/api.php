<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'domain' => 'shop.' . env('APP_URL', 'project.local.com')
], function () {
    Route::prefix('order')->controller(\App\Http\Controllers\OrderController::class)->group(function () {
        Route::post('submit', 'submitOrder')->name('api.order.submit');
        Route::post('pay', 'payOrder')->name('api.order.pay');
        Route::post('cancel', 'cancelOrder')->name('api.order.cancel');
    });
});
