@php
    $pageTitle = 'Monturas y Gafas de Sol | LAG Medical';
    $pageDescription = 'Descubre monturas y gafas de sol, catálogos de marcas y oportunidades comerciales para tu óptica o distribución.';
    $canonicalUrl = route('campaign.frames.index');
    $socialImage = asset('assets/campaign/frames/assets/editorial.webp');
    $channel = core()->getCurrentChannel();
    $channelLogo = $channel->logo_url ?: 'images/logo.svg';
    $channelFavicon = $channel->favicon_url ?: bagisto_asset('images/favicon.ico');
    $channelUrl = url('/');

    if (app()->environment('production') && $channel->hostname) {
        $channelUrl = preg_match('/^https?:\/\//i', $channel->hostname)
            ? $channel->hostname
            : 'https://'.$channel->hostname;

        $channelUrl = rtrim($channelUrl, '/');
    }
    $logoUrl = filter_var($channelLogo, FILTER_VALIDATE_URL)
        ? $channelLogo
        : asset(ltrim($channelLogo, '/'));
    $siteUrl = url('/');
    $marchonBrands = [
        'Airlock', 'Calvin Klein Jeans', 'Calvin Klein', 'Canada Goose',
        'Columbia', 'Converse', 'DKNY', 'Donna Karan', 'Dragon', 'Ferragamo',
        'Flexon', 'Karl Lagerfeld', 'Kendra Scott', 'Lacoste', 'Liu Jo',
        'Longchamp', 'Marchon NYC', 'Nautica', 'Nike', 'Nine West', 'Pure', 'Skaga',
    ];
    $structuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl.'#webpage',
                'url' => $canonicalUrl,
                'name' => $pageTitle,
                'description' => $pageDescription,
                'inLanguage' => 'es',
                'isPartOf' => ['@id' => $siteUrl.'#website'],
                'about' => ['@id' => $siteUrl.'#organization'],
                'primaryImageOfPage' => ['@id' => $socialImage.'#image'],
            ],
            [
                '@type' => 'Organization',
                '@id' => $siteUrl.'#organization',
                'name' => 'LAG Medical Inc.',
                'url' => $siteUrl,
                'logo' => ['@type' => 'ImageObject', 'url' => $logoUrl],
            ],
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl.'#website',
                'url' => $siteUrl,
                'name' => 'LAG Medical',
                'publisher' => ['@id' => $siteUrl.'#organization'],
                'inLanguage' => 'es',
            ],
            [
                '@type' => 'ImageObject',
                '@id' => $socialImage.'#image',
                'url' => $socialImage,
                'contentUrl' => $socialImage,
                'caption' => 'Selección editorial de monturas LAG Medical',
            ],
        ],
    ];
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="keywords" content="monturas, gafas de sol, catálogos de monturas, distribución mayorista, ópticas, Komiko Eyewear, Marchon, LAG Medical">
    <meta name="author" content="LAG Medical Inc.">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="theme-color" content="#f6f7f9">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ $channelFavicon }}">
    <link rel="shortcut icon" href="{{ $channelFavicon }}">
    <link rel="apple-touch-icon" href="{{ $channelFavicon }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="LAG Medical">
    <meta property="og:locale" content="es_ES">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $socialImage }}">
    <meta property="og:image:alt" content="Selección editorial de monturas para ópticas">
    <meta property="og:image:type" content="image/webp">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $socialImage }}">
    <meta name="twitter:image:alt" content="Selección editorial de monturas para ópticas">
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <link rel="stylesheet" href="{{ asset('assets/campaign/frames/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/campaign/frames/enhancements.css') }}">
</head>
<body>
<div class="lf-page">
    <header class="lf-header">
        <a class="lf-logo" href="{{ $channelUrl }}" aria-label="LAG Medical inicio"><img class="lf-logo-image" src="{{ $logoUrl }}" alt="LAG Medical" width="180" height="48"></a>
        <span class="lf-header-note">EYEWEAR · PARA PROFESIONALES</span>
        <button class="lf-small-cta" data-signup>Acceso mayorista <span>↗</span></button>
    </header>
    <main>
        <section class="lf-intro">
            <div class="lf-eyebrow">CATÁLOGOS DE EYEWEAR / DISTRIBUCIÓN MAYORISTA</div>
            <h1>Monturas y<br><em>Gafas de Sol.</em></h1>
            <div class="lf-intro-bottom"><button type="button" class="lf-button lf-dark" data-signup>Suscríbete a la lista de difusión <span>↗</span></button><p>Recibe catálogos, novedades y condiciones comerciales<br>directamente en tu inbox.</p></div>
        </section>
        <section class="lf-hero" aria-label="Selección editorial de monturas">
            <video autoplay muted loop playsinline preload="metadata" poster="{{ asset('assets/campaign/frames/assets/editorial.webp') }}" aria-label="Video editorial de monturas"><source src="{{ asset('assets/campaign/frames/assets/banner.mp4') }}" type="video/mp4"></video>
            <div class="lf-hero-content"><span class="lf-eyebrow">EYEWEAR PARA ÓPTICAS Y DISTRIBUIDORES</span><h2>Encuentra las colecciones<br>que tu negocio necesita.</h2><button class="lf-button lf-light" data-signup>Solicitar catálogos y novedades <span>↗</span></button><p>Para ópticas y distribuidores.</p></div>
        </section>
         <section class="lf-brands lf-brands--komiko" id="komiko"><div class="lf-brand-section-grid"><article class="lf-brand-panel lf-brand-panel--komiko"><span class="lf-eyebrow">KOMIKO / IDENTIDAD LAG MEDICAL</span><div class="lf-komiko"><img class="lf-komiko-logo" src="{{ asset('assets/campaign/frames/assets/Komiko-brand-logo.png') }}" alt="Komiko Eyewear" width="754" height="144"><span>EYEWEAR</span></div><h2>Diseñada para<br><em>tu selección.</em></h2><p>Komiko es nuestra marca propia: una propuesta con identidad, criterio y espacio para crecer dentro del portafolio de tu óptica.</p></article><div class="lf-brand-image lf-brand-image--komiko"><img src="{{ asset('assets/campaign/frames/assets/komiko-eyewear.png') }}" alt="Gafas Komiko Eyewear" loading="lazy" width="1122" height="1370"></div></div></section>
        <section class="lf-brands lf-brands--marchon" id="marchon"><article class="lf-brand-panel lf-brand-panel--marchon"><span class="lf-eyebrow">MARCHON / BRAND PORTFOLIO</span><div class="lf-marchon">MARCHON<span>BRAND PORTFOLIO</span></div><h2>Marcas que<br><em>abren posibilidades.</em></h2><p>Explora marcas reconocidas y colecciones de oportunidad para ampliar tu propuesta comercial.</p><div class="lf-brand-carousel" aria-label="Marcas del portafolio Marchon"><div class="lf-brand-carousel__track">@foreach ($marchonBrands as $brand)<span>{{ $brand }}</span>@endforeach @foreach ($marchonBrands as $brand)<span aria-hidden="true">{{ $brand }}</span>@endforeach</div></div><details class="lf-all-brands" open><summary>Explorar todas las marcas <span>+</span></summary><div class="lf-brand-grid">@foreach ($marchonBrands as $brand)<span>{{ $brand }}</span>@endforeach</div></details><p class="lf-note">Consulta disponibilidad de marcas y colecciones para tu mercado.</p></article></section>
        <section class="lf-edit"><div class="lf-product-image"><img src="{{ asset('assets/campaign/frames/assets/frames.webp') }}" alt="Composición conceptual de una montura de acetato carey" loading="lazy" width="1536" height="1024"><span>FORM / TEXTURE / DETAIL</span></div><div class="lf-edit-copy"><h2>El detalle<br>hace la<br><em>diferencia.</em></h2><p>Explora nuevas posibilidades para el portafolio de tu óptica. Conoce las colecciones y consulta las opciones comerciales con LAG Medical.</p><button class="lf-text-button" data-signup>Quiero conocer las colecciones <span>↗</span></button></div></section>
        <section class="lf-benefits"><div class="lf-section-top"><span>Tu próxima selección empieza aquí.</span></div><h2>Menos búsqueda.<br><em>Más perspectiva.</em></h2><div class="lf-benefit-grid"><article><h3>Catálogos premium.</h3><p>Descubre monturas y colecciones para ampliar las opciones de tu negocio.</p></article><article><h3>Información mayorista.</h3><p>Recibe información sobre precios y condiciones comerciales para compradores profesionales.</p></article><article><h3>Lo nuevo, en tu inbox.</h3><p>Mantente al día con actualizaciones de catálogo y novedades de las marcas.</p></article></div></section>
        <section class="lf-final"><span class="lf-eyebrow">CATÁLOGOS Y OPORTUNIDADES COMERCIALES</span><h2>Recibe catálogos<br>para tu <em>próxima colección.</em></h2><button class="lf-button lf-dark" data-signup>Suscribirme a la lista de difusión <span>↗</span></button><p>Solo tu email. Cancela tu suscripción cuando quieras.</p></section>
        <section class="lf-faq"><h2>Preguntas frecuentes.</h2><div><details><summary>¿Para quién es esta lista?<span>+</span></summary><p>Para ópticas, distribuidores y profesionales que buscan información comercial de monturas para su negocio.</p></details><details><summary>¿Qué recibiré por email?<span>+</span></summary><p>Actualizaciones de catálogos, novedades de colecciones e información mayorista de LAG Medical.</p></details><details><summary>¿Todas las marcas están disponibles en mi país?<span>+</span></summary><p>La disponibilidad de marcas, modelos y condiciones comerciales puede variar según el mercado. El equipo de LAG Medical puede orientarte para tu negocio.</p></details></div></section>
    </main>
    <footer class="lf-footer"><a class="lf-logo" href="{{ $channelUrl }}"><img class="lf-logo-image" src="{{ $logoUrl }}" alt="LAG Medical" width="180" height="48"></a><p>Eyewear. A business perspective.</p><button class="lf-privacy-open">Privacidad</button><small>© 2026 LAG Medical Inc.</small><p class="lf-disclaimer">Fotografías editoriales conceptuales; no representan modelos específicos del catálogo. Los nombres de marcas pertenecen a sus respectivos titulares.</p></footer>
    <dialog class="lf-modal" id="lf-signup" aria-labelledby="lf-modal-title"><button class="lf-close" aria-label="Cerrar ventana">×</button><div class="lf-modal-inner">@if (session('success'))<span class="lf-eyebrow">LAG MEDICAL / MONTURAS Y GAFAS DE SOL</span><h2 id="lf-modal-title">Registro enviado<br><em>con éxito.</em></h2><p class="lf-form-message" data-state="success">{{ session('success') }}</p>@else<span class="lf-eyebrow">LAG MEDICAL / MONTURAS Y GAFAS DE SOL</span><h2 id="lf-modal-title">Tu próxima<br><em>perspectiva.</em></h2><p>Recibe catálogos, información mayorista y novedades de monturas para tu negocio.</p><form id="lf-form" method="POST" action="{{ route('campaign.frames.subscribe') }}">@csrf<label for="lf-email">Tu email</label><input id="lf-email" type="email" name="email" autocomplete="email" placeholder="tu@email.com" required maxlength="254"><div class="lf-honeypot" aria-hidden="true"><input type="text" name="email_address_check" tabindex="-1" autocomplete="off"></div><button type="submit" class="lf-button lf-dark" data-submit-button><span data-submit-text>Suscribirme a la lista de difusión</span><span aria-hidden="true">↗</span></button><p class="lf-consent"><label><input type="checkbox" name="consent" value="1" checked required> Acepto recibir comunicaciones comerciales de LAG Medical por email. Puedo retirar mi consentimiento en cualquier momento.</label> <button type="button" class="lf-privacy-open">Ver privacidad</button>.</p></form>@if ($errors->any())<p class="lf-form-message" data-state="error">{{ $errors->first() }}</p>@endif @endif</div></dialog>
    <dialog class="lf-modal lf-privacy" id="lf-privacy" aria-labelledby="lf-privacy-title"><button class="lf-close" aria-label="Cerrar privacidad">×</button><div class="lf-modal-inner"><span class="lf-eyebrow">LAG MEDICAL</span><h2 id="lf-privacy-title">Tu email.<br>Tu decisión.</h2><p>Utilizaremos tu email para enviarte los catálogos, novedades e información comercial que solicitas al suscribirte.</p><p>Las comunicaciones se gestionan a través de Brevo. Puedes retirar tu suscripción mediante el enlace de baja incluido en cada correo.</p><p>Para consultas sobre el uso de tus datos, contacta a LAG Medical a través de <a href="https://lagmedicalinc.com" target="_blank" rel="noopener">lagmedicalinc.com</a>.</p></div></dialog>
</div>
<script src="{{ asset('assets/campaign/frames/app.js') }}" defer></script>
<script type="text/javascript">
    {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
</script>
</body>
</html>
