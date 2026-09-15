<?php

namespace LagMedical\CmsPro\Services;

use Illuminate\Support\Collection;
use Webkul\Product\Repositories\ProductRepository;

class ProductBlockService
{
    public function __construct(protected ProductRepository $products) {}

    public function products(array $data, ?int $channelId = null): Collection
    {
        $limit = min(24, max(1, (int) ($data['limit'] ?? 8)));
        $channelId ??= core()->getCurrentChannel()->id;
        // Catalog blocks expose the same catalog filters used by the B2B storefront.
        $source = $data['source'] ?? 'featured';

        if ($source === 'manual') {
            $ids = collect(explode(',', $data['product_ids'] ?? ''))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->unique()
                ->take($limit)
                ->all();

            if ($ids === []) {
                return collect();
            }

            return $this->products->findWhereIn('id', $ids)
                ->filter(fn ($product) => $product->status
                    && $product->visible_individually
                    && $product->channels->contains('id', $channelId))
                ->take($limit)
                ->values();
        }

        $params = [
            'channel_id' => $channelId,
            'status' => 1,
            'visible_individually' => 1,
            'limit' => $limit,
        ];

        if ($source === 'category' && ! empty($data['category_id'])) {
            $params['category_id'] = (int) $data['category_id'];
        } elseif ($source === 'new') {
            $params['new'] = 1;
        } else {
            $params['featured'] = 1;
        }

        return collect($this->products->getAll($params)->items())->take($limit)->values();
    }
}
