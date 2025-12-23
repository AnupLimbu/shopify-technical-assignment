<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $shopDomain = $request->query('shop');
        $shop=Shop::where('shop_domain', $shopDomain)->firstOrFail();
        if (! $shop) {
            return response()->json(['message' => 'Shop not found or does not exist!'], 400);
        }

        $q = $request->query('q', null);
        $status = $request->query('status', null); // active|draft|archived or null


        $query = Product::where('shop_id', $shop->id);

        if ($q) {
            $query->where('title', 'like', '%' . $q . '%');
        }

        if ($status) {
            $query->where('status', $status);
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
