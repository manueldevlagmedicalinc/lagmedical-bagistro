<?php

namespace LagMedical\ChannelCategory\Http\Controllers\Shop;

use LagMedical\ChannelCategory\Http\Resources\CategoryTreeResource;
use LagMedical\ChannelCategory\Services\CategoryChannelService;

class HomeController extends \Webkul\Shop\Http\Controllers\HomeController
{
    public function index()
    {
        $customizations = $this->themeCustomizationRepository->orderBy('sort_order')->findWhere([
            'status' => self::STATUS,
            'channel_id' => core()->getCurrentChannel()->id,
            'theme_code' => core()->getCurrentChannel()->theme,
        ]);

        $categories = $this->categoryRepository->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id);

        $categories = app(CategoryChannelService::class)->filterTree($categories);

        $categories = CategoryTreeResource::collection($categories);

        return view('shop::home.index', compact('customizations', 'categories'));
    }
}
