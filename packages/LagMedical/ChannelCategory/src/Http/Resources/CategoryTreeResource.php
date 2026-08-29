<?php

namespace LagMedical\ChannelCategory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LagMedical\ChannelCategory\Services\CategoryChannelService;

class CategoryTreeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalProducts = app(CategoryChannelService::class)->totalProducts($this->resource);

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'raw_name' => $this->name,
            'slug' => $this->slug,
            'url' => $this->url,
            'status' => $this->status,
            'total_products' => $totalProducts,
            'children' => self::collection($this->children),
        ];
    }
}
