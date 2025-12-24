<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyWebhookService
{
    protected $apiVersion;

    public function __construct()
    {
        $this->apiVersion = env('services.shopify.api_version', '2025-10');
    }

    public function registerWebhooks(Shop $shop): array
    {
        $webhooks = [
            'products/create',
            'products/update',
            'products/delete',
        ];

        $results = [];
        $url = "https://{$shop->shop_domain}/admin/api/{$this->apiVersion}/webhooks.json";
        $webhookUrl = config('app.url') . '/api/webhooks/shopify/products';

        foreach ($webhooks as $topic) {
            try {
                $response = Http::withHeaders([
                    'X-Shopify-Access-Token' => $shop->access_token,
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'webhook' => [
                        'topic' => $topic,
                        'address' => $webhookUrl,
                        'format' => 'json',
                    ],
                ]);

                if ($response->successful()) {
                    $results[$topic] = 'Registered';
                    Log::info("Webhook registered: {$topic} for {$shop->shop_domain}");
                } else {
                    $results[$topic] = 'Failed: ' . $response->body();
                    Log::error("Webhook registration failed: {$topic}", [
                        'shop' => $shop->shop_domain,
                        'response' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                $results[$topic] = 'Error: ' . $e->getMessage();
                Log::error("Webhook registration error: {$topic}", [
                    'shop' => $shop->shop_domain,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    public function unregisterAllWebhooks(Shop $shop): void
    {
        $apiVersion=config('services.shopify.version');
        $url = "https://{$shop->shop_domain}/admin/api/{$this->apiVersion}/webhooks.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->access_token,
        ])->get($url);

        if ($response->successful()) {
            $webhooks = $response->json()['webhooks'] ?? [];

            foreach ($webhooks as $webhook) {
                Http::withHeaders([
                    'X-Shopify-Access-Token' => $shop->access_token,
                ])->delete("https://{$shop->shop_domain}/admin/api/2024-01/webhooks/{$webhook['id']}.json");
            }
        }
    }
}
