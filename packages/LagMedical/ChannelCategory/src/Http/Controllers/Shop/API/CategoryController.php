<?php

namespace LagMedical\ChannelCategory\Http\Controllers\Shop\API;

use Illuminate\Http\Resources\Json\JsonResource;
use LagMedical\ChannelCategory\Http\Resources\CategoryTreeResource;
use LagMedical\ChannelCategory\Services\CategoryChannelService;

class CategoryController extends \Webkul\Shop\Http\Controllers\API\CategoryController
{
    public function tree(): JsonResource
    {
        $categories = $this->categoryRepository->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id);

        $categories = app(CategoryChannelService::class)->filterTree($categories);

        return CategoryTreeResource::collection($categories);
    }
}
