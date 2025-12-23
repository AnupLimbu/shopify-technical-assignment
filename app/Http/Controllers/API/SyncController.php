<?php

namespace App\Http\Controllers\API;

use App\Models\Shop;
use App\Services\ShopifySyncService;
use Illuminate\Http\Request;

class SyncController
{
    protected ShopifySyncService $syncService;

    public function __construct(ShopifySyncService $syncService)
    {
        $this->syncService = $syncService;
    }
    public function sync(Request $request)
    {
        $shopDomain = $request->input('shop');

        if ($shopDomain) {
            $shop = Shop::where('shop_domain', $shopDomain)->first();
            if (!$shop) {
                return response()->json(['message' => 'Shop not found'], 404);
            }

            $result = $this->syncService->syncShop($shop);
            return response()->json([
                'message' => 'Synced shop: ' . $shopDomain,
                'result' => $result,
            ]);
        }
    }
}
