<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\ShopifyService;
use App\Services\ShopifyWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ShopifyAuthController extends Controller
{
    protected ShopifyService $shopify;

    public function __construct(ShopifyService $shopify)
    {
        $this->shopify = $shopify;
    }

    //redirect merchant to Shopify authorization URL
    public function install(Request $request)
    {
        $shop = $request->get('shop');
        if (! $shop) {
            return abort(400, 'Missing shop parameter');
        }

        //adding a random generated string/token to send it with the request to mitigate CSRF attacks
        $state = Str::random(24);

        //look into whether cache is better or session is better for this
        Cache::put('shopify_oauth_state:' . $state, $shop, now()->addMinutes(10));

        $scopes = config('services.shopify.scopes') ?: env('SHOPIFY_SCOPES');

        $redirectUri = route('shopify.callback');

        //was causing issue in local production since the url generated was not in https. look into this further
        if (parse_url($redirectUri, PHP_URL_SCHEME) !== 'https') {
            abort(403, 'HTTPS is required for this callback');
        }

        $authUrl = $this->shopify->buildAuthUrl($shop, $scopes, $redirectUri, $state);

        return redirect()->away($authUrl);
    }

    // Shopify callback, validate HMAC, exchange code, persist token, redirect to embedded app
    public function callback(Request $request)
    {

        $params = $request->all();

        $shop = $params['shop'] ?? null;
        $hmac = $params['hmac'] ?? null;
        $code = $params['code'] ?? null;
        $state = $params['state'] ?? null;
        $host = $params['host'] ?? null;

        if (! $shop || ! $hmac || ! $code) {
            return abort(400, 'Missing required parameters');
        }

        //if the shopify_oauth_state in the request is not the same as the one in our session, reject the callback.
        $cachedShop = Cache::pull('shopify_oauth_state:' . $state);

        if (! $cachedShop) {
            return abort(403, 'Invalid or expired OAuth state (no matching server record)');
        }

        // Check the shop matches the one in cache
        if ($shop !== $cachedShop) {
            return abort(403, 'Shop mismatch with OAuth state');
        }


        if (! $this->shopify->verifyHmac($params)) {
            Log::warning('Shopify OAuth HMAC verification failed', $params);
            return abort(403, 'HMAC verification failed');
        }

        $tokenResponse = $this->shopify->getAccessToken($shop, $code);

        if (! isset($tokenResponse['access_token'])) {
            Log::error('Failed to get access token', ['response' => $tokenResponse]);
            return abort(500, 'Failed to get access token');
        }

        // Persist shop
        $shopModel = Shop::updateOrCreate(
            ['shop_domain' => $shop],
            [
                'access_token' => $tokenResponse['access_token'],
                'scope' => $tokenResponse['scope'] ?? null,
                'installed_at' => now(),
            ]
        );

        try {
            $webhookService = new ShopifyWebhookService();
            $webhookService->registerWebhooks($shopModel);

        } catch (\Throwable $e) {
            Log::warning('Failed to register webhooks for shop ' . $shop, ['error' => $e->getMessage()]);
        }
        // Redirect merchant into embedded app with shop param

        $appUrl = route('app') . '?shop=' . urlencode($shop) . '&host=' . urlencode($host);
        return redirect()->to($appUrl);
    }
}
