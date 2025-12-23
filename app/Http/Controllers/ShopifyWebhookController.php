<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ShopifyWebhookController extends Controller
{
    public function products(Request $request)
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256') ?? '';
        $topic = $request->header('X-Shopify-Topic') ?? '';
        $shopDomain = $request->header('X-Shopify-Shop-Domain') ?? '';

        $rawBody = $request->getContent();

        // Verify HMAC
        $secret = config('services.shopify.secret') ?: env('SHOPIFY_API_SECRET');
        $calculated = base64_encode(hash_hmac('sha256', $rawBody, $secret, true));
        if (!hash_equals($calculated, $hmacHeader)) {
            Log::warning('Shopify webhook HMAC verification failed', [
                'shop' => $shopDomain,
                'topic' => $topic,
            ]);
            return response('Invalid HMAC', 401);
        }

        // Parse payload
        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            Log::warning('Invalid webhook payload', ['shop' => $shopDomain, 'topic' => $topic]);
            return response('Bad payload', 400);
        }

        // Map identifiers: try to use shop_id FK if your products table uses it, otherwise use shop_domain
        $shop = Shop::where('shop_domain', $shopDomain)->first();


        $shopifyId = isset($payload['id']) ? (string)$payload['id'] : null;
        if (! $shopifyId) {
            // Some webhooks may send a different structure; log and return
            Log::warning('Webhook missing product id', ['shop' => $shopDomain, 'topic' => $topic, 'payload' => $payload]);
            return response('Missing product id', 400);
        }

        $where = [['shop_id' => $shop->id], ['shopify_id' => $shopifyId]];

        try {
            if (in_array($topic, ['products/create', 'products/update'])) {
                // Upsert product
                $attrs = [
                    'title' => $payload['title'] ?? ($payload['product_title'] ?? ''),
                    'body_html' => $payload['body_html'] ?? null,
                    'status' => $payload['status'] ?? 'active',
                    'published_at' => isset($payload['published_at']) && $payload['published_at'] ? Carbon::parse($payload['published_at']) : null,
                    'synced_at' => now(),
                ];



                Product::updateOrCreate($where, $attrs);

                Log::info('Processed product webhook upsert', ['shop' => $shopDomain, 'id' => $shopifyId, 'topic' => $topic]);
            } elseif ($topic === 'products/delete') {
                // Delete product locally
                $deleted = Product::where($where)->delete();
                Log::info('Processed product webhook delete', ['shop' => $shopDomain, 'id' => $shopifyId, 'deleted' => (bool)$deleted]);
            } else {
                // Unknown topic for this endpoint — respond 200 to avoid retries, but log
                Log::info('Ignored webhook topic', ['shop' => $shopDomain, 'topic' => $topic]);
            }
        } catch (\Throwable $e) {
            Log::error('Error processing product webhook', [
                'shop' => $shopDomain,
                'topic' => $topic,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            // Return 500 so Shopify will retry later
            return response('Webhook processing error', 500);
        }


        return response('OK', 200);
    }
}
