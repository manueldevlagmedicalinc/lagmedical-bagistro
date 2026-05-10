<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LagMedicalDemoSeederV2 extends Seeder
{
    private const LOCALES = ['en', 'es', 'pt_BR'];

    private const TARGET_CHANNEL_CODE = 'lag-medical';

    private array $systemAttributeIds = [];

    private array $customAttributeIds = [];

    private array $categoryIds = [];

    private ?int $targetChannelId = null;

    private ?string $targetChannelCode = null;

    public function run(): void
    {
        DB::transaction(function () {
            $this->ensureLocales();
            $this->resolveTargetChannel();
            $this->cleanCatalogData();
            $this->seedCategories();
            $this->seedAttributes();
            $this->seedFamilies();
            $this->seedProducts();
            $this->syncTargetChannelLocalesAndRoot();
        });
    }

    private function resolveTargetChannel(): void
    {
        $channel = DB::table('channels')
            ->where('code', self::TARGET_CHANNEL_CODE)
            ->first();

        if (! $channel) {
            $channel = DB::table('channels')->first();
        }

        if (! $channel) {
            throw new \RuntimeException('No channel found. Create at least one channel before running this seeder.');
        }

        $this->targetChannelId = (int) $channel->id;
        $this->targetChannelCode = (string) $channel->code;
    }

    private function ensureLocales(): void
    {
        $now = now();

        $locales = [
            ['code' => 'en', 'name' => 'English', 'direction' => 'ltr'],
            ['code' => 'es', 'name' => 'Espanol', 'direction' => 'ltr'],
            ['code' => 'pt_BR', 'name' => 'Portugues Brasil', 'direction' => 'ltr'],
        ];

        foreach ($locales as $locale) {
            $existing = DB::table('locales')->where('code', $locale['code'])->first();

            if ($existing) {
                DB::table('locales')->where('id', $existing->id)->update([
                    'name' => $locale['name'],
                    'direction' => $locale['direction'],
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('locales')->insert([
                'code' => $locale['code'],
                'name' => $locale['name'],
                'direction' => $locale['direction'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function cleanCatalogData(): void
    {
        DB::table('product_images')->delete();
        DB::table('product_categories')->delete();
        DB::table('product_channels')->delete();
        DB::table('product_inventories')->delete();
        DB::table('product_attribute_values')->delete();
        DB::table('products')->delete();

        DB::table('attribute_group_mappings')->delete();
        DB::table('attribute_groups')->where('is_user_defined', 1)->delete();
        DB::table('attribute_families')->where('is_user_defined', 1)->delete();

        $customAttributeIds = DB::table('attributes')->where('is_user_defined', 1)->pluck('id')->all();

        if (! empty($customAttributeIds)) {
            DB::table('attribute_option_translations')->whereIn('attribute_option_id', function ($q) use ($customAttributeIds) {
                $q->select('id')->from('attribute_options')->whereIn('attribute_id', $customAttributeIds);
            })->delete();

            DB::table('attribute_options')->whereIn('attribute_id', $customAttributeIds)->delete();
            DB::table('attribute_translations')->whereIn('attribute_id', $customAttributeIds)->delete();
            DB::table('attributes')->whereIn('id', $customAttributeIds)->delete();
        }

        DB::table('theme_customization_translations')->delete();
        DB::table('theme_customizations')->delete();

        DB::table('category_translations')->where('category_id', '>', 1)->delete();
        DB::table('categories')->where('id', '>', 1)->delete();
    }

    private function seedCategories(): void
    {
        $now = Carbon::now();

        DB::table('categories')->where('id', 1)->update([
            'parent_id' => null,
            'position' => 1,
            'status' => 1,
            '_lft' => 1,
            '_rgt' => 40,
            'updated_at' => $now,
        ]);

        DB::table('category_translations')->where('category_id', 1)->delete();

        $rootTranslations = [
            'en' => ['Lag Medical Catalog', 'lag-medical-catalog', 'Structured B2B catalog for ophthalmic equipment and premium eyewear.'],
            'es' => ['Catalogo Lag Medical', 'catalogo-lag-medical', 'Catalogo B2B estructurado para equipos oftalmicos y eyewear premium.'],
            'pt_BR' => ['Catalogo Lag Medical', 'catalogo-lag-medical', 'Catalogo B2B estruturado para equipamentos oftalmicos e eyewear premium.'],
        ];

        foreach (self::LOCALES as $locale) {
            DB::table('category_translations')->insert([
                'name' => $rootTranslations[$locale][0],
                'slug' => $rootTranslations[$locale][1],
                'description' => $rootTranslations[$locale][2],
                'meta_title' => $rootTranslations[$locale][0].' | Lag Medical Inc.',
                'meta_description' => $rootTranslations[$locale][2],
                'meta_keywords' => $this->categoryKeywords($locale),
                'category_id' => 1,
                'locale' => $locale,
            ]);
        }

        $tree = [
            ['code' => 'medical-equipment', 'parent' => 1, 'lft' => 2, 'rgt' => 33, 'en' => 'Medical Equipment', 'es' => 'Equipos medicos', 'pt_BR' => 'Equipamentos medicos'],
            ['code' => 'ophthalmic-equipment', 'parent' => 'medical-equipment', 'lft' => 3, 'rgt' => 14, 'en' => 'Ophthalmic Equipment', 'es' => 'Equipos oftalmicos', 'pt_BR' => 'Equipamentos oftalmicos'],
            ['code' => 'auto-refractors', 'parent' => 'ophthalmic-equipment', 'lft' => 4, 'rgt' => 5, 'en' => 'Auto Refractors', 'es' => 'Autorefractometros', 'pt_BR' => 'Autorrefratores'],
            ['code' => 'lensmeters', 'parent' => 'ophthalmic-equipment', 'lft' => 6, 'rgt' => 7, 'en' => 'Lensmeters', 'es' => 'Lensometros', 'pt_BR' => 'Lensometros'],
            ['code' => 'slit-lamps', 'parent' => 'ophthalmic-equipment', 'lft' => 8, 'rgt' => 9, 'en' => 'Slit Lamps', 'es' => 'Lamparas de hendidura', 'pt_BR' => 'Lampadas de fenda'],
            ['code' => 'fundus-cameras', 'parent' => 'ophthalmic-equipment', 'lft' => 10, 'rgt' => 11, 'en' => 'Fundus Cameras', 'es' => 'Camaras de fondo de ojo', 'pt_BR' => 'Cameras de fundo de olho'],
            ['code' => 'portable-devices', 'parent' => 'ophthalmic-equipment', 'lft' => 12, 'rgt' => 13, 'en' => 'Portable Devices', 'es' => 'Equipos portatiles', 'pt_BR' => 'Equipamentos portateis'],
            ['code' => 'optical-equipment', 'parent' => 'medical-equipment', 'lft' => 15, 'rgt' => 24, 'en' => 'Optical Equipment', 'es' => 'Equipos opticos', 'pt_BR' => 'Equipamentos opticos'],
            ['code' => 'trial-lens-sets', 'parent' => 'optical-equipment', 'lft' => 16, 'rgt' => 17, 'en' => 'Trial Lens Sets', 'es' => 'Sets de lentes de prueba', 'pt_BR' => 'Kits de lentes de prova'],
            ['code' => 'phoropters', 'parent' => 'optical-equipment', 'lft' => 18, 'rgt' => 19, 'en' => 'Phoropters', 'es' => 'Foropteros', 'pt_BR' => 'Foropteros'],
            ['code' => 'chart-projectors', 'parent' => 'optical-equipment', 'lft' => 20, 'rgt' => 21, 'en' => 'Chart Projectors', 'es' => 'Proyectores de optotipos', 'pt_BR' => 'Projetores de optotipos'],
            ['code' => 'vision-screeners', 'parent' => 'optical-equipment', 'lft' => 22, 'rgt' => 23, 'en' => 'Vision Screeners', 'es' => 'Screeners visuales', 'pt_BR' => 'Screeners visuais'],
            ['code' => 'clinical-accessories', 'parent' => 'medical-equipment', 'lft' => 25, 'rgt' => 32, 'en' => 'Clinical Accessories', 'es' => 'Accesorios clinicos', 'pt_BR' => 'Acessorios clinicos'],
            ['code' => 'diagnostic-accessories', 'parent' => 'clinical-accessories', 'lft' => 26, 'rgt' => 27, 'en' => 'Diagnostic Accessories', 'es' => 'Accesorios diagnosticos', 'pt_BR' => 'Acessorios diagnosticos'],
            ['code' => 'exam-room-accessories', 'parent' => 'clinical-accessories', 'lft' => 28, 'rgt' => 29, 'en' => 'Exam Room Accessories', 'es' => 'Accesorios de sala de examen', 'pt_BR' => 'Acessorios de sala de exame'],
            ['code' => 'replacement-parts', 'parent' => 'clinical-accessories', 'lft' => 30, 'rgt' => 31, 'en' => 'Replacement Parts', 'es' => 'Repuestos', 'pt_BR' => 'Pecas de reposicao'],
            ['code' => 'frames-eyewear', 'parent' => 1, 'lft' => 34, 'rgt' => 39, 'en' => 'Frames and Eyewear', 'es' => 'Armazones y Eyewear', 'pt_BR' => 'Armacoes e Eyewear'],
            ['code' => 'optical-frames', 'parent' => 'frames-eyewear', 'lft' => 35, 'rgt' => 36, 'en' => 'Optical Frames', 'es' => 'Armazones opticos', 'pt_BR' => 'Armacoes opticas'],
            ['code' => 'sunglasses', 'parent' => 'frames-eyewear', 'lft' => 37, 'rgt' => 38, 'en' => 'Sunglasses', 'es' => 'Gafas de sol', 'pt_BR' => 'Oculos de sol'],
        ];

        $position = 1;

        foreach ($tree as $node) {
            $parentId = is_int($node['parent']) ? $node['parent'] : ($this->categoryIds[$node['parent']] ?? 1);

            $id = DB::table('categories')->insertGetId([
                'parent_id' => $parentId,
                'position' => $position++,
                'status' => 1,
                'display_mode' => 'products_and_description',
                '_lft' => $node['lft'],
                '_rgt' => $node['rgt'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->categoryIds[$node['code']] = $id;

            foreach (self::LOCALES as $locale) {
                $name = $node[$locale];

                DB::table('category_translations')->insert([
                    'name' => $name,
                    'slug' => $node['code'].'-'.$locale,
                    'description' => $this->categoryDescription($name, $locale),
                    'meta_title' => $name.' | Lag Medical Inc.',
                    'meta_description' => $this->categoryDescription($name, $locale),
                    'meta_keywords' => $this->categoryKeywords($locale),
                    'category_id' => $id,
                    'locale' => $locale,
                ]);
            }
        }
    }

    private function seedAttributes(): void
    {
        $this->systemAttributeIds = DB::table('attributes')
            ->whereIn('code', ['name', 'url_key', 'status', 'visible_individually', 'short_description', 'description', 'meta_title', 'meta_keywords', 'meta_description', 'price', 'cost', 'special_price', 'weight'])
            ->pluck('id', 'code')
            ->all();

        $defs = [
            ['medical_brand', 'Medical Brand', 'select', true],
            ['eyewear_brand', 'Eyewear Brand', 'select', true],
            ['model', 'Model', 'text', false],
            ['device_type', 'Device Type', 'text', true],
            ['clinical_use', 'Clinical Use', 'textarea', false],
            ['measurement_range', 'Measurement Range', 'text', false],
            ['display_type', 'Display Type', 'text', false],
            ['connectivity', 'Connectivity', 'text', false],
            ['power_supply', 'Power Supply', 'text', false],
            ['portability', 'Portability', 'text', false],
            ['warranty', 'Warranty', 'text', false],
            ['country_of_origin', 'Country of Origin', 'text', false],
            ['included_accessories', 'Included Accessories', 'textarea', false],
            ['technical_specifications', 'Technical Specifications', 'textarea', false],
            ['certification', 'Certification', 'text', false],
            ['ships_from', 'Ships From', 'text', false],
            ['distribution_region', 'Distribution Region', 'select', true],
            ['diagnostic_function', 'Diagnostic Function', 'text', false],
            ['exam_type', 'Exam Type', 'text', false],
            ['imaging_capability', 'Imaging Capability', 'text', false],
            ['patient_application', 'Patient Application', 'text', false],
            ['clinic_environment', 'Clinic Environment', 'text', false],
            ['optical_use', 'Optical Use', 'text', false],
            ['measurement_type', 'Measurement Type', 'text', false],
            ['calibration_required', 'Calibration Required', 'boolean', false],
            ['accessory_type', 'Accessory Type', 'text', false],
            ['compatible_devices', 'Compatible Devices', 'textarea', false],
            ['material', 'Material', 'text', false],
            ['pack_quantity', 'Pack Quantity', 'integer', false],
            ['usage_environment', 'Usage Environment', 'text', false],
            ['frame_type', 'Frame Type', 'text', false],
            ['gender', 'Gender', 'select', true],
            ['shape', 'Shape', 'text', false],
            ['frame_color', 'Frame Color', 'text', false],
            ['lens_width', 'Lens Width', 'text', false],
            ['bridge_width', 'Bridge Width', 'text', false],
            ['temple_length', 'Temple Length', 'text', false],
            ['size', 'Size', 'text', false],
            ['rim_type', 'Rim Type', 'text', false],
            ['collection', 'Collection', 'text', false],
            ['style', 'Style', 'text', false],
            ['commercial_segment', 'Commercial Segment', 'text', false],
            ['sunglass_type', 'Sunglass Type', 'text', false],
            ['lens_color', 'Lens Color', 'text', false],
            ['lens_type', 'Lens Type', 'text', false],
            ['uv_protection', 'UV Protection', 'text', false],
            ['polarized', 'Polarized', 'boolean', false],
            ['new_arrival', 'New Arrival', 'boolean', false],
            ['best_seller', 'Best Seller', 'boolean', false],
            ['professional_grade', 'Professional Grade', 'boolean', false],
            ['ships_from_miami', 'Ships from Miami', 'boolean', false],
            ['latam_distribution', 'LATAM Distribution', 'boolean', false],
            ['clinical_solution', 'Clinical Solution', 'boolean', false],
            ['premium_frame', 'Premium Frame', 'boolean', false],
            ['high_rotation', 'High Rotation', 'boolean', false],
            ['b2b_favorite', 'B2B Favorite', 'boolean', false],
        ];

        $now = now();
        $position = 1;

        foreach ($defs as [$code, $name, $type, $filter]) {
            $id = DB::table('attributes')->insertGetId([
                'code' => $code,
                'admin_name' => $name,
                'type' => $type,
                'validation' => null,
                'position' => $position++,
                'is_required' => 0,
                'is_unique' => 0,
                'is_filterable' => $filter ? 1 : 0,
                'is_comparable' => 1,
                'is_configurable' => 0,
                'is_user_defined' => 1,
                'is_visible_on_front' => 1,
                'value_per_locale' => 0,
                'value_per_channel' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->customAttributeIds[$code] = $id;

            foreach (self::LOCALES as $locale) {
                DB::table('attribute_translations')->insert([
                    'attribute_id' => $id,
                    'name' => $name,
                    'locale' => $locale,
                ]);
            }
        }

        $this->seedAttributeOptions();
    }

    private function seedAttributeOptions(): void
    {
        $map = [
            'medical_brand' => ['Argo', 'Baxter', 'Hans Heiss', 'Kowa', 'MediWorks', 'Potec', 'Reichert', 'Tomey', 'Unicos'],
            'eyewear_brand' => ['Tucan Eyewear', 'Derigo', 'Minima', 'OnDeck', 'Closeouts', 'Komiko', 'Mimito', 'Vanguard'],
            'distribution_region' => ['USA', 'LATAM', 'USA and LATAM'],
            'gender' => ['Unisex', 'Men', 'Women', 'Kids'],
        ];

        foreach ($map as $code => $options) {
            $attributeId = $this->customAttributeIds[$code];

            foreach ($options as $index => $label) {
                $optionId = DB::table('attribute_options')->insertGetId([
                    'admin_name' => $label,
                    'swatch_value' => null,
                    'sort_order' => $index + 1,
                    'attribute_id' => $attributeId,
                ]);

                foreach (self::LOCALES as $locale) {
                    DB::table('attribute_option_translations')->insert([
                        'attribute_option_id' => $optionId,
                        'label' => $label,
                        'locale' => $locale,
                    ]);
                }
            }
        }
    }

    private function seedFamilies(): void
    {
        $families = [
            'Medical Equipment' => ['medical_brand', 'model', 'device_type', 'clinical_use', 'measurement_range', 'display_type', 'connectivity', 'power_supply', 'portability', 'warranty', 'country_of_origin', 'included_accessories', 'technical_specifications', 'certification', 'ships_from', 'distribution_region'],
            'Ophthalmic Equipment' => ['medical_brand', 'model', 'device_type', 'clinical_use', 'measurement_range', 'display_type', 'connectivity', 'power_supply', 'portability', 'warranty', 'country_of_origin', 'included_accessories', 'technical_specifications', 'certification', 'ships_from', 'distribution_region', 'diagnostic_function', 'exam_type', 'imaging_capability', 'patient_application', 'clinic_environment'],
            'Optical Equipment' => ['medical_brand', 'model', 'device_type', 'optical_use', 'measurement_type', 'calibration_required', 'portability', 'technical_specifications', 'warranty', 'ships_from', 'distribution_region'],
            'Clinical Accessory' => ['medical_brand', 'accessory_type', 'compatible_devices', 'material', 'pack_quantity', 'usage_environment', 'warranty', 'ships_from', 'distribution_region'],
            'Optical Frame' => ['eyewear_brand', 'model', 'frame_type', 'gender', 'material', 'shape', 'frame_color', 'lens_width', 'bridge_width', 'temple_length', 'size', 'rim_type', 'collection', 'style', 'commercial_segment', 'warranty', 'ships_from', 'distribution_region'],
            'Sunglasses' => ['eyewear_brand', 'model', 'sunglass_type', 'gender', 'material', 'shape', 'frame_color', 'lens_color', 'lens_type', 'uv_protection', 'polarized', 'lens_width', 'bridge_width', 'temple_length', 'collection', 'style', 'commercial_segment', 'warranty', 'ships_from', 'distribution_region'],
        ];

        $common = ['name', 'url_key', 'status', 'visible_individually', 'short_description', 'description', 'meta_title', 'meta_keywords', 'meta_description', 'price', 'cost', 'special_price'];
        $badges = ['new_arrival', 'best_seller', 'professional_grade', 'ships_from_miami', 'latam_distribution', 'clinical_solution', 'premium_frame', 'high_rotation', 'b2b_favorite'];

        foreach ($families as $familyName => $customCodes) {
            $familyId = DB::table('attribute_families')->insertGetId([
                'code' => Str::slug($familyName, '_'),
                'name' => $familyName,
                'status' => 1,
                'is_user_defined' => 1,
            ]);

            $groupId = DB::table('attribute_groups')->insertGetId([
                'attribute_family_id' => $familyId,
                'name' => 'General',
                'position' => 1,
                'is_user_defined' => 1,
            ]);

            $codes = array_merge($common, $customCodes, $badges);
            $position = 1;

            foreach ($codes as $code) {
                $attributeId = $this->systemAttributeIds[$code] ?? $this->customAttributeIds[$code] ?? null;

                if (! $attributeId) {
                    continue;
                }

                DB::table('attribute_group_mappings')->insert([
                    'attribute_id' => $attributeId,
                    'attribute_group_id' => $groupId,
                    'position' => $position++,
                ]);
            }
        }
    }

    private function seedProducts(): void
    {
        $familyIds = DB::table('attribute_families')->where('is_user_defined', 1)->pluck('id', 'name')->all();
        $channelId = $this->targetChannelId;
        $inventorySourceId = DB::table('inventory_sources')->value('id') ?? 1;

        $products = array_merge(
            $this->medicalProducts(),
            $this->opticalEquipmentProducts(),
            $this->clinicalAccessoryProducts(),
            $this->frameProducts(),
            $this->sunglassesProducts(),
        );

        foreach ($products as $product) {
            $productId = DB::table('products')->insertGetId([
                'sku' => $product['sku'],
                'type' => 'simple',
                'attribute_family_id' => $familyIds[$product['family']],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->assignProductToCategoryHierarchy($productId, $this->categoryIds[$product['category']]);

            DB::table('product_channels')->insert([
                'product_id' => $productId,
                'channel_id' => $channelId,
            ]);

            DB::table('product_inventories')->insert([
                'qty' => $product['stock'],
                'product_id' => $productId,
                'vendor_id' => 0,
                'inventory_source_id' => $inventorySourceId,
            ]);

            $this->seedProductAttributes($productId, $product);
        }
    }

    private function seedProductAttributes(int $productId, array $product): void
    {
        $this->insertValue($productId, 'price', 'float_value', $product['price']);
        $this->insertValue($productId, 'cost', 'float_value', round($product['price'] * 0.62, 2));
        $this->insertValue($productId, 'special_price', 'float_value', round($product['price'] * 0.93, 2));
        $this->insertValue($productId, 'status', 'boolean_value', 1);
        $this->insertValue($productId, 'visible_individually', 'boolean_value', 1);

        foreach ($product['attributes'] as $code => $value) {
            if (in_array($code, ['medical_brand', 'eyewear_brand', 'distribution_region', 'gender'], true)) {
                $this->insertValue($productId, $code, 'integer_value', $this->optionId($code, (string) $value));

                continue;
            }

            if (is_bool($value)) {
                $this->insertValue($productId, $code, 'boolean_value', $value ? 1 : 0);

                continue;
            }

            if (is_int($value)) {
                $this->insertValue($productId, $code, 'integer_value', $value);

                continue;
            }

            $this->insertValue($productId, $code, 'text_value', (string) $value);
        }

        foreach (self::LOCALES as $locale) {
            $copy = $this->localizedCopy($product, $locale);
            $this->insertValue($productId, 'name', 'text_value', $copy['name'], $locale, null);
            $this->insertValue($productId, 'url_key', 'text_value', $copy['slug'], $locale, null);
            $this->insertValue($productId, 'short_description', 'text_value', $copy['short_description'], $locale, null);
            $this->insertValue($productId, 'description', 'text_value', $copy['description'], $locale, null);
            $this->insertValue($productId, 'meta_title', 'text_value', $copy['meta_title'], $locale, null);
            $this->insertValue($productId, 'meta_keywords', 'text_value', $this->categoryKeywords($locale), $locale, null);
            $this->insertValue($productId, 'meta_description', 'text_value', $copy['meta_description'], $locale, null);
        }
    }

    private function medicalProducts(): array
    {
        $rows = [
            ['Potec', 'PRK900', 'Potec PRK-900 Auto Refractometer', 'auto-refractors', 'Ophthalmic Equipment'],
            ['Tomey', 'TL400', 'Tomey TL-400 Digital Lensmeter', 'lensmeters', 'Ophthalmic Equipment'],
            ['Kowa', 'RV500', 'Kowa RetinaView Fundus Camera', 'fundus-cameras', 'Ophthalmic Equipment'],
            ['MediWorks', 'SL650', 'MediWorks SL-650 Slit Lamp', 'slit-lamps', 'Ophthalmic Equipment'],
            ['Reichert', 'CDS300', 'Reichert Clinical Diagnostic Station', 'ophthalmic-equipment', 'Medical Equipment'],
            ['Unicos', 'PVS200', 'Unicos Portable Vision Screener', 'portable-devices', 'Ophthalmic Equipment'],
            ['Argo', 'ARS820', 'Argo Smart Auto Refractor', 'auto-refractors', 'Ophthalmic Equipment'],
            ['Baxter', 'BLS510', 'Baxter Digital Lensmeter', 'lensmeters', 'Ophthalmic Equipment'],
            ['Hans Heiss', 'HSL700', 'Hans Heiss Precision Slit Lamp', 'slit-lamps', 'Ophthalmic Equipment'],
            ['Kowa', 'KFC810', 'Kowa Clinical Fundus Camera', 'fundus-cameras', 'Ophthalmic Equipment'],
            ['Potec', 'PPR100', 'Potec Portable Refractor Unit', 'portable-devices', 'Ophthalmic Equipment'],
            ['Tomey', 'TAR950', 'Tomey Advanced Auto Refractor', 'auto-refractors', 'Ophthalmic Equipment'],
            ['MediWorks', 'MDL420', 'MediWorks Digital Lensmeter', 'lensmeters', 'Ophthalmic Equipment'],
            ['Argo', 'ASL730', 'Argo Clinical Slit Lamp', 'slit-lamps', 'Ophthalmic Equipment'],
            ['Reichert', 'RFC920', 'Reichert Fundus Capture System', 'fundus-cameras', 'Ophthalmic Equipment'],
            ['Unicos', 'UPD110', 'Unicos Portable Diagnostic Device', 'portable-devices', 'Ophthalmic Equipment'],
            ['Baxter', 'BAR220', 'Baxter Auto Refraction Unit', 'auto-refractors', 'Ophthalmic Equipment'],
            ['Hans Heiss', 'HLM330', 'Hans Heiss Lens Measurement Device', 'lensmeters', 'Ophthalmic Equipment'],
            ['Kowa', 'KSL440', 'Kowa Precision Slit Lamp', 'slit-lamps', 'Ophthalmic Equipment'],
            ['Tomey', 'TFC550', 'Tomey Fundus Capture Pro', 'fundus-cameras', 'Ophthalmic Equipment'],
        ];

        return $this->buildProductRows($rows, 'LGM-MED', 7600, 'medical_brand');
    }

    private function opticalEquipmentProducts(): array
    {
        $rows = [
            ['Argo', 'TLS100', 'Argo Trial Lens Set', 'trial-lens-sets', 'Optical Equipment'],
            ['Unicos', 'PH210', 'Unicos Digital Phoropter', 'phoropters', 'Optical Equipment'],
            ['MediWorks', 'CP320', 'MediWorks Chart Projector', 'chart-projectors', 'Optical Equipment'],
            ['Baxter', 'VS430', 'Baxter Vision Screener', 'vision-screeners', 'Optical Equipment'],
            ['Reichert', 'PH540', 'Reichert Precision Phoropter', 'phoropters', 'Optical Equipment'],
            ['Potec', 'CP650', 'Potec High Definition Chart Projector', 'chart-projectors', 'Optical Equipment'],
        ];

        return $this->buildProductRows($rows, 'LGM-OPT', 5200, 'medical_brand');
    }

    private function clinicalAccessoryProducts(): array
    {
        $rows = [
            ['Baxter', 'CSK100', 'Baxter Clinical Supply Kit', 'diagnostic-accessories', 'Clinical Accessory'],
            ['Hans Heiss', 'PDT210', 'Hans Heiss Precision Diagnostic Tool', 'diagnostic-accessories', 'Clinical Accessory'],
            ['Argo', 'ERP330', 'Argo Exam Room Accessory Pack', 'exam-room-accessories', 'Clinical Accessory'],
            ['Unicos', 'RPT440', 'Unicos Replacement Parts Bundle', 'replacement-parts', 'Clinical Accessory'],
        ];

        return $this->buildProductRows($rows, 'LGM-ACC', 480, 'medical_brand');
    }

    private function frameProducts(): array
    {
        $rows = [
            ['Tucan Eyewear', 'CLASSIC', 'Tucan Classic Acetate Frame', 'optical-frames', 'Optical Frame', 'BLK'],
            ['Derigo', 'EXECUTIVE', 'Derigo Executive Optical Frame', 'optical-frames', 'Optical Frame', 'BRN'],
            ['Minima', 'AIRFLEX', 'Minima AirFlex Rimless Frame', 'optical-frames', 'Optical Frame', 'SLV'],
            ['OnDeck', 'URBAN', 'OnDeck Urban Metal Frame', 'optical-frames', 'Optical Frame', 'BLU'],
            ['Komiko', 'ESSENTIAL', 'Komiko Essential Optical Frame', 'optical-frames', 'Optical Frame', 'BLK'],
            ['Vanguard', 'TITANIUM', 'Vanguard Titanium Optical Frame', 'optical-frames', 'Optical Frame', 'GRY'],
        ];

        return $this->buildEyewearRows($rows, 'LGM-FR', 220);
    }

    private function sunglassesProducts(): array
    {
        $rows = [
            ['Tucan Eyewear', 'POLARIZED', 'Tucan Polarized Sunglasses', 'sunglasses', 'Sunglasses', 'BLK'],
            ['Derigo', 'PREMIUMSUN', 'Derigo Premium Sun Collection', 'sunglasses', 'Sunglasses', 'BRN'],
            ['Vanguard', 'OUTDOOR', 'Vanguard Outdoor Sunglasses', 'sunglasses', 'Sunglasses', 'GRN'],
            ['OnDeck', 'LIFESTYLE', 'OnDeck Lifestyle Sunglasses', 'sunglasses', 'Sunglasses', 'BLU'],
        ];

        return $this->buildEyewearRows($rows, 'LGM-SG', 260);
    }

    private function buildProductRows(array $rows, string $skuPrefix, float $basePrice, string $brandCode): array
    {
        $result = [];

        foreach ($rows as $index => $row) {
            [$brand, $model, $name, $category, $family] = $row;

            $result[] = [
                'sku' => $skuPrefix.'-'.Str::upper(Str::slug($brand, '')).'-'.Str::upper($model),
                'name' => $name,
                'family' => $family,
                'category' => $category,
                'price' => $basePrice + ($index * 145),
                'stock' => 12 + ($index * 3),
                'attributes' => [
                    $brandCode => $brand,
                    'model' => $model,
                    'device_type' => $family,
                    'clinical_use' => 'Professional ophthalmic solutions for modern clinics.',
                    'technical_specifications' => 'Clinical technology for better operational efficiency.',
                    'measurement_range' => '+/- 25D',
                    'display_type' => 'HD Touch Panel',
                    'connectivity' => 'LAN, USB, HDMI',
                    'power_supply' => '110-240V AC',
                    'portability' => 'Desktop',
                    'warranty' => '24 months',
                    'country_of_origin' => 'Japan',
                    'included_accessories' => 'Power adapter, calibration card, user manual',
                    'certification' => 'FDA listed, CE certified',
                    'ships_from' => 'Miami, Florida, USA',
                    'distribution_region' => 'USA and LATAM',
                    'diagnostic_function' => 'Refraction and diagnostic assessment',
                    'exam_type' => 'General ophthalmic exam',
                    'imaging_capability' => 'High resolution capture',
                    'patient_application' => 'Adult and pediatric',
                    'clinic_environment' => 'Clinic, optical chain, consultory',
                    'optical_use' => 'Optometric support',
                    'measurement_type' => 'Diopter and axis',
                    'calibration_required' => true,
                    'accessory_type' => 'Clinical accessory',
                    'compatible_devices' => 'Auto refractor, slit lamp, fundus camera',
                    'material' => 'Medical grade polymer',
                    'pack_quantity' => 1,
                    'usage_environment' => 'Exam room',
                    'new_arrival' => $index % 3 === 0,
                    'best_seller' => $index % 4 === 0,
                    'professional_grade' => true,
                    'ships_from_miami' => true,
                    'latam_distribution' => true,
                    'clinical_solution' => true,
                    'premium_frame' => false,
                    'high_rotation' => $index % 2 === 0,
                    'b2b_favorite' => $index % 5 === 0,
                ],
            ];
        }

        return $result;
    }

    private function buildEyewearRows(array $rows, string $skuPrefix, float $basePrice): array
    {
        $result = [];

        foreach ($rows as $index => $row) {
            [$brand, $model, $name, $category, $family, $color] = $row;

            $result[] = [
                'sku' => $skuPrefix.'-'.Str::upper(Str::slug($brand, '')).'-'.Str::upper($model).'-'.$color,
                'name' => $name,
                'family' => $family,
                'category' => $category,
                'price' => $basePrice + ($index * 22),
                'stock' => 35 + ($index * 5),
                'attributes' => [
                    'eyewear_brand' => $brand,
                    'model' => $model,
                    'frame_type' => 'Full rim',
                    'sunglass_type' => 'Lifestyle',
                    'gender' => 'Unisex',
                    'material' => 'Acetate and lightweight metal',
                    'shape' => 'Rectangle',
                    'frame_color' => $color,
                    'lens_color' => 'Smoke',
                    'lens_type' => 'UV400',
                    'uv_protection' => 'UV400',
                    'polarized' => $family === 'Sunglasses',
                    'lens_width' => '52mm',
                    'bridge_width' => '18mm',
                    'temple_length' => '140mm',
                    'size' => 'M',
                    'rim_type' => 'Full Rim',
                    'collection' => 'Lag Medical Signature',
                    'style' => 'Professional Modern',
                    'commercial_segment' => 'Premium optical retail',
                    'warranty' => '12 months',
                    'ships_from' => 'Miami, Florida, USA',
                    'distribution_region' => 'USA and LATAM',
                    'new_arrival' => true,
                    'best_seller' => $index % 2 === 0,
                    'professional_grade' => false,
                    'ships_from_miami' => true,
                    'latam_distribution' => true,
                    'clinical_solution' => false,
                    'premium_frame' => true,
                    'high_rotation' => true,
                    'b2b_favorite' => true,
                ],
            ];
        }

        return $result;
    }

    private function localizedCopy(array $product, string $locale): array
    {
        return match ($locale) {
            'es' => [
                'name' => $product['name'],
                'slug' => Str::slug($product['name']).'-es',
                'short_description' => 'Portafolio premium para profesionales opticos con distribucion B2B desde Miami para USA y LATAM.',
                'description' => 'Professional ophthalmic solutions for modern clinics. Este producto fue preparado para opticas, clinicas visuales, consultorios y distribuidores que requieren soporte comercial y especificaciones tecnicas confiables.',
                'meta_title' => $product['name'].' | Lag Medical Inc.',
                'meta_description' => 'Equipos oftalmicos, optical frames wholesale y distribucion profesional desde Miami para USA y LATAM.',
            ],
            'pt_BR' => [
                'name' => $product['name'],
                'slug' => Str::slug($product['name']).'-pt-br',
                'short_description' => 'Portifolio premium para profissionais opticos com distribuicao B2B desde Miami para EUA e LATAM.',
                'description' => 'Professional ophthalmic solutions for modern clinics. Este produto atende oticas, clinicas visuais, consultorios e distribuidores que precisam de suporte comercial e especificacoes tecnicas confiaveis.',
                'meta_title' => $product['name'].' | Lag Medical Inc.',
                'meta_description' => 'Equipamentos oftalmicos, armacoes opticas no atacado e distribuicao profissional desde Miami para EUA e LATAM.',
            ],
            default => [
                'name' => $product['name'],
                'slug' => Str::slug($product['name']).'-en',
                'short_description' => 'Premium product portfolio for optical professionals with B2B distribution from Miami to USA and LATAM.',
                'description' => 'Professional ophthalmic solutions for modern clinics. This product is designed for optical stores, visual clinics, consulting rooms and distributors that require reliable technical specifications and commercial continuity.',
                'meta_title' => $product['name'].' | Lag Medical Inc.',
                'meta_description' => 'Ophthalmic equipment distributor, optical frames wholesale and optometry equipment supplier based in Miami.',
            ],
        };
    }

    private function insertValue(int $productId, string $attributeCode, string $valueField, mixed $value, ?string $locale = null, ?string $channel = 'default'): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $attributeId = $this->systemAttributeIds[$attributeCode] ?? $this->customAttributeIds[$attributeCode] ?? null;

        if (! $attributeId) {
            return;
        }

        DB::table('product_attribute_values')->insert([
            'product_id' => $productId,
            'attribute_id' => $attributeId,
            'locale' => $locale,
            'channel' => $channel,
            'text_value' => $valueField === 'text_value' ? $value : null,
            'boolean_value' => $valueField === 'boolean_value' ? $value : null,
            'integer_value' => $valueField === 'integer_value' ? $value : null,
            'float_value' => $valueField === 'float_value' ? $value : null,
            'datetime_value' => null,
            'date_value' => null,
            'json_value' => null,
        ]);
    }

    private function optionId(string $attributeCode, string $label): ?int
    {
        $attributeId = $this->customAttributeIds[$attributeCode] ?? null;

        if (! $attributeId) {
            return null;
        }

        return DB::table('attribute_options')
            ->where('attribute_id', $attributeId)
            ->where('admin_name', $label)
            ->value('id');
    }

    private function syncTargetChannelLocalesAndRoot(): void
    {
        $localeIds = DB::table('locales')->whereIn('code', self::LOCALES)->pluck('id')->all();

        DB::table('channels')->where('id', $this->targetChannelId)->update(['root_category_id' => 1]);

        foreach ($localeIds as $localeId) {
            DB::table('channel_locales')->updateOrInsert([
                'channel_id' => $this->targetChannelId,
                'locale_id' => $localeId,
            ], [
                'channel_id' => $this->targetChannelId,
                'locale_id' => $localeId,
            ]);
        }
    }

    private function assignProductToCategoryHierarchy(int $productId, int $categoryId): void
    {
        $currentCategoryId = $categoryId;

        while ($currentCategoryId) {
            DB::table('product_categories')->insertOrIgnore([
                'product_id' => $productId,
                'category_id' => $currentCategoryId,
            ]);

            $currentCategoryId = DB::table('categories')
                ->where('id', $currentCategoryId)
                ->value('parent_id');
        }
    }

    private function categoryDescription(string $name, string $locale): string
    {
        return match ($locale) {
            'es' => $name.' para distribucion B2B profesional desde Miami hacia USA y LATAM.',
            'pt_BR' => $name.' para distribuicao B2B profissional desde Miami para EUA e LATAM.',
            default => $name.' for professional B2B distribution from Miami to USA and LATAM.',
        };
    }

    private function categoryKeywords(string $locale): string
    {
        return match ($locale) {
            'es' => 'equipos oftalmicos, armazones opticos al mayor, distribuidor oftalmico en Miami',
            'pt_BR' => 'equipamentos oftalmicos, armacoes opticas no atacado, distribuidor oftalmico em Miami',
            default => 'ophthalmic equipment distributor, optical frames wholesale, ophthalmic devices Miami',
        };
    }
}
