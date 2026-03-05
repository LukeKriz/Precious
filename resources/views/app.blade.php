<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @php
        $site = \App\Models\SiteSetting::first();

        $s = fn($v) => is_string($v) ? trim($v) : '';

        // základ
        $siteName = $s(data_get($site, 'site_name')) ?: config('app.name', 'Laravel');
        $baseUrl  = $s(data_get($site, 'base_url')) ?: request()->getSchemeAndHttpHost();

    
        // OG
        $ogTitle = $s(data_get($site, 'og_title')) ?: $defaultTitle;
        $ogDescription = $s(data_get($site, 'og_description')) ?: $defaultDescription;
        $ogImage = $s(data_get($site, 'og_image'));

        // ikony
        $faviconIco = $s(data_get($site, 'favicon_ico'));
        $appleIcon  = $s(data_get($site, 'apple_touch_icon'));
        $pwaIcon    = $s(data_get($site, 'pwa_icon_512'));

        // robots
        $siteNoindex = (bool) data_get($site, 'site_noindex', false);

        // custom head
        $customHead = data_get($site, 'meta_head_custom');

        // canonical URL
        $canonical = rtrim($baseUrl, '/') . '/' . ltrim(request()->path(), '/');
        if (request()->path() === '/') $canonical = rtrim($baseUrl, '/').'/';
    @endphp

    {{-- TITLE: Inertia to přepíše, pokud stránka použije <Head title="..."> --}}
    <title inertia></title>


    <link rel="canonical" href="{{ $canonical }}">

    @if($siteNoindex)
        <meta name="robots" content="noindex, nofollow">
    @endif

    {{-- OpenGraph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:url" content="{{ $baseUrl }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    @if($ogDescription)
        <meta property="og:description" content="{{ $ogDescription }}">
    @endif
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    @if($ogDescription)
        <meta name="twitter:description" content="{{ $ogDescription }}">
    @endif
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    {{-- Icons --}}
    @if($faviconIco)
        <link rel="icon" href="{{ $faviconIco }}">
    @endif

    @if($appleIcon)
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $appleIcon }}">
    @endif

    @if($pwaIcon)
        <link rel="icon" type="image/png" sizes="512x512" href="{{ $pwaIcon }}">
    @endif

    {{-- Theme CSS --}}
    <link
        id="theme-css"
        rel="stylesheet"
        href="{{ route('theme.css', ['v' => optional(\App\Models\MainDesign::first())->updated_at?->timestamp ?? time()]) }}"
    />

    {{-- Custom <head> --}}
    @if($customHead)
        {!! $customHead !!}
    @endif

    {{-- Inertia --}}
    @routes
    @vite([
        'resources/js/app.js',
        "resources/js/Pages/{$page['component']}.vue"
    ])
    @inertiaHead
</head>

<body class="antialiased overflow-x-hidden">
    @inertia
</body>
</html>
