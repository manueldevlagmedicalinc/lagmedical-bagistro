<?php

namespace LagMedical\CmsPro\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use LagMedical\CmsPro\Services\BlockRegistry;
use LagMedical\CmsPro\Services\CmsProService;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\CMS\Repositories\PageRepository;

class EditorController extends Controller
{
    public function __construct(
        protected PageRepository $pageRepository,
        protected CmsProService $cmsPro,
        protected BlockRegistry $blocks,
    ) {}

    public function edit(int $pageId): View
    {
        abort_unless(bouncer()->hasPermission('cms.pro.edit'), 403);

        $cmsPage = $this->pageRepository->findOrFail($pageId);

        abort_unless($this->cmsPro->isEnabledForPage($cmsPage), 403, 'CMS Pro no está activo para esta página.');

        $locale = core()->getRequestedLocaleCode();
        $content = $this->cmsPro->content($cmsPage->id, $locale);
        $channelId = $cmsPage->channels->first()?->id;

        return view('cms-pro::admin.editor', [
            'page' => $cmsPage,
            'locale' => $locale,
            'content' => $content,
            'definitions' => $this->blocks->definitions(),
            'templates' => $this->blocks->templates(),
            'media' => $this->cmsPro->media($channelId),
            'channelId' => $channelId,
            'revisions' => $this->cmsPro->revisions($cmsPage->id, $locale),
            'canUseCustomCode' => bouncer()->hasPermission('cms.pro.custom_code'),
        ]);
    }

    public function update(int $pageId): RedirectResponse
    {
        abort_unless(bouncer()->hasPermission('cms.pro.edit'), 403);

        $cmsPage = $this->pageRepository->findOrFail($pageId);

        abort_unless($this->cmsPro->isEnabledForPage($cmsPage), 403);

        $validated = request()->validate([
            'locale' => 'required|string|max:10|exists:locales,code',
            'blocks' => 'required|string|max:2000000',
            'status' => 'required|in:draft,published',
            'custom_css' => 'nullable|string|max:200000',
            'custom_js' => 'nullable|string|max:200000',
        ]);

        abort_unless($cmsPage->channels->contains(fn ($channel) => $channel->locales->contains('code', $validated['locale'])), 422);
        abort_if($validated['status'] === 'published' && ! bouncer()->hasPermission('cms.pro.publish'), 403);
        if (($validated['custom_css'] ?? '') !== '' || ($validated['custom_js'] ?? '') !== '') {
            abort_unless(bouncer()->hasPermission('cms.pro.custom_code'), 403, 'No tienes permiso para usar código personalizado.');
        }

        $decoded = json_decode($validated['blocks'], true);

        if (! is_array($decoded)) {
            return back()->withErrors(['blocks' => 'El contenido del editor no es válido.'])->withInput();
        }

        $this->cmsPro->save(
            $cmsPage->id,
            $validated['locale'],
            $this->blocks->sanitize($decoded),
            $validated['status'],
            $validated['custom_css'] ?? '',
            $validated['custom_js'] ?? '',
        );

        Event::dispatch('cms.page.update.after', $cmsPage);

        if ($validated['status'] === 'published') {
            $cmsPage->touch();
        }

        session()->flash('success', $validated['status'] === 'published'
            ? 'Página CMS Pro publicada correctamente.'
            : 'Borrador CMS Pro guardado correctamente.');

        return redirect()->route('admin.cms.pro.edit', [
            'pageId' => $cmsPage->id,
            'locale' => $validated['locale'],
        ]);
    }

    public function publish(int $pageId): RedirectResponse
    {
        abort_unless(bouncer()->hasPermission('cms.pro.publish'), 403);

        request()->merge(['status' => 'published']);

        return $this->update($pageId);
    }

    public function preview(int $pageId): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('cms.pro.edit'), 403);

        $cmsPage = $this->pageRepository->findOrFail($pageId);

        abort_unless($this->cmsPro->isEnabledForPage($cmsPage), 403);

        $validated = request()->validate([
            'blocks' => 'required|array|max:80',
            'custom_css' => 'nullable|string|max:200000',
            'custom_js' => 'nullable|string|max:200000',
        ]);
        if (($validated['custom_css'] ?? '') !== '' || ($validated['custom_js'] ?? '') !== '') {
            abort_unless(bouncer()->hasPermission('cms.pro.custom_code'), 403);
        }
        $blocks = $this->blocks->sanitize($validated['blocks']);
        $cmsProChannelId = $cmsPage->channels->first()?->id;

        return response()->json([
            'html' => view('cms-pro::shop.renderer', [
                'blocks' => $blocks,
                'cmsProChannelId' => $cmsProChannelId,
                'customCss' => $this->cmsPro->sanitizeCustomCode($validated['custom_css'] ?? '', 'css'),
                'customJs' => $this->cmsPro->sanitizeCustomCode($validated['custom_js'] ?? '', 'js'),
            ])->render(),
        ]);
    }

    public function restore(int $pageId, int $revision): RedirectResponse
    {
        abort_unless(bouncer()->hasPermission('cms.pro.edit'), 403);

        $cmsPage = $this->pageRepository->findOrFail($pageId);
        $locale = request()->string('locale')->toString() ?: core()->getRequestedLocaleCode();

        abort_unless($this->cmsPro->isEnabledForPage($cmsPage), 403);

        $record = DB::table('cms_pro_revisions as revisions')
            ->join('cms_pro_pages as pages', 'pages.id', '=', 'revisions.cms_pro_page_id')
            ->where('revisions.id', $revision)
            ->where('pages.cms_page_id', $cmsPage->id)
            ->where('pages.locale', $locale)
            ->select('revisions.*')
            ->first();

        abort_unless($record, 404);

        $this->cmsPro->save(
            $cmsPage->id,
            $locale,
            $this->blocks->sanitize(json_decode($record->blocks, true) ?: []),
            'draft',
            $record->custom_css ?? '',
            $record->custom_js ?? '',
        );

        session()->flash('success', 'La revisión fue restaurada como borrador.');

        return redirect()->route('admin.cms.pro.edit', ['pageId' => $cmsPage->id, 'locale' => $locale]);
    }
}
