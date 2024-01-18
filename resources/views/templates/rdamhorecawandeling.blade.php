<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-K9DR9D9');</script>
    <!-- End Google Tag Manager -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $data['website_options']['wt_website_meta_title'] }}</title>
    <meta name="description" content="{{ $data['website_options']['wt_website_meta_description'] }}">
    {{-- <script src="https://kit.fontawesome.com/d7d4003c56.js" crossorigin="anonymous"></script> --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ secure_asset('icons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ secure_asset('icons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ secure_asset('icons/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ secure_asset('icons/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ secure_asset('icons/safari-pinned-tab.svg') }}" color="#008100">
    <link rel="shortcut icon" href="{{ secure_asset('icons/favicon.ico') }}">
    <meta name="msapplication-TileColor" content="#008100">
    <meta name="msapplication-config" content="{{ secure_asset('icons/browserconfig.xml') }}">
    <meta name="theme-color" content="#008100">
    <link rel="stylesheet" href="{{ secure_asset('css/styles.css') }}">
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K9DR9D9"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <header id="header">
        <div id="logoHolder"><img src="{{ str_replace('http://', 'https://', secure_asset($data['website_options']['wt_website_logo'])) }}" alt=""></div>
        <nav id="mNav">
            {!! $data['html_menu'] !!}
        </nav>
    </header>
    {{-- <div id="contentWrapper"> --}}
        @yield('content')
    {{-- </div> --}}
    <footer id="footer">
        <div>{!! $data['website_options']['wt_website_footer'] !!}</div>
    </footer>
    @foreach ($data['website_options']['wt_website_background_images'] as $backImage)
        <img src="{{ str_replace('http://', 'https://', secure_asset($backImage)) }}" alt="" class="backgroundImage">
    @endforeach
    {{-- <script src="{{ secure_asset('js/script.js') }}"></script> --}}
    @yield('before_closing_body_tag')
</body>
</html>