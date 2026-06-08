<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Models\CategoryProxy;

if (! function_exists('lagmedical_is_quote_mode')) {
    function lagmedical_is_quote_mode(): bool
    {
        if (! config('lagmedical.enabled', true)) {
            return false;
        }

        $channelCode = core()->getCurrentChannel()?->code;

        if (! $channelCode) {
            return false;
        }

        return in_array($channelCode, config('lagmedical.quote_mode_channels', []), true);
    }
}

if (! function_exists('lagmedical_quote_mode_value')) {
    function lagmedical_quote_mode_value(string $key, mixed $default = null): mixed
    {
        return config('lagmedical.quote_mode.'.$key, $default);
    }
}

if (! function_exists('lagmedical_whatsapp_value')) {
    function lagmedical_whatsapp_value(string $key, mixed $default = null): mixed
    {
        return config('lagmedical.whatsapp.'.$key, $default);
    }
}

if (! function_exists('lagmedical_localized')) {
    function lagmedical_localized(mixed $value, string $default): string
    {
        if (! is_array($value)) {
            return is_string($value) ? $value : $default;
        }

        $locale = app()->getLocale();

        if (! empty($value[$locale])) {
            return (string) $value[$locale];
        }

        if (! empty($value['en'])) {
            return (string) $value['en'];
        }

        return $default;
    }
}

if (! function_exists('lagmedical_hide_prices')) {
    function lagmedical_hide_prices(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_prices', true);
    }
}

if (! function_exists('lagmedical_hide_totals')) {
    function lagmedical_hide_totals(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_totals', true);
    }
}

if (! function_exists('lagmedical_hide_checkout_payment')) {
    function lagmedical_hide_checkout_payment(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_checkout_payment', true);
    }
}

if (! function_exists('lagmedical_hide_coupons')) {
    function lagmedical_hide_coupons(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_coupons', true);
    }
}

if (! function_exists('lagmedical_hide_tax')) {
    function lagmedical_hide_tax(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_tax', true);
    }
}

if (! function_exists('lagmedical_hide_shipping_amounts')) {
    function lagmedical_hide_shipping_amounts(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_shipping_amounts', true);
    }
}

if (! function_exists('lagmedical_quote_message')) {
    function lagmedical_quote_message(): string
    {
        return lagmedical_localized(
            lagmedical_quote_mode_value('review_message', []),
            'Orders are reviewed and confirmed by our sales department before processing.'
        );
    }
}

if (! function_exists('lagmedical_quote_button_label')) {
    function lagmedical_quote_button_label(): string
    {
        return lagmedical_localized(
            lagmedical_quote_mode_value('cart_button_label', []),
            'Submit Order Request'
        );
    }
}

if (! function_exists('lagmedical_checkout_button_label')) {
    function lagmedical_checkout_button_label(): string
    {
        return lagmedical_localized(
            lagmedical_quote_mode_value('checkout_button_label', []),
            'Submit Order Request'
        );
    }
}

if (! function_exists('lagmedical_whatsapp_enabled')) {
    function lagmedical_whatsapp_enabled(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_whatsapp_value('enabled', false);
    }
}

if (! function_exists('lagmedical_whatsapp_phone')) {
    function lagmedical_whatsapp_phone(): string
    {
        return preg_replace('/\D+/', '', (string) lagmedical_whatsapp_value('phone', ''));
    }
}

if (! function_exists('lagmedical_whatsapp_button_label')) {
    function lagmedical_whatsapp_button_label(): string
    {
        return lagmedical_localized(
            lagmedical_whatsapp_value('button_label', []),
            'Ask on WhatsApp'
        );
    }
}

if (! function_exists('lagmedical_whatsapp_message')) {
    function lagmedical_whatsapp_message(string $productName, string $productUrl): string
    {
        $template = lagmedical_localized(
            lagmedical_whatsapp_value('message_template', []),
            'Hello Lag Medical, I need this product: :product_name. I saw it at this link: :product_url'
        );

        return strtr($template, [
            ':product_name' => $productName,
            ':product_url' => $productUrl,
        ]);
    }
}

if (! function_exists('lagmedical_whatsapp_url')) {
    function lagmedical_whatsapp_url(string $productName, string $productUrl): string
    {
        $phone = lagmedical_whatsapp_phone();

        if ($phone === '') {
            return '';
        }

        $message = lagmedical_whatsapp_message($productName, $productUrl);

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }
}

if (! function_exists('lagmedical_equipment_brand_filter_context')) {
    function lagmedical_equipment_brand_filter_context(mixed $category): array
    {
        $emptyContext = [
            'active' => false,
            'brand_attribute_code' => config('lagmedical.equipment_brand_filters.brand_attribute_code', 'medical_brand'),
            'brand_option_id' => null,
            'brand_option_label' => null,
            'available_categories' => [],
        ];

        if (! $category?->id) {
            return $emptyContext;
        }

        $equipmentBrandsRootId = (int) config('lagmedical.equipment_brand_filters.equipment_brands_root_id', 0);
        $medicalEquipmentRootId = (int) config('lagmedical.equipment_brand_filters.medical_equipment_root_id', 0);
        $brandAttributeCode = (string) config('lagmedical.equipment_brand_filters.brand_attribute_code', 'medical_brand');

        if (! $equipmentBrandsRootId || ! $medicalEquipmentRootId) {
            return $emptyContext;
        }

        $categoryModel = CategoryProxy::modelClass();
        $equipmentBrandsRoot = $categoryModel::find($equipmentBrandsRootId);

        if (! $equipmentBrandsRoot) {
            return $emptyContext;
        }

        $isEquipmentBrandCategory = (int) $category->id !== $equipmentBrandsRootId
            && (int) $category->_lft > (int) $equipmentBrandsRoot->_lft
            && (int) $category->_rgt < (int) $equipmentBrandsRoot->_rgt;

        if (! $isEquipmentBrandCategory) {
            return $emptyContext;
        }

        $brandAttribute = app(AttributeRepository::class)
            ->findOneByField('code', $brandAttributeCode);

        if (! $brandAttribute) {
            return $emptyContext;
        }

        $brandOption = lagmedical_find_matching_brand_option($brandAttribute->id, $category);

        if (! $brandOption) {
            return $emptyContext;
        }

        return [
            'active' => true,
            'brand_attribute_code' => $brandAttributeCode,
            'brand_option_id' => (string) $brandOption->id,
            'brand_option_label' => $brandOption->label ?: $brandOption->admin_name,
            'available_categories' => lagmedical_available_medical_equipment_categories(
                $medicalEquipmentRootId,
                $equipmentBrandsRootId,
                $brandAttribute->id,
                $brandOption->id
            ),
        ];
    }
}

if (! function_exists('lagmedical_find_matching_brand_option')) {
    function lagmedical_find_matching_brand_option(int $attributeId, mixed $category): mixed
    {
        $locale = app()->getLocale();
        $categoryName = (string) ($category->name ?? '');
        $categorySlug = (string) ($category->slug ?? '');
        $normalizedCategoryValues = array_filter([
            lagmedical_normalize_brand_value($categoryName),
            lagmedical_normalize_brand_value($categorySlug),
        ]);

        return DB::table('attribute_options')
            ->leftJoin('attribute_option_translations', function ($join) use ($locale) {
                $join->on('attribute_options.id', '=', 'attribute_option_translations.attribute_option_id')
                    ->where('attribute_option_translations.locale', $locale);
            })
            ->where('attribute_options.attribute_id', $attributeId)
            ->select(
                'attribute_options.id',
                'attribute_options.admin_name',
                'attribute_option_translations.label'
            )
            ->get()
            ->first(function ($option) use ($normalizedCategoryValues) {
                $optionValues = array_filter([
                    lagmedical_normalize_brand_value((string) $option->admin_name),
                    lagmedical_normalize_brand_value((string) $option->label),
                ]);

                return ! empty(array_intersect($normalizedCategoryValues, $optionValues));
            });
    }
}

if (! function_exists('lagmedical_normalize_brand_value')) {
    function lagmedical_normalize_brand_value(string $value): string
    {
        return Str::slug($value);
    }
}

if (! function_exists('lagmedical_available_medical_equipment_categories')) {
    function lagmedical_available_medical_equipment_categories(int $medicalEquipmentRootId, int $equipmentBrandsRootId, int $brandAttributeId, int $brandOptionId): array
    {
        $categoryModel = CategoryProxy::modelClass();
        $medicalEquipmentRoot = $categoryModel::find($medicalEquipmentRootId);
        $equipmentBrandsRoot = $categoryModel::find($equipmentBrandsRootId);

        if (! $medicalEquipmentRoot || ! $equipmentBrandsRoot) {
            return [];
        }

        $statusAttributeId = DB::table('attributes')->where('code', 'status')->value('id');
        $visibleAttributeId = DB::table('attributes')->where('code', 'visible_individually')->value('id');

        $query = DB::table('categories')
            ->join('category_translations', function ($join) {
                $join->on('categories.id', '=', 'category_translations.category_id')
                    ->where('category_translations.locale', app()->getLocale());
            })
            ->join('product_categories', 'categories.id', '=', 'product_categories.category_id')
            ->join('products', 'product_categories.product_id', '=', 'products.id')
            ->join('product_attribute_values as brand_values', function ($join) use ($brandAttributeId, $brandOptionId) {
                $join->on('products.id', '=', 'brand_values.product_id')
                    ->where('brand_values.attribute_id', $brandAttributeId)
                    ->where('brand_values.integer_value', $brandOptionId);
            });

        if ($statusAttributeId) {
            $query->join('product_attribute_values as status_values', function ($join) use ($statusAttributeId) {
                $join->on('products.id', '=', 'status_values.product_id')
                    ->where('status_values.attribute_id', $statusAttributeId)
                    ->where('status_values.boolean_value', 1);
            });
        }

        if ($visibleAttributeId) {
            $query->join('product_attribute_values as visible_values', function ($join) use ($visibleAttributeId) {
                $join->on('products.id', '=', 'visible_values.product_id')
                    ->where('visible_values.attribute_id', $visibleAttributeId)
                    ->where('visible_values.boolean_value', 1);
            });
        }

        return $query
            ->where('categories.status', 1)
            ->where('categories.id', '!=', $medicalEquipmentRootId)
            ->where('categories._lft', '>', $medicalEquipmentRoot->_lft)
            ->where('categories._rgt', '<', $medicalEquipmentRoot->_rgt)
            ->where(function ($query) use ($equipmentBrandsRoot) {
                $query->where('categories._lft', '<', $equipmentBrandsRoot->_lft)
                    ->orWhere('categories._rgt', '>', $equipmentBrandsRoot->_rgt);
            })
            ->select([
                'categories.id',
                'category_translations.name',
                'category_translations.slug',
            ])
            ->distinct()
            ->orderBy('categories.position')
            ->orderBy('category_translations.name')
            ->get()
            ->map(fn ($category) => [
                'id' => (string) $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->values()
            ->all();
    }
}
