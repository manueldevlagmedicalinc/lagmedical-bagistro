<?php

namespace LagMedical\ChannelCategory\Http\Controllers\Shop;

use Illuminate\Http\Request;
use LagMedical\ChannelCategory\Services\CategoryChannelService;

class ProductsCategoriesProxyController extends \Webkul\Shop\Http\Controllers\ProductsCategoriesProxyController
{
    public function index(Request $request)
    {
        $slugOrURLKey = urldecode(trim($request->getPathInfo(), '/'));

        $category = $this->categoryRepository->findBySlug($slugOrURLKey);

        if ($category && ! app(CategoryChannelService::class)->isAvailableForChannel($category)) {
            abort(404);
        }

        return parent::index($request);
    }
}
