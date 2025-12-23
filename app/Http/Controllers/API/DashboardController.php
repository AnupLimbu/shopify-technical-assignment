<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\Shop;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $shopDomain = $request->query('shop');
        $shop=Shop::where('shop_domain', $shopDomain)->firstOrFail();
        if (! $shop) {
            return response()->json(['message' => 'Shop not found or does not exist!'], 400);
        }

        $totalProducts = Product::where('shop_id', $shop->id)->count();
        $totalCollections = ProductCollection::where('shop_id', $shop->id)->count();

        $lastProductSync = Product::where('shop_id', $shop->id)->max('synced_at');
        $lastCollectionSync = ProductCollection::where('shop_id', $shop->id)->max('synced_at');
        $lastSync = collect([$lastProductSync, $lastCollectionSync])->filter()->max();

        $shopUpdatedAt = $shop->updated_at;
        if (!$lastSync && $shopUpdatedAt) {
            $lastSync = $shopUpdatedAt;
        }
        return response()->json([
            'total_products' => (int) $totalProducts,
            'total_collections' => (int) $totalCollections,
            'last_sync' => $lastSync ? $lastSync : null,
        ]);
    }
}
