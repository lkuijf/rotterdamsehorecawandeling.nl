<!doctype html>
<html {!! get_language_attributes() !!}>
<head>
    <meta charset="{{ get_bloginfo('charset') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    @head
</head>
<body @php(body_class())>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content">{{ esc_html__('Skip to content', THEME_TD) }}</a>

    <header id="masthead" class="site-header">
        <div class="site-branding">
            {!! get_custom_logo() !!}
            @if(is_front_page() && is_home())
                <h1 class="site-title"><a href="{{ esc_url(home_url('/')) }}" rel="home">{{ get_bloginfo('name') }}</a></h1>
            @else
                <p class="site-title"><a href="{{ esc_url( home_url('/')) }}" rel="home">{{ get_bloginfo('name') }}</a></p>
            @endif

            @if(($description = get_bloginfo('description', 'display')) || is_customize_preview())
                <p class="site-description">{{ $description }}</p>
            @endif
        </div><!-- .site-branding -->


        {{-- <nav class="mainNav">
            <input type="checkbox" id="burger-check">
            <label for="burger-check" class="burger-label">
                <span></span>
                <span></span>
                <span></span>
            </label>
            {!! $data['html_menu'] !!}
        </nav> --}}


        <nav id="site-navigation" class="main-navigation">


            {{-- <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">{{ esc_html__('Primary Menu', THEME_TD) }}</button> --}}
            <input type="checkbox" id="burger-check">
            <label for="burger-check" class="burger-label">
                <span></span>
                <span></span>
                <span></span>
            </label>


            {!! wp_nav_menu([
                'theme_location' => 'menu-1',
                'menu_id' => 'primary-menu',
                'echo' => false
            ]) !!}
        </nav><!-- #site-navigation -->
    </header><!-- #masthead -->

    <div id="content" class="site-content">
        <div id="primary" class="content-area">
            <main id="main" class="site-main">
                @yield('content')
            </main>
        </div>
        {{-- <!-- Sidebar -->
        @if(is_active_sidebar('sidebar-1'))
            <aside id="secondary" class="widget-area">
                @php(dynamic_sidebar('sidebar-1'))
            </aside>
        @endif
        <!-- End sidebar --> --}}
    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="site-info">
            {!! get_option('th_footer_text') !!}
            {{-- <a href="{{ esc_url(__('https://wordpress.org/', THEME_TD)) }}">
                {{ sprintf(esc_html__('Proudly powered by %s', THEME_TD), 'WordPress') }}
            </a>
            <span class="sep"> | </span>
            {!! sprintf(esc_html__('Theme: %1$s by %2$s.', THEME_TD), 'Themosis', '<a href="https://framework.themosis.com">Themosis Framework</a>') !!} --}}
        </div><!-- .site-info -->
    </footer><!-- #colophon -->
</div><!-- #page -->

<a href="" id="toTop"><span>scroll to top</span></a>

@footer

@env('local')
    <script src="http://localhost:35729/livereload.js"></script>
@endenv

</body>
</html>
