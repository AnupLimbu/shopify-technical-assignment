<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\Shop;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ShopifySyncService
{
    protected ShopifyService $shopify;

    public function __construct(ShopifyService $shopify)
    {
        $this->shopify = $shopify;
    }

    /**
     * Sync a single shop: products and collections.
     * Returns summary: ['products' => n, 'collections' => m, 'errors' => []]
     */
    public function syncShop(Shop $shop): array
    {
        $accessToken = $shop->access_token;
        $domain = $shop->shop_domain;
        $summary = [
            'products' => 0,
            'collections' => 0,
            'errors' => [],
        ];

        try {
            // Sync products
            $productsCount = $this->syncProducts($domain, $accessToken);
            $summary['products'] = $productsCount;
        } catch (\Throwable $e) {
            Log::error('Error syncing products', ['shop' => $domain, 'error' => $e->getMessage()]);
            $summary['errors'][] = 'products: ' . $e->getMessage();
        }

        try {
            // Sync collections (smart & custom collections combined via collectionQuery)
            $collectionsCount = $this->syncCollections($domain, $accessToken);
            $summary['collections'] = $collectionsCount;
        } catch (\Throwable $e) {
            Log::error('Error syncing collections', ['shop' => $domain, 'error' => $e->getMessage()]);
            $summary['errors'][] = 'collections: ' . $e->getMessage();
        }

        // update shop updated_at to mark last sync
        $shop->touch();

        return $summary;
    }

    protected function syncProducts(string $shopDomain, string $accessToken): int
    {
        $count = 0;
        $after = null;

        $query = <<<'GRAPHQL'
query ($first: Int!, $after: String) {
  products(first: $first, after: $after) {
    pageInfo { hasNextPage }
    edges {
      cursor
      node {
        id
        title
        bodyHtml
        publishedAt
        status
        updatedAt
      }
    }
  }
}
GRAPHQL;

        do {
            $variables = ['first' => 250, 'after' => $after];
            $resp = $this->shopify->graphQL($shopDomain, $accessToken, $query, $variables);

            if (!isset($resp['data']['products']['edges'])) {
                throw new \RuntimeException('Invalid GraphQL response for products: ' . json_encode($resp));
            }

            $edges = $resp['data']['products']['edges'];
            $shop=Shop::where('shop_domain', $shopDomain)->first();
            foreach ($edges as $edge) {
                $node = $edge['node'];
                $shopifyGid = $node['id']; // e.g., "gid://shopify/Product/123456789"
                $shopifyId = $this->extractIdFromGid($shopifyGid);

                Product::updateOrCreate(
                    ['shop_id' => $shop->id, 'shopify_id' => $shopifyId],
                    [
                        'title' => $node['title'] ?? '',
                        'body_html' => $node['bodyHtml'] ?? null,
                        'status' => $node['status'] ?? 'active',
                        'published_at' => $node['publishedAt'] ? now()->parse($node['publishedAt']) : null,
                        'synced_at' => now(),
                    ]
                );

                $count++;
                $after = $edge['cursor'];
            }

            $hasNext = $resp['data']['products']['pageInfo']['hasNextPage'] ?? false;
        } while ($hasNext);

        return $count;
    }

    protected function syncCollections(string $shopDomain, string $accessToken): int
    {
        $count = 0;
        $after = null;

        // Collections can be of different types; this will fetch collections and include productCount
        $query = <<<'GRAPHQL'
query ($first: Int!, $after: String) {
  collections(first: $first, after: $after) {
    pageInfo { hasNextPage }
    edges {
      cursor
      node {
        id
        title
        updatedAt
        productsCount: productsCount
      }
    }
  }
}
GRAPHQL;

        do {
            $variables = ['first' => 250, 'after' => $after];
            $resp = $this->shopify->graphQL($shopDomain, $accessToken, $query, $variables);

            if (!isset($resp['data']['collections']['edges'])) {
                // Some shops may not have collections field depending on API version; log and break
                throw new \RuntimeException('Invalid GraphQL response for collections: ' . json_encode($resp));
            }

            $edges = $resp['data']['collections']['edges'];
            $shop=Shop::where('shop_domain', $shopDomain)->first();
            foreach ($edges as $edge) {
                $node = $edge['node'];
                $title = $node['title'] ?? '';
                $productsCount = isset($node['productsCount']) ? (int)$node['productsCount'] : 0;

                ProductCollection::updateOrCreate(
                    ['shop_id' => $shop->id, 'title' => $title],
                    [
                        'products_count' => $productsCount,
                        'synced_at' => now(),
                    ]
                );

                $count++;
                $after = $edge['cursor'];
            }

            $hasNext = $resp['data']['collections']['pageInfo']['hasNextPage'] ?? false;
        } while ($hasNext);

        return $count;
    }

    protected function extractIdFromGid(string $gid): string
    {
        // Extract numeric id from gid like "gid://shopify/Product/123456789"
        if (preg_match('/\/(\d+)$/', $gid, $m)) {
            return $m[1];
        }
        // fallback to base64 or full gid
        return $gid;
    }
}
