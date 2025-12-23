<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\SyncController;
use App\Http\Controllers\ShopifyWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
$version=1;
Route::get('/dashboard-summary', [DashboardController::class, 'summary']);
Route::get('/products', [ProductController::class, 'index']);
Route::post('/sync', [SyncController::class, 'sync']);




Route::post('shopify/webhooks/products', [ShopifyWebhookController::class, 'products'])->name('shopify.webhooks.products');
