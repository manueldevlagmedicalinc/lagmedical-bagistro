@if ($cmsProService->isEnabledForPage($page) && bouncer()->hasPermission('cms.pro.edit'))
    <div class="box-shadow mb-2.5 flex items-center justify-between gap-4 rounded bg-gradient-to-r from-slate-950 to-blue-950 p-5 text-white">
        <div>
            <div class="mb-1 flex items-center gap-2">
                <span class="rounded bg-blue-500 px-2 py-1 text-[10px] font-bold uppercase tracking-wider">CMS Pro</span>
                <p class="font-semibold">Editor visual responsive</p>
            </div>

            <p class="text-sm text-slate-300">
                 Diseña esta página con bloques, catálogos, marcas y medios reutilizables.
            </p>
        </div>

        <a
            href="{{ route('admin.cms.pro.edit', ['pageId' => $page->id, 'locale' => core()->getRequestedLocaleCode()]) }}"
            class="primary-button whitespace-nowrap"
        >
            Abrir CMS Pro
        </a>
    </div>
@endif
