@inject('productBlocks', 'LagMedical\CmsPro\Services\ProductBlockService')

@php($cmsProService->primeMedia($blocks))

@once
    <style>
        .cms-pro { --cms-accent: #1769aa; color: #172033; }
        .cms-pro-sr-only { clip: rect(0,0,0,0); clip-path: inset(50%); height: 1px; overflow: hidden; position: absolute; white-space: nowrap; width: 1px; }
        .cms-pro *, .cms-pro *::before, .cms-pro *::after { box-sizing: border-box; }
        .cms-pro-section { background: var(--cms-bg, transparent); color: var(--cms-color, inherit); padding-block: var(--cms-pt, 64px) var(--cms-pb, 64px); }
        .cms-pro-container { margin-inline: auto; padding-inline: clamp(16px, 4vw, 60px); width: 100%; }
        .cms-pro-container--wide { max-width: 1440px; }
        .cms-pro-container--content { max-width: 960px; }
        .cms-pro-heading { font-family: inherit; font-size: clamp(1.8rem, 4vw, 3.5rem); font-weight: 700; letter-spacing: -.03em; line-height: 1.08; }
        .cms-pro-section-title { font-size: clamp(1.5rem, 3vw, 2.35rem); font-weight: 700; letter-spacing: -.025em; line-height: 1.15; margin-bottom: 28px; }
        .cms-pro-eyebrow { color: var(--cms-accent); font-size: .75rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; }
        .cms-pro-copy { font-size: 1.05rem; line-height: 1.75; }
        .cms-pro-copy p + p { margin-top: 1em; }
        .cms-pro-button { align-items: center; background: var(--cms-accent); border-radius: 8px; color: #fff; display: inline-flex; font-weight: 700; justify-content: center; margin-top: 24px; min-height: 46px; padding: 10px 22px; text-decoration: none; }
        .cms-pro-button:hover { filter: brightness(.9); }
        .cms-pro-hero { display: grid; min-height: clamp(440px, 65vh, 720px); overflow: hidden; place-items: center; position: relative; }
        .cms-pro-hero__media { height: 100%; inset: 0; object-fit: cover; position: absolute; width: 100%; }
        .cms-pro-hero::after { background: linear-gradient(90deg, rgba(7,18,35,.82), rgba(7,18,35,.32)); content: ''; inset: 0; position: absolute; }
        .cms-pro-hero__content { color: #fff; max-width: 760px; padding-block: 80px; position: relative; z-index: 1; }
        .cms-pro-slides { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; }
        .cms-pro-slide { flex: 0 0 100%; scroll-snap-align: start; }
        .cms-pro-media-text { align-items: center; display: grid; gap: clamp(28px, 6vw, 80px); grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .cms-pro-media-text--right .cms-pro-media-text__media { order: 2; }
        .cms-pro-media-text img { aspect-ratio: 4 / 3; border-radius: 18px; height: 100%; object-fit: cover; width: 100%; }
        .cms-pro-grid { display: grid; gap: 24px; grid-template-columns: repeat(var(--cms-columns, 3), minmax(0, 1fr)); }
        .cms-pro-card { background: rgba(255,255,255,.92); border: 1px solid rgba(100,116,139,.2); border-radius: 14px; color: #172033; overflow: hidden; padding: 22px; }
        .cms-pro-card img { aspect-ratio: 4 / 3; border-radius: 9px; margin-bottom: 18px; object-fit: cover; width: 100%; }
        .cms-pro-card h3 { font-size: 1.1rem; font-weight: 700; }
        .cms-pro-card p { color: #64748b; line-height: 1.6; margin-top: 8px; }
        .cms-pro-product-grid { display: grid; gap: 24px; grid-template-columns: repeat(var(--cms-columns, 4), minmax(0, 1fr)); }
        .cms-pro-product-carousel { display: flex; gap: 22px; overflow-x: auto; padding-bottom: 12px; scroll-snap-type: x mandatory; }
        .cms-pro-product-carousel .cms-pro-product { flex: 0 0 min(280px, 75vw); scroll-snap-align: start; }
        .cms-pro-product { color: inherit; display: block; text-decoration: none; }
        .cms-pro-product img { aspect-ratio: 1; background: #f4f6f8; border-radius: 12px; object-fit: contain; width: 100%; }
        .cms-pro-product h3 { font-size: 1rem; font-weight: 700; margin-top: 12px; }
        .cms-pro-logos { align-items: center; display: flex; flex-wrap: wrap; gap: 36px; justify-content: center; }
        .cms-pro-logos img { height: 54px; max-width: 150px; object-fit: contain; }
        .cms-pro-gallery img { aspect-ratio: 4 / 3; border-radius: 12px; object-fit: cover; width: 100%; }
        .cms-pro-accordion details { border-top: 1px solid rgba(100,116,139,.3); padding-block: 20px; }
        .cms-pro-accordion summary { cursor: pointer; font-size: 1.05rem; font-weight: 700; }
        .cms-pro-accordion details .cms-pro-copy { margin-top: 14px; }
        .cms-pro-stats { text-align: center; }
        .cms-pro-stat strong { display: block; font-size: clamp(2rem, 5vw, 4rem); }
        .cms-pro-cta { background-position: center; background-size: cover; border-radius: 22px; overflow: hidden; padding: clamp(36px, 7vw, 90px); position: relative; text-align: center; }
        .cms-pro-cta::before { background: rgba(8,24,45,.68); content: ''; inset: 0; position: absolute; }
        .cms-pro-cta__content { color: #fff; margin: auto; max-width: 760px; position: relative; }
        .cms-pro-video { aspect-ratio: 16 / 9; background: #020617; border: 0; border-radius: 16px; width: 100%; }
        .cms-pro-hero__embed { border: 0; height: 100%; inset: 0; pointer-events: none; position: absolute; transform: scale(1.25); width: 100%; }
        .cms-pro-comparison { border-collapse: collapse; width: 100%; }
        .cms-pro-comparison th, .cms-pro-comparison td { border-bottom: 1px solid #dbe2ea; padding: 16px; text-align: left; }
        @media (max-width: 767px) {
            .cms-pro-section { padding-block: var(--cms-mobile-padding, 32px); }
            .cms-pro-hide-mobile { display: none !important; }
            .cms-pro-media-text { grid-template-columns: 1fr; }
            .cms-pro-media-text--right .cms-pro-media-text__media { order: 0; }
            .cms-pro-grid { grid-template-columns: 1fr; }
            .cms-pro-product-grid { gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cms-pro-hero::after { background: rgba(7,18,35,.62); }
        }
        @media (min-width: 768px) { .cms-pro-hide-desktop { display: none !important; } }
        @media (prefers-reduced-motion: reduce) { .cms-pro-slides, .cms-pro-product-carousel { scroll-behavior: auto; } }
    </style>
@endonce

<div class="cms-pro">
    @if (! empty($customCss))
        <style data-cms-pro-custom-css>{!! $customCss !!}</style>
    @endif
    @if (isset($pageTitle) && ! collect($blocks)->contains(fn ($block) => ($block['data']['heading_level'] ?? '') === 'h1'))
        <h1 class="cms-pro-sr-only">{{ $pageTitle }}</h1>
    @endif

    @foreach ($blocks as $index => $block)
        @php
            $data = $block['data'] ?? [];
            $settings = $block['settings'] ?? [];
            $container = $settings['container'] ?? 'wide';
            $classes = collect([
                'cms-pro-section',
                ! empty($settings['hide_mobile']) ? 'cms-pro-hide-mobile' : null,
                ! empty($settings['hide_desktop']) ? 'cms-pro-hide-desktop' : null,
            ])->filter()->implode(' ');
            $style = collect([
                ! empty($settings['background']) ? '--cms-bg:'.$settings['background'] : null,
                ! empty($settings['text_color']) ? '--cms-color:'.$settings['text_color'] : null,
                '--cms-pt:'.((int) ($settings['padding_top'] ?? 64)).'px',
                '--cms-pb:'.((int) ($settings['padding_bottom'] ?? 64)).'px',
                '--cms-mobile-padding:'.((int) ($settings['mobile_padding'] ?? 32)).'px',
            ])->filter()->implode(';');
            $containerClass = $container === 'full' ? '' : 'cms-pro-container cms-pro-container--'.$container;
            $heading = in_array($data['heading_level'] ?? '', ['h1', 'h2', 'h3']) ? $data['heading_level'] : 'h2';
        @endphp

        <section id="{{ $settings['anchor'] ?? '' }}" class="{{ $classes }}" style="{{ $style }}">
            @switch($block['type'])
                @case('hero')
                    @php $heroMedia = $cmsProService->findMedia((int) (($data['media_id'] ?? 0) ?: ($data['poster_id'] ?? 0))); @endphp
                    @if (($data['mode'] ?? 'image') === 'carousel' && ! empty($data['slides']))
                        <div class="cms-pro-slides" aria-label="{{ $data['title'] ?? 'Banner' }}">
                            @foreach ($data['slides'] as $slide)
                                @php $slideMedia = $cmsProService->findMedia((int) ($slide['media_id'] ?? 0)); @endphp
                                <article class="cms-pro-hero cms-pro-slide">
                                    @if ($slideMedia)<img class="cms-pro-hero__media" src="{{ $slideMedia->url }}" alt="{{ $slideMedia->alt_text ?: ($slide['title'] ?? '') }}" width="{{ $slideMedia->width }}" height="{{ $slideMedia->height }}" {{ $index ? 'loading=lazy' : 'fetchpriority=high' }}>@endif
                                    <div class="cms-pro-container cms-pro-hero__content"><h2 class="cms-pro-heading">{{ $slide['title'] ?? '' }}</h2><div class="cms-pro-copy">{!! $slide['text'] ?? '' !!}</div>@if (! empty($slide['button_label']) && ! empty($slide['button_url']))<a class="cms-pro-button" href="{{ $slide['button_url'] }}">{{ $slide['button_label'] }}</a>@endif</div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="cms-pro-hero">
                            @if (($data['mode'] ?? '') === 'video' && ! empty($data['video_url']))
                                @php $embedUrl = $cmsProService->videoEmbedUrl($data['video_url']); @endphp
                                @if ($embedUrl)
                                    <iframe class="cms-pro-hero__embed" src="{{ $embedUrl }}?autoplay=1&mute=1&controls=0&loop=1" title="{{ $data['title'] ?? 'Video' }}" allow="autoplay; encrypted-media" tabindex="-1"></iframe>
                                @else
                                    <video class="cms-pro-hero__media" autoplay muted loop playsinline @if($heroMedia) poster="{{ $heroMedia->url }}" @endif><source src="{{ $data['video_url'] }}"></video>
                                @endif
                            @elseif ($heroMedia)
                                <img class="cms-pro-hero__media" src="{{ $heroMedia->url }}" alt="{{ $heroMedia->alt_text ?: ($data['title'] ?? '') }}" width="{{ $heroMedia->width }}" height="{{ $heroMedia->height }}" fetchpriority="high">
                            @endif
                            <div class="cms-pro-container cms-pro-hero__content">
                                @if (! empty($data['eyebrow']))<p class="cms-pro-eyebrow">{{ $data['eyebrow'] }}</p>@endif
                                <{{ $heading }} class="cms-pro-heading">{{ $data['title'] ?? '' }}</{{ $heading }}>
                                <div class="cms-pro-copy">{!! $data['text'] ?? '' !!}</div>
                                @if (! empty($data['button_label']) && ! empty($data['button_url']))<a class="cms-pro-button" href="{{ $data['button_url'] }}">{{ $data['button_label'] }}</a>@endif
                            </div>
                        </div>
                    @endif
                    @break

                @case('rich_text')
                    <div class="{{ $containerClass }}" style="text-align: {{ $data['align'] ?? 'left' }}">
                        @if (($data['heading_level'] ?? 'h2') !== 'none' && ! empty($data['title']))<{{ $heading }} class="cms-pro-section-title">{{ $data['title'] }}</{{ $heading }}>@endif
                        <div class="cms-pro-copy">{!! $data['content'] ?? '' !!}</div>
                    </div>
                    @break

                @case('media_text')
                    @php $media = $cmsProService->findMedia((int) ($data['media_id'] ?? 0)); @endphp
                    <div class="{{ $containerClass }}"><div class="cms-pro-media-text {{ ($data['media_position'] ?? 'left') === 'right' ? 'cms-pro-media-text--right' : '' }}">
                        <div class="cms-pro-media-text__media">@if ($media)<img src="{{ $media->url }}" alt="{{ ($data['image_alt'] ?? '') ?: ($media->alt_text ?: ($data['title'] ?? '')) }}" width="{{ $media->width }}" height="{{ $media->height }}" loading="lazy">@endif</div>
                        <div>@if (! empty($data['eyebrow']))<p class="cms-pro-eyebrow">{{ $data['eyebrow'] }}</p>@endif<h2 class="cms-pro-section-title">{{ $data['title'] ?? '' }}</h2><div class="cms-pro-copy">{!! $data['content'] ?? '' !!}</div>@if (! empty($data['button_label']) && ! empty($data['button_url']))<a class="cms-pro-button" href="{{ $data['button_url'] }}">{{ $data['button_label'] }}</a>@endif</div>
                    </div></div>
                    @break

                @case('feature_cards')
                @case('testimonials')
                    <div class="{{ $containerClass }}">
                        @if (! empty($data['title']))<h2 class="cms-pro-section-title">{{ $data['title'] }}</h2>@endif
                        <div class="cms-pro-grid" style="--cms-columns: {{ (int) ($data['columns'] ?? 3) }}">
                            @foreach ($data['items'] ?? [] as $item)
                                @php $media = $cmsProService->findMedia((int) ($item['media_id'] ?? 0)); @endphp
                                <article class="cms-pro-card">@if ($media)<img src="{{ $media->url }}" alt="{{ $media->alt_text ?: ($item['title'] ?? $item['name'] ?? '') }}" width="{{ $media->width }}" height="{{ $media->height }}" loading="lazy">@endif<h3>{{ $item['title'] ?? $item['name'] ?? '' }}</h3><div class="cms-pro-copy">{!! $item['text'] ?? $item['quote'] ?? '' !!}</div>@if (! empty($item['role']))<p>{{ $item['role'] }}</p>@endif @if (! empty($item['url']))<a href="{{ $item['url'] }}" class="cms-pro-button">Ver más</a>@endif</article>
                            @endforeach
                        </div>
                    </div>
                    @break

                @case('product_grid')
                @case('product_carousel')
                    @php $products = $productBlocks->products($data, $cmsProChannelId ?? null); @endphp
                    @if ($products->isNotEmpty())
                        <div class="{{ $containerClass }}">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:20px">@if (! empty($data['title']))<h2 class="cms-pro-section-title">{{ $data['title'] }}</h2>@endif @if (! empty($data['button_label']) && ! empty($data['button_url']))<a href="{{ $data['button_url'] }}">{{ $data['button_label'] }}</a>@endif</div>
                            <div class="{{ $block['type'] === 'product_carousel' ? 'cms-pro-product-carousel' : 'cms-pro-product-grid' }}" style="--cms-columns: {{ (int) ($data['columns'] ?? 4) }}">
                                @foreach ($products as $product)
                                    @php $image = product_image()->getProductBaseImage($product); @endphp
                                    <a class="cms-pro-product" href="{{ route('shop.product_or_category.index', $product->url_key) }}"><img src="{{ $image['medium_image_url'] }}" alt="{{ $product->name }}" width="300" height="300" loading="lazy"><h3>{{ $product->name }}</h3></a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @break

                @case('logo_strip')
                    <div class="{{ $containerClass }}">@if (! empty($data['title']))<h2 class="cms-pro-section-title">{{ $data['title'] }}</h2>@endif<div class="cms-pro-logos">@foreach ($data['items'] ?? [] as $item) @php $media = $cmsProService->findMedia((int) ($item['media_id'] ?? 0)); @endphp @if ($media)<a href="{{ $item['url'] ?? '#' }}" aria-label="{{ $item['name'] ?? '' }}"><img src="{{ $media->url }}" alt="{{ $media->alt_text ?: ($item['name'] ?? '') }}" width="{{ $media->width }}" height="{{ $media->height }}" loading="lazy"></a>@endif @endforeach</div></div>
                    @break

                @case('gallery')
                    <div class="{{ $containerClass }}">@if (! empty($data['title']))<h2 class="cms-pro-section-title">{{ $data['title'] }}</h2>@endif<div class="cms-pro-grid cms-pro-gallery" style="--cms-columns: {{ (int) ($data['columns'] ?? 3) }}">@foreach ($data['items'] ?? [] as $item) @php $media = $cmsProService->findMedia((int) ($item['media_id'] ?? 0)); @endphp @if ($media)<figure><img src="{{ $media->url }}" alt="{{ ($item['alt'] ?? '') ?: ($media->alt_text ?: '') }}" width="{{ $media->width }}" height="{{ $media->height }}" loading="lazy">@if (! empty($item['caption']))<figcaption>{{ $item['caption'] }}</figcaption>@endif</figure>@endif @endforeach</div></div>
                    @break

                @case('accordion')
                    <div class="{{ $containerClass }} cms-pro-accordion">@if (! empty($data['title']))<h2 class="cms-pro-section-title">{{ $data['title'] }}</h2>@endif @foreach ($data['items'] ?? [] as $item)<details><summary>{{ $item['title'] ?? '' }}</summary><div class="cms-pro-copy">{!! $item['content'] ?? '' !!}</div></details>@endforeach</div>
                    @if (! empty($data['faq_schema']) && ! empty($data['items']))
                        <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => collect($data['items'])->map(fn ($item) => ['@type' => 'Question', 'name' => strip_tags($item['title'] ?? ''), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($item['content'] ?? '')]])->values()], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
                    @endif
                    @break

                @case('stats')
                    <div class="{{ $containerClass }} cms-pro-stats">@if (! empty($data['title']))<h2 class="cms-pro-section-title">{{ $data['title'] }}</h2>@endif<div class="cms-pro-grid" style="--cms-columns: {{ min(4, max(1, count($data['items'] ?? []))) }}">@foreach ($data['items'] ?? [] as $item)<div class="cms-pro-stat"><strong>{{ $item['value'] ?? '' }}</strong><span>{{ $item['label'] ?? '' }}</span></div>@endforeach</div></div>
                    @break

                @case('cta')
                    @php $background = $cmsProService->findMedia((int) ($data['background_id'] ?? 0)); @endphp
                    <div class="{{ $containerClass }}"><div class="cms-pro-cta" @if($background) style="background-image:url('{{ $background->url }}')" @endif><div class="cms-pro-cta__content"><h2 class="cms-pro-section-title">{{ $data['title'] ?? '' }}</h2><div class="cms-pro-copy">{!! $data['text'] ?? '' !!}</div>@if (! empty($data['button_label']) && ! empty($data['button_url']))<a class="cms-pro-button" href="{{ $data['button_url'] }}">{{ $data['button_label'] }}</a>@endif</div></div></div>
                    @break

                @case('video')
                    @php $poster = $cmsProService->findMedia((int) ($data['poster_id'] ?? 0)); @endphp
                    @php $embedUrl = $cmsProService->videoEmbedUrl($data['video_url'] ?? null); @endphp
                    <div class="{{ $containerClass }}">@if (! empty($data['title']))<h2 class="cms-pro-section-title">{{ $data['title'] }}</h2>@endif @if ($embedUrl)<iframe class="cms-pro-video" src="{{ $embedUrl }}" title="{{ $data['title'] ?? 'Video' }}" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>@else<video class="cms-pro-video" controls playsinline @if($poster) poster="{{ $poster->url }}" @endif><source src="{{ $data['video_url'] ?? '' }}"></video>@endif @if (! empty($data['caption']))<div class="cms-pro-copy">{!! $data['caption'] !!}</div>@endif</div>
                    @break

                @case('comparison')
                    <div class="{{ $containerClass }}">@if (! empty($data['title']))<h2 class="cms-pro-section-title">{{ $data['title'] }}</h2>@endif<table class="cms-pro-comparison"><tbody>@foreach ($data['items'] ?? [] as $item)<tr><th scope="row">{{ $item['label'] ?? '' }}</th><td>{{ $item['value'] ?? '' }}</td></tr>@endforeach</tbody></table></div>
                    @break

                @case('custom_html')
                    <div class="{{ $containerClass }}">{!! $data['html'] ?? '' !!}</div>
                    @break

            @endswitch
        </section>
    @endforeach
</div>
@if (! empty($customJs))
    <script data-cms-pro-custom-js>
        {!! $customJs !!}
    </script>
@endif
