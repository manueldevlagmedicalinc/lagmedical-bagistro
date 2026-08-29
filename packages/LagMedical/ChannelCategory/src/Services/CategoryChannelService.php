<?php

namespace LagMedical\ChannelCategory\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CategoryChannelService
{
    private array $productCountCache = [];

    public function sync(int $categoryId, array $channelIds): void
    {
        $channelIds = collect($channelIds)
            ->filter()
            ->map(fn ($channelId) => (int) $channelId)
            ->unique()
            ->values();

        DB::table('category_channels')->where('category_id', $categoryId)->delete();

        if ($channelIds->isEmpty()) {
            return;
        }

        DB::table('category_channels')->insert($channelIds->map(fn ($channelId) => [
            'category_id' => $categoryId,
            'channel_id' => $channelId,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());
    }

    public function selectedChannelIds(int $categoryId): array
    {
        return DB::table('category_channels')
            ->where('category_id', $categoryId)
            ->pluck('channel_id')
            ->map(fn ($channelId) => (int) $channelId)
            ->all();
    }

    public function isAvailableForChannel(object $category, ?int $channelId = null): bool
    {
        $channelId ??= (int) core()->getCurrentChannel()->id;

        $assignedChannelIds = $this->selectedChannelIds((int) $category->id);

        return empty($assignedChannelIds) || in_array($channelId, $assignedChannelIds, true);
    }

    public function filterTree(Collection $categories, ?int $channelId = null): Collection
    {
        $channelId ??= (int) core()->getCurrentChannel()->id;
        $rootCategoryId = (int) core()->getCurrentChannel()->root_category_id;

        return $categories
            ->map(fn ($category) => $this->filterNode($category, $channelId, $rootCategoryId))
            ->filter()
            ->values();
    }

    public function totalProducts(object $category, ?int $channelId = null): int
    {
        $channelId ??= (int) core()->getCurrentChannel()->id;

        $cacheKey = implode(':', [
            $channelId,
            app()->getLocale(),
            $category->id,
        ]);

        if (array_key_exists($cacheKey, $this->productCountCache)) {
            return $this->productCountCache[$cacheKey];
        }

        $categoryIds = DB::table('categories')
            ->where('_lft', '>=', $category->_lft)
            ->where('_rgt', '<=', $category->_rgt)
            ->where(function ($query) use ($channelId): void {
                $query
                    ->whereNotExists(function ($query): void {
                        $query
                            ->selectRaw('1')
                            ->from('category_channels')
                            ->whereColumn('category_channels.category_id', 'categories.id');
                    })
                    ->orWhereExists(function ($query) use ($channelId): void {
                        $query
                            ->selectRaw('1')
                            ->from('category_channels')
                            ->whereColumn('category_channels.category_id', 'categories.id')
                            ->where('category_channels.channel_id', $channelId);
                    });
            })
            ->pluck('id');

        return $this->productCountCache[$cacheKey] = DB::table('product_categories as pc')
            ->join('product_channels as pch', 'pc.product_id', '=', 'pch.product_id')
            ->join('product_flat as pf', function ($join): void {
                $join
                    ->on('pc.product_id', '=', 'pf.product_id')
                    ->where('pf.channel', core()->getCurrentChannel()->code)
                    ->where('pf.locale', app()->getLocale())
                    ->where('pf.status', 1);
            })
            ->whereIn('pc.category_id', $categoryIds)
            ->where('pch.channel_id', $channelId)
            ->distinct('pc.product_id')
            ->count('pc.product_id');
    }

    private function filterNode(object $category, int $channelId, int $rootCategoryId): ?object
    {
        if ((int) $category->id !== $rootCategoryId && ! $this->isAvailableForChannel($category, $channelId)) {
            return null;
        }

        $children = $category->children instanceof Collection
            ? $category->children
            : collect($category->children ?? []);

        $filteredChildren = $children
            ->map(fn ($child) => $this->filterNode($child, $channelId, $rootCategoryId))
            ->filter()
            ->values();

        $category->setRelation('children', $filteredChildren);

        if ((int) $category->id === $rootCategoryId) {
            return $category;
        }

        if (
            $this->isAvailableForChannel($category, $channelId)
            && ($filteredChildren->isNotEmpty() || $this->totalProducts($category, $channelId) > 0)
        ) {
            return $category;
        }

        return null;
    }
}
