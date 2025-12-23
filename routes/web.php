<?php

use App\Http\Controllers\ShopifyAuthController;
use Illuminate\Support\Facades\Route;

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
    return view('app');
})->name('app');

// Shopify OAuth entry points
Route::get('/shopify/install', [ShopifyAuthController::class, 'install'])->name('shopify.install');
Route::get('/shopify/callback', [ShopifyAuthController::class, 'callback'])->name('shopify.callback');
