<?php

use Themosis\Core\Application;

use Themosis\Support\Facades\Page;
use Themosis\Support\Section;

/*
|--------------------------------------------------------------------------
| Bootstrap Theme
|--------------------------------------------------------------------------
|
| We bootstrap the theme. The following code is loading your theme
| configuration files and register theme images sizes, menus, sidebars,
| theme support features and templates.
|
*/
$theme = (Application::getInstance())->loadTheme(__DIR__, 'config');

/*
|--------------------------------------------------------------------------
| Theme i18n | l10n
|--------------------------------------------------------------------------
|
| Registers the "languages" directory for storing the theme translations.
|
| The "THEME_TD" constant is defined during bootstrap and its value is
| set based on the "style.css" [Text Domain] property located into
| the file header.
|
*/
load_theme_textdomain(
    THEME_TD,
    $theme->getPath($theme->getHeader('domain_path'))
);

/*
|--------------------------------------------------------------------------
| Theme assets locations
|--------------------------------------------------------------------------
|
| You can define your theme assets paths and URLs. You can add as many
| locations as you want. The key is your asset directory path and
| the value is its public URL.
|
*/
$theme->assets([
    $theme->getPath('dist') => $theme->getUrl('dist')
]);

/*
|--------------------------------------------------------------------------
| Theme Views
|--------------------------------------------------------------------------
|
| Register theme view paths. By default, the theme is registering
| the "views" directory but you can add as many directories as you want
| from the theme.php configuration file.
|
*/
$theme->views($theme->config('theme.views', []));

/*
|--------------------------------------------------------------------------
| Theme Service Providers
|--------------------------------------------------------------------------
|
| Register theme service providers. You can manage the list of
| services providers through the theme.php configuration file.
|
*/
$theme->providers($theme->config('theme.providers', []));

/*
|--------------------------------------------------------------------------
| Theme includes
|--------------------------------------------------------------------------
|
| Auto includes files by providing one or more paths. By default, we setup
| an "inc" directory within the theme. Use that "inc" directory to extend
| your theme features. Nested files are also included.
|
*/
$theme->includes([
    $theme->getPath('inc')
]);

/*
|--------------------------------------------------------------------------
| Theme Image Sizes
|--------------------------------------------------------------------------
|
| Register theme image sizes. Image sizes are configured in your theme
| images.php configuration file.
|
*/
$theme->images($theme->config('images'));

/*
|--------------------------------------------------------------------------
| Theme Menu Locations
|--------------------------------------------------------------------------
|
| Register theme menu locations. Menu locations are configured in your theme
| menus.php configuration file.
|
*/
$theme->menus($theme->config('menus'));

/*
|--------------------------------------------------------------------------
| Theme Sidebars
|--------------------------------------------------------------------------
|
| Register theme sidebars. Sidebars are configured in your theme
| sidebars.php configuration file.
|
*/
$theme->sidebars($theme->config('sidebars'));

/*
|--------------------------------------------------------------------------
| Theme Support
|--------------------------------------------------------------------------
|
| Register theme support. Support features are configured in your theme
| support.php configuration file.
|
*/
$theme->support($theme->config('support', []));

/*
|--------------------------------------------------------------------------
| Theme Templates
|--------------------------------------------------------------------------
|
| Register theme templates. Templates are configured in your theme
| templates.php configuration file.
|
*/
$theme->templates($theme->config('templates', []));


/* CUSTOM Themosis W.T. by Leon Kuijf */

$page = Page::make('website-options', __( 'Set your website options' ))
    ->setMenu('Website options')
    ->set();
// $page->route('/', function () {
//     return view('admin.home');
// });
$page->addSections([
    new Section('general', 'General'),
    new Section('social', 'Social'),
    new Section('footer', 'Footer')
]);
$page->addSettings([
    'general' => [
        Field::text('title', [
            // 'rules' => 'required|min:6'
        ]),
        Field::textarea('comment')
    ],
    'social' => [
        Field::text('twitter', [
            // 'rules' => 'required|url'
        ])
    ],
    'footer' => [
        Field::editor('footer_text', [
            'settings' => [
                'wpautop' => false
            ]
        ])
    ]
]);


/* Carbon Fields W.T. by Leon Kuijf */
use Carbon_Fields\Container;
use Carbon_Fields\Block;
use Carbon_Fields\Field;

add_action( 'after_setup_theme', 'crb_load' );
add_action( 'carbon_fields_register_fields', 'myNewBlock'  );

function myNewBlock(){
    Block::make( __( 'My Shiny Gutenberg Block' ) ) // cannot be changed afterwards
	->add_fields( array(
        Field::make( 'text', 'anchor', __( 'Anchor (Link menu items to this block with: #[Anchor])' ) ),
		Field::make( 'text', 'heading', __( 'Block Heading' ) ),
		Field::make( 'image', 'image', __( 'Block Image' ) ),
		Field::make( 'rich_text', 'content', __( 'Block Content' ) ),

        // Field::text('te1'),
        // Field::text('te2')
	) )
    ->set_description( __( 'A simple block with some sheittt2' ) )
    // ->set_category( 'layout' )
    ->set_category( 'custom-wt-category', __( 'WT blocks' ), 'smiley' )
    ->set_icon( 'heart' )
    ->set_keywords( [ __( 'wt' ), __( 'rotterdamse' ), __( 'custom' ), __( 'extra' ) ] )
    // ->set_mode( 'both' )
    // ->set_editor_style( 'crb-my-shiny-gutenberg-block-stylesheet-BACKEND' )
    // ->set_style( 'crb-my-shiny-gutenberg-block-stylesheet-FRONTEND' )

    /*
    ->set_inner_blocks( true )
    ->set_inner_blocks_position( 'below' )
    ->set_inner_blocks_template( array(
		array( 'core/heading' ),
		array( 'core/paragraph' )
	) )
    ->set_inner_blocks_template_lock( 'insert' )
    ->set_parent( 'carbon-fields/product' )
    ->set_allowed_inner_blocks( array(
		'core/paragraph',
		'core/list'
	) )
	->set_render_callback( function () {
	} )
    */

	->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
        /**** 10-1-2024 Leon Kuijf. Set some initial values. Adding fields AFTER block has already been created ends up in an undefined array key ****/
        if(!isset($fields['anchor'])) $fields['anchor'] = '';
		?>
        <?php
            //echo wp_get_attachment_image( $fields['image'], 'full' );
            list($url, $width, $height) = wp_get_attachment_image_src($fields['image'], 'full');
        ?>
        <div class="wtBlock">
            <div class="wtbImage" style="background-image:url('<?php echo parse_url($url, PHP_URL_PATH) ?>')">&nbsp;</div><!-- /.wtbImage -->
            <div class="wtbContent">
                <div class="wtbText">
                    <div class="wtbInnerText">
                        <div class="wtb_heading">
                            <a class="wtanchor" id="<?php echo esc_html( $fields['anchor'] ); ?>"></a>
                            <h1><?php echo esc_html( $fields['heading'] ); ?></h1>
                        </div><!-- /.wtb_heading -->
                        <div class="wtb_content">
                            <?php echo apply_filters( 'the_content', $fields['content'] ); ?>
                        </div><!-- /.wtb_content -->
                    </div><!-- /.wtbInnerText -->
                </div><!-- /.wtbText -->
            </div><!-- /.wtbContent -->
        </div><!-- /.wtBlock -->
		<?php
	} );
}


function crb_load() {
    require_once( 'vendor/autoload.php' );
    \Carbon_Fields\Carbon_Fields::boot();
}
