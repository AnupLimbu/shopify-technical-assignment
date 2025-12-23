<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
class ShopifyService
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $apiVersion;

    public function __construct()
    {
        $this->apiKey = config('services.shopify.api_key') ?: env('SHOPIFY_API_KEY');
        $this->apiSecret = config('services.shopify.api_secret') ?: env('SHOPIFY_API_SECRET');
        $this->apiVersion = env('services.shopify.api_version', '2025-10');
    }

    public function buildAuthUrl(string $shop, string $scopes, string $redirectUri, string $state): string
    {
        $params = http_build_query([
            'client_id' => $this->apiKey,
            'scope' => $scopes,
            'redirect_uri' => $redirectUri,
            'state' => $state,

        ]);

        return "https://{$shop}/admin/oauth/authorize?{$params}";
    }

    public function verifyHmac(array $params): bool
    {
        $hmac = $params['hmac'] ?? '';
        $paramsForHmac = collect($params)
            ->except(['signature', 'hmac'])
            ->mapWithKeys(function ($value, $key) {
                return [$key => $value];
            })
            ->toArray();

        ksort($paramsForHmac);

        $computed = hash_hmac('sha256', http_build_query($paramsForHmac), $this->apiSecret);

        return hash_equals($computed, $hmac);
    }

    public function getAccessToken(string $shop, string $code): array
    {
        $url = "https://{$shop}/admin/oauth/access_token";

        $response = Http::asForm()->post($url, [
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret,
            'code' => $code,
        ]);

        return $response->json();
    }

    // helper to call GraphQL Admin API
    public function graphQL(string $shop, string $accessToken, string $query, array $variables = [])
    {
        $url = "https://{$shop}/admin/api/{$this->apiVersion}/graphql.json";

        $resp = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'query' => $query,
            'variables' => $variables,
        ]);

        return $resp->json();
    }

    /**
     * Register a REST webhook on a shop.
     * $shop - shop domain (e.g. some-shop.myshopify.com)
     * $accessToken - shop access token
     * $topic - e.g. 'products/create'
     * $address - publicly accessible HTTPS URL where Shopify will POST (e.g., https://example.com/api/webhooks/products)
     */
    public function registerWebhook(string $shop, string $accessToken, string $topic, string $address): array
    {
        $url = "https://{$shop}/admin/api/{$this->apiVersion}/webhooks.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'webhook' => [
                'topic' => $topic,
                'address' => $address,
                'format' => 'json',
            ],
        ]);

        // Return raw json payload so caller can inspect result
        return $response->json();
    }
}
