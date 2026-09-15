<?php

namespace LagMedical\CmsPro\Tests\Unit;

use LagMedical\CmsPro\Services\BlockRegistry;
use LagMedical\CmsPro\Services\CmsProService;
use PHPUnit\Framework\TestCase;

class BlockRegistryTest extends TestCase
{
    public function test_it_exposes_the_required_editor_blocks_and_templates(): void
    {
        $registry = new BlockRegistry;

        $this->assertSame([
            'hero', 'rich_text', 'media_text', 'feature_cards', 'product_grid',
            'product_carousel', 'logo_strip', 'gallery', 'video', 'accordion',
            'testimonials', 'stats', 'cta', 'comparison', 'custom_html',
        ], array_keys($registry->definitions()));
        $this->assertArrayHasKey('brand_landing', $registry->templates());
        $this->assertArrayHasKey('campaign', $registry->templates());

        foreach ($registry->templates() as $template) {
            $this->assertNotEmpty($template['blocks']);

            foreach ($template['blocks'] as $block) {
                $this->assertArrayHasKey($block['type'], $registry->definitions());
            }
        }
    }

    public function test_it_rejects_unknown_blocks_and_unsafe_links(): void
    {
        $registry = new BlockRegistry;
        $blocks = $registry->sanitize([
            [
                'id' => 'safe-id',
                'type' => 'product_grid',
                'data' => [
                    'title' => '<b>Products</b>',
                    'source' => 'featured',
                    'columns' => '99',
                    'limit' => 200,
                    'button_label' => 'View all',
                    'button_url' => 'javascript:alert(1)',
                ],
                'settings' => [
                    'container' => 'invalid',
                    'background' => 'red',
                    'anchor' => 'Featured Products',
                ],
            ],
            ['type' => 'unknown'],
        ]);

        $this->assertCount(1, $blocks);
        $this->assertSame('Products', $blocks[0]['data']['title']);
        $this->assertSame('', $blocks[0]['data']['button_url']);
        $this->assertSame('4', $blocks[0]['data']['columns']);
        $this->assertSame('wide', $blocks[0]['settings']['container']);
        $this->assertSame('', $blocks[0]['settings']['background']);
        $this->assertSame('featured-products', $blocks[0]['settings']['anchor']);
    }

    public function test_it_only_generates_embeds_for_supported_video_providers(): void
    {
        $service = new CmsProService;

        $this->assertSame(
            'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            $service->videoEmbedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
        );
        $this->assertSame('https://player.vimeo.com/video/123456', $service->videoEmbedUrl('https://vimeo.com/123456'));
        $this->assertNull($service->videoEmbedUrl('https://example.com/video'));
    }

    public function test_page_code_cannot_break_out_of_its_container(): void
    {
        $service = new CmsProService;

        $this->assertSame('body { color: red; }', $service->sanitizeCustomCode('body { color: red; }</style>', 'css'));
        $this->assertSame('alert(1);', $service->sanitizeCustomCode('alert(1);</script>', 'js'));
    }
}
