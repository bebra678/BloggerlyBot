<?php

use Illuminate\Support\Facades\Route;
use SleepingOwl\Admin\Facades\Admin;
use App\Http\Controllers\TelegramController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Route::get('message', [TelegramController::class, 'index'])->name('admin.message.index');
//
Route::post('message/send', [TelegramController::class, 'sendBroadcast'])->name('admin.message.send');

