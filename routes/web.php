<?php

 use App\Http\Controllers\Admin\LoginController as AdminLogin;
use App\Http\Controllers\Admin\OrderController as AdminOrder;
use App\Http\Controllers\NotifyController as Notify;
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
], function () {
    Route::match(['get', 'post'], 'login', [AdminLogin::class, 'login']);
    Route::group([
        'prefix' => 'order',
        'controller' => AdminOrder::class
    ], function () {
        Route::match(['get', 'post'], '/index', 'index')->name('admin.order.list');
    });
});
Route::prefix('notify')->group(function () {
    Route::any('weChatPay', [Notify::class, 'weChatPay'])->name('notify.weChatPay');
});

Route::view('/', 'index');
