<?php

namespace LagMedical\CmsPro\Services;

use Illuminate\Support\Str;

class BlockRegistry
{
    public function definitions(): array
    {
        return [
            'hero' => $this->block('Banner', 'Banner con imagen, video o carrusel.', [
                $this->select('mode', 'Formato', ['image' => 'Imagen', 'video' => 'Video', 'carousel' => 'Carrusel'], 'image'),
                $this->select('heading_level', 'Etiqueta del título', ['h1' => 'H1', 'h2' => 'H2'], 'h1'),
                $this->text('eyebrow', 'Antetítulo'),
                $this->text('title', 'Título', 'Nueva colección'),
                $this->textarea('text', 'Descripción'),
                $this->media('media_id', 'Imagen', ['mode' => 'image']),
                $this->url('video_url', 'URL del video', ['mode' => 'video']),
                $this->media('poster_id', 'Poster del video', ['mode' => 'video']),
                $this->text('button_label', 'Texto del botón'),
                $this->url('button_url', 'Enlace del botón'),
                $this->repeater('slides', 'Diapositivas', [
                    $this->media('media_id', 'Imagen'),
                    $this->text('title', 'Título'),
                    $this->textarea('text', 'Descripción'),
                    $this->text('button_label', 'Texto del botón'),
                    $this->url('button_url', 'Enlace del botón'),
                ], ['mode' => 'carousel']),
            ]),
            'rich_text' => $this->block('Texto', 'Contenido editorial semántico.', [
                $this->select('heading_level', 'Etiqueta del título', ['h2' => 'H2', 'h3' => 'H3', 'none' => 'Sin título'], 'h2'),
                $this->text('title', 'Título'),
                $this->textarea('content', 'Contenido'),
                $this->select('align', 'Alineación', ['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha'], 'left'),
            ]),
            'media_text' => $this->block('Imagen y texto', 'Contenido en dos columnas.', [
                $this->media('media_id', 'Imagen'),
                $this->text('image_alt', 'Texto alternativo'),
                $this->select('media_position', 'Posición', ['left' => 'Imagen izquierda', 'right' => 'Imagen derecha'], 'left'),
                $this->text('eyebrow', 'Antetítulo'),
                $this->text('title', 'Título'),
                $this->textarea('content', 'Contenido'),
                $this->text('button_label', 'Texto del botón'),
                $this->url('button_url', 'Enlace del botón'),
            ]),
            'feature_cards' => $this->block('Tarjetas', 'Beneficios, servicios o características.', [
                $this->text('title', 'Título de sección'),
                $this->select('columns', 'Columnas', ['2' => '2', '3' => '3', '4' => '4'], '3'),
                $this->repeater('items', 'Tarjetas', [
                    $this->media('media_id', 'Imagen'),
                    $this->text('title', 'Título'),
                    $this->textarea('text', 'Texto'),
                    $this->url('url', 'Enlace'),
                ]),
            ]),
            'product_grid' => $this->productBlock('Productos del catálogo mayorista'),
            'product_carousel' => $this->productBlock('Líneas destacadas del catálogo'),
            'logo_strip' => $this->block('Marcas', 'Franja de logotipos enlazables.', [
                $this->text('title', 'Título'),
                $this->repeater('items', 'Logotipos', [
                    $this->media('media_id', 'Logotipo'),
                    $this->text('name', 'Nombre de marca'),
                    $this->url('url', 'Enlace'),
                ]),
            ]),
            'gallery' => $this->block('Galería', 'Galería responsive de imágenes.', [
                $this->text('title', 'Título'),
                $this->select('columns', 'Columnas', ['2' => '2', '3' => '3', '4' => '4'], '3'),
                $this->repeater('items', 'Imágenes', [
                    $this->media('media_id', 'Imagen'),
                    $this->text('alt', 'Texto alternativo'),
                    $this->text('caption', 'Descripción'),
                ]),
            ]),
            'video' => $this->block('Video', 'Video externo con poster.', [
                $this->text('title', 'Título'),
                $this->url('video_url', 'URL del video'),
                $this->media('poster_id', 'Poster'),
                $this->textarea('caption', 'Descripción'),
            ]),
            'accordion' => $this->block('Acordeón / FAQ', 'Preguntas frecuentes con datos estructurados.', [
                $this->text('title', 'Título'),
                $this->switch('faq_schema', 'Activar datos estructurados FAQ', true),
                $this->repeater('items', 'Preguntas', [
                    $this->text('title', 'Pregunta'),
                    $this->textarea('content', 'Respuesta'),
                ]),
            ]),
            'testimonials' => $this->block('Testimonios', 'Prueba social.', [
                $this->text('title', 'Título'),
                $this->repeater('items', 'Testimonios', [
                    $this->textarea('quote', 'Testimonio'),
                    $this->text('name', 'Nombre'),
                    $this->text('role', 'Cargo o empresa'),
                    $this->media('media_id', 'Foto'),
                ]),
            ]),
            'stats' => $this->block('Indicadores', 'Cifras y resultados.', [
                $this->text('title', 'Título'),
                $this->repeater('items', 'Indicadores', [
                    $this->text('value', 'Valor'),
                    $this->text('label', 'Descripción'),
                ]),
            ]),
            'cta' => $this->block('Llamada a la acción', 'Bloque destacado de conversión.', [
                $this->text('title', 'Título'),
                $this->textarea('text', 'Descripción'),
                $this->text('button_label', 'Texto del botón'),
                $this->url('button_url', 'Enlace del botón'),
                $this->media('background_id', 'Imagen de fondo'),
            ]),
            'comparison' => $this->block('Tabla comparativa', 'Comparación accesible de opciones.', [
                $this->text('title', 'Título'),
                $this->repeater('items', 'Filas', [
                    $this->text('label', 'Característica'),
                    $this->text('value', 'Detalle'),
                ]),
            ]),
            'custom_html' => $this->block('HTML personalizado', 'HTML adicional para esta página.', [
                $this->code('html', 'HTML', 'html'),
            ]),
        ];
    }

    public function templates(): array
    {
        return [
            'brand_landing' => [
                'name' => 'Landing de marca',
                'blocks' => [
                    $this->make('hero'),
                    $this->make('logo_strip'),
                    $this->make('media_text'),
                    $this->make('product_carousel'),
                    $this->make('feature_cards'),
                    $this->make('accordion'),
                    $this->make('gallery'),
                    $this->make('testimonials'),
                    $this->make('stats'),
                    $this->make('comparison'),
                    $this->make('cta'),
                ],
            ],
            'campaign' => [
                'name' => 'Campaña de captación',
                'blocks' => [
                    $this->make('hero'),
                    $this->make('feature_cards'),
                    $this->make('stats'),
                    $this->make('testimonials'),
                    $this->make('cta'),
                ],
            ],
            'catalog' => [
                'name' => 'Catálogo mayorista',
                'blocks' => [
                    $this->make('hero'),
                    $this->make('product_grid'),
                    $this->make('media_text'),
                    $this->make('accordion'),
                    $this->make('cta'),
                ],
            ],
            'editorial' => [
                'name' => 'Página editorial',
                'blocks' => [
                    $this->make('hero'),
                    $this->make('rich_text'),
                    $this->make('media_text'),
                    $this->make('gallery'),
                    $this->make('cta'),
                ],
            ],
        ];
    }

    public function sanitize(array $blocks): array
    {
        $definitions = $this->definitions();

        return collect($blocks)
            ->filter(fn ($block) => is_array($block) && isset($definitions[$block['type'] ?? '']))
            ->take(80)
            ->map(function ($block) use ($definitions) {
                $definition = $definitions[$block['type']];

                return [
                    'id' => preg_replace('/[^a-zA-Z0-9_-]/', '', $block['id'] ?? '') ?: (string) Str::uuid(),
                    'type' => $block['type'],
                    'data' => $this->sanitizeFields($block['data'] ?? [], $definition['fields']),
                    'settings' => $this->sanitizeSettings($block['settings'] ?? []),
                ];
            })
            ->values()
            ->all();
    }

    public function make(string $type): array
    {
        $definition = $this->definitions()[$type];
        $data = [];

        foreach ($definition['fields'] as $field) {
            $data[$field['key']] = $field['default'] ?? ($field['type'] === 'repeater' ? [] : '');
        }

        return [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => $data,
            'settings' => $this->defaultSettings(),
        ];
    }

    public function defaultSettings(): array
    {
        return [
            'container' => 'wide',
            'background' => '',
            'text_color' => '',
            'padding_top' => 64,
            'padding_bottom' => 64,
            'mobile_padding' => 32,
            'hide_desktop' => false,
            'hide_mobile' => false,
            'anchor' => '',
        ];
    }

    protected function sanitizeFields(array $data, array $fields): array
    {
        $clean = [];

        foreach ($fields as $field) {
            $value = $data[$field['key']] ?? ($field['default'] ?? '');

            if ($field['type'] === 'repeater') {
                $clean[$field['key']] = collect(is_array($value) ? $value : [])
                    ->take(30)
                    ->map(fn ($item) => $this->sanitizeFields(is_array($item) ? $item : [], $field['fields']))
                    ->values()
                    ->all();

                continue;
            }

            $clean[$field['key']] = match ($field['type']) {
                'number', 'media' => max(0, (int) $value),
                'switch' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'select' => array_key_exists((string) $value, $field['options']) ? (string) $value : $field['default'],
                'textarea' => clean_content((string) $value),
                'html' => clean_content((string) $value),
                'code' => Str::limit((string) $value, 50000, ''),
                'url' => $this->safeUrl((string) $value),
                default => Str::limit(strip_tags((string) $value), 2000, ''),
            };
        }

        return $clean;
    }

    protected function sanitizeSettings(array $settings): array
    {
        return [
            'container' => in_array($settings['container'] ?? '', ['full', 'wide', 'content']) ? $settings['container'] : 'wide',
            'background' => $this->color($settings['background'] ?? ''),
            'text_color' => $this->color($settings['text_color'] ?? ''),
            'padding_top' => min(200, max(0, (int) ($settings['padding_top'] ?? 64))),
            'padding_bottom' => min(200, max(0, (int) ($settings['padding_bottom'] ?? 64))),
            'mobile_padding' => min(120, max(0, (int) ($settings['mobile_padding'] ?? 32))),
            'hide_desktop' => filter_var($settings['hide_desktop'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'hide_mobile' => filter_var($settings['hide_mobile'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'anchor' => Str::slug($settings['anchor'] ?? ''),
        ];
    }

    protected function color(string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '';
    }

    protected function safeUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '' || str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return Str::limit($url, 2000, '');
        }

        return filter_var($url, FILTER_VALIDATE_URL)
            && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'])
                ? Str::limit($url, 2000, '')
                : '';
    }

    protected function productBlock(string $name): array
    {
        return $this->block($name, 'Productos obtenidos del catálogo de la tienda.', [
            $this->text('title', 'Título'),
            $this->select('source', 'Origen', ['featured' => 'Destacados', 'new' => 'Nuevos', 'category' => 'Categoría', 'manual' => 'IDs manuales'], 'featured'),
            $this->number('category_id', 'ID de categoría', 0, ['source' => 'category']),
            $this->text('product_ids', 'IDs separados por coma', '', ['source' => 'manual']),
            $this->number('limit', 'Cantidad', 8),
            $this->select('columns', 'Columnas', ['2' => '2', '3' => '3', '4' => '4'], '4'),
            $this->text('button_label', 'Texto de ver todos'),
            $this->url('button_url', 'Enlace de ver todos'),
        ]);
    }

    protected function block(string $name, string $description, array $fields): array
    {
        return compact('name', 'description', 'fields');
    }

    protected function text(string $key, string $label, string $default = '', array $showWhen = []): array
    {
        return $this->field('text', $key, $label, $default, $showWhen);
    }

    protected function textarea(string $key, string $label, string $default = '', array $showWhen = []): array
    {
        return $this->field('textarea', $key, $label, $default, $showWhen);
    }

    protected function code(string $key, string $label, string $language): array
    {
        return $this->field('code', $key, $label, '', [], ['language' => $language]);
    }

    protected function number(string $key, string $label, int $default = 0, array $showWhen = []): array
    {
        return $this->field('number', $key, $label, $default, $showWhen);
    }

    protected function media(string $key, string $label, array $showWhen = []): array
    {
        return $this->field('media', $key, $label, 0, $showWhen);
    }

    protected function url(string $key, string $label, array $showWhen = []): array
    {
        return $this->field('url', $key, $label, '', $showWhen);
    }

    protected function switch(string $key, string $label, bool $default = false): array
    {
        return $this->field('switch', $key, $label, $default);
    }

    protected function select(string $key, string $label, array $options, string $default): array
    {
        return array_merge($this->field('select', $key, $label, $default), compact('options'));
    }

    protected function repeater(string $key, string $label, array $fields, array $showWhen = []): array
    {
        return array_merge($this->field('repeater', $key, $label, [], $showWhen), compact('fields'));
    }

    protected function field(string $type, string $key, string $label, mixed $default = '', array $showWhen = [], array $extra = []): array
    {
        return array_merge([
            'type' => $type,
            'key' => $key,
            'label' => $label,
            'default' => $default,
            'show_when' => $showWhen,
        ], $extra);
    }
}
