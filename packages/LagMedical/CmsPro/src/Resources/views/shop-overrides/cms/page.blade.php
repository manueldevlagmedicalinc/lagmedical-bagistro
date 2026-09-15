@php
    $cmsProContent = $cmsProService->isEnabledForPage($page)
        ? $cmsProService->content($page->id, core()->getRequestedLocaleCode(), true)
        : null;
@endphp

@push('meta')
    <meta name="title" content="{{ $page->meta_title }}" />
    <meta name="description" content="{{ $page->meta_description }}" />
    <meta name="keywords" content="{{ $page->meta_keywords }}" />
@endPush

<x-shop::layouts>
    <x-slot:title>{{ $page->meta_title }}</x-slot>

    @if ($cmsProContent)
        @include('cms-pro::shop.renderer', ['blocks' => $cmsProContent->blocks, 'pageTitle' => $page->page_title, 'customCss' => $cmsProContent->custom_css ?? '', 'customJs' => $cmsProContent->custom_js ?? ''])
    @else
        <div class="container mt-8 px-[60px] max-lg:px-8">
            {!! $page->html_content !!}
        </div>
    @endif
</x-shop::layouts>
