<?php

namespace LagMedical\CmsPro\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CmsProService
{
    protected ?Collection $mediaCache = null;

    public function pageType(int $pageId): string
    {
        if (! Schema::hasTable('cms_pro_page_settings')) {
            return 'native';
        }

        return DB::table('cms_pro_page_settings')
            ->where('cms_page_id', $pageId)
            ->value('editor_type') ?: 'native';
    }

    public function isEnabledForPage(object $page): bool
    {
        return $this->pageType($page->id) === 'cms_pro';
    }

    public function setPageType(int $pageId, string $type): void
    {
        if (! Schema::hasTable('cms_pro_page_settings')) {
            return;
        }

        $settings = DB::table('cms_pro_page_settings')->where('cms_page_id', $pageId);
        $values = ['editor_type' => $type === 'cms_pro' ? 'cms_pro' : 'native', 'updated_at' => now()];

        if ($settings->exists()) {
            $settings->update($values);
        } else {
            DB::table('cms_pro_page_settings')->insert($values + [
                'cms_page_id' => $pageId,
                'created_at' => now(),
            ]);
        }
    }

    public function content(int $pageId, string $locale, bool $publishedOnly = false): ?object
    {
        if (! Schema::hasTable('cms_pro_pages')) {
            return null;
        }

        $query = DB::table('cms_pro_pages')
            ->where('cms_page_id', $pageId)
            ->where('locale', $locale);

        if ($publishedOnly) {
            $query->where('status', 'published');
        }

        $content = $query->first();

        if ($content) {
            $source = $publishedOnly
                ? $content->blocks
                : ($content->draft_blocks ?: $content->blocks);

            $content->blocks = json_decode($source ?: '[]', true) ?: [];
            $content->custom_css = $publishedOnly
                ? ($content->custom_css ?? '')
                : ($content->draft_custom_css ?? $content->custom_css ?? '');
            $content->custom_js = $publishedOnly
                ? ($content->custom_js ?? '')
                : ($content->draft_custom_js ?? $content->custom_js ?? '');
        }

        return $content;
    }

    public function save(int $pageId, string $locale, array $blocks, string $status, string $customCss = '', string $customJs = ''): object
    {
        return DB::transaction(function () use ($pageId, $locale, $blocks, $status, $customCss, $customJs) {
            $now = now();
            $json = json_encode($blocks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            DB::table('cms_pro_pages')->upsert([[
                'cms_page_id' => $pageId,
                'locale' => $locale,
                'schema_version' => 1,
                'status' => $status,
                'blocks' => $status === 'published' ? $json : null,
                'draft_blocks' => $json,
                'custom_css' => $status === 'published' ? $this->sanitizeCode($customCss, 'css') : null,
                'custom_js' => $status === 'published' ? $this->sanitizeCode($customJs, 'js') : null,
                'draft_custom_css' => $this->sanitizeCode($customCss, 'css'),
                'draft_custom_js' => $this->sanitizeCode($customJs, 'js'),
                'created_at' => $now,
                'updated_at' => $now,
            ]], ['cms_page_id', 'locale'], $status === 'published'
                ? ['schema_version', 'status', 'blocks', 'draft_blocks', 'custom_css', 'custom_js', 'draft_custom_css', 'draft_custom_js', 'updated_at']
                : ['schema_version', 'draft_blocks', 'draft_custom_css', 'draft_custom_js', 'updated_at']);

            $content = DB::table('cms_pro_pages')
                ->where('cms_page_id', $pageId)
                ->where('locale', $locale)
                ->lockForUpdate()
                ->first();

            DB::table('cms_pro_revisions')->insert([
                'cms_pro_page_id' => $content->id,
                'admin_id' => auth()->guard('admin')->id(),
                'status' => $status,
                'blocks' => $json,
                'custom_css' => $this->sanitizeCode($customCss, 'css'),
                'custom_js' => $this->sanitizeCode($customJs, 'js'),
                'created_at' => $now,
            ]);

            $keptRevisionIds = DB::table('cms_pro_revisions')
                ->where('cms_pro_page_id', $content->id)
                ->latest('id')
                ->limit(25)
                ->pluck('id');

            DB::table('cms_pro_revisions')
                ->where('cms_pro_page_id', $content->id)
                ->whereNotIn('id', $keptRevisionIds)
                ->delete();

            $content->blocks = $blocks;

            return $content;
        });
    }

    public function revisions(int $pageId, string $locale): Collection
    {
        if (! Schema::hasTable('cms_pro_revisions')) {
            return collect();
        }

        return DB::table('cms_pro_revisions as revisions')
            ->join('cms_pro_pages as pages', 'pages.id', '=', 'revisions.cms_pro_page_id')
            ->where('pages.cms_page_id', $pageId)
            ->where('pages.locale', $locale)
            ->select('revisions.id', 'revisions.status', 'revisions.created_at')
            ->latest('revisions.id')
            ->limit(25)
            ->get();
    }

    public function media(?int $channelId = null): Collection
    {
        if (! Schema::hasTable('cms_pro_media')) {
            return collect();
        }

        return DB::table('cms_pro_media')
            ->when($channelId, fn ($query) => $query->where(fn ($inner) => $inner
                ->whereNull('channel_id')
                ->orWhere('channel_id', $channelId)))
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(fn ($media) => $this->formatMedia($media));
    }

    public function findMedia(?int $id): ?object
    {
        if (! $id) {
            return null;
        }

        if ($this->mediaCache !== null) {
            return $this->mediaCache->get($id);
        }

        if (! Schema::hasTable('cms_pro_media')) {
            return null;
        }

        $media = DB::table('cms_pro_media')->find($id);

        return $media ? $this->formatMedia($media) : null;
    }

    public function primeMedia(array $blocks): void
    {
        if (! Schema::hasTable('cms_pro_media')) {
            $this->mediaCache = collect();

            return;
        }

        $ids = collect();
        $collect = function (array $values) use (&$collect, $ids): void {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $collect($value);
                } elseif (str_ends_with((string) $key, '_id') && $key !== 'category_id' && (int) $value > 0) {
                    $ids->push((int) $value);
                }
            }
        };

        $collect($blocks);

        $this->mediaCache = DB::table('cms_pro_media')
            ->whereIn('id', $ids->unique()->values())
            ->get()
            ->map(fn ($media) => $this->formatMedia($media))
            ->keyBy('id');
    }

    public function formatMedia(object $media): object
    {
        $media->url = Storage::disk($media->disk)->url($media->path);

        return $media;
    }

    public function videoEmbedUrl(?string $url): ?string
    {
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower(parse_url($url, PHP_URL_HOST) ?: '');
        $path = trim(parse_url($url, PHP_URL_PATH) ?: '', '/');

        if (in_array($host, ['youtu.be', 'www.youtu.be'])) {
            $id = strtok($path, '/');

            return preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $id) ? 'https://www.youtube-nocookie.com/embed/'.$id : null;
        }

        if (in_array($host, ['youtube.com', 'www.youtube.com'])) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);
            $id = $query['v'] ?? (str_starts_with($path, 'embed/') ? substr($path, 6) : '');

            return preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $id) ? 'https://www.youtube-nocookie.com/embed/'.$id : null;
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com']) && preg_match('/^\d+$/', $path)) {
            return 'https://player.vimeo.com/video/'.$path;
        }

        return null;
    }

    protected function sanitizeCode(string $code, string $type): string
    {
        $code = str_replace(["\r\n", "\r"], "\n", trim($code));
        $tag = $type === 'css' ? 'style' : 'script';

        return preg_replace('/<\/?'.$tag.'[^>]*>/i', '', substr($code, 0, 200000)) ?: '';
    }

    public function sanitizeCustomCode(string $code, string $type): string
    {
        return $this->sanitizeCode($code, $type);
    }
}
