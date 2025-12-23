<?php

use App\Http\Controllers\ShopifyAuthController;

use App\Http\Controllers\ShopifyWebhookController;
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
    return view('app'); // the blade that contains the <div id="app" ...> and bootstraps React
})->name('app');

Route::get('/app/{any?}', function () {
    return view('app'); // the blade that contains the <div id="app" ...> and bootstraps React
})->where('any', '.*');

// Shopify OAuth entry points
Route::get('/shopify/install', [ShopifyAuthController::class, 'install'])->name('shopify.install');
Route::get('/shopify/callback', [ShopifyAuthController::class, 'callback'])->name('shopify.callback');






