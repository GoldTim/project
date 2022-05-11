<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

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
Route::prefix('admin')->group(function () {
});

Route::group([
    'domain' => 'admin.' . env('APP_URL', 'project.local.com'),
//    'middleware' => 'AdminMiddleware'
], function () {
    Route::match(['get', 'post'], 'login', [\App\Http\Controllers\Admin\LoginController::class, 'login']);
    Route::group([
        'prefix' => 'order',
        'controller' => \App\Http\Controllers\Admin\OrderController::class
    ], function () {
        Route::match(['get', 'post'], '/', 'index')->name('admin.order.list');
    });
});

Route::view('/', 'index');
