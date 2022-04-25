<?php
use Carbon_Fields\Field;
use Carbon_Fields\Container;

$carbonFieldsArgs = array();

$websiteOptions = array();
// $websiteOptions[] = array('text', 'wt_website_text1', 'Website header e-mail adres');
// $websiteOptions[] = array('text', 'wt_website_text2', 'Website header telefoonnummer');
// $websiteOptions[] = array('text', 'wt_website_text3', 'Website text 3');
// $websiteOptions[] = array('text', 'wt_website_text4', 'Website text 4');
// $websiteOptions[] = array('text', 'wt_website_text5', 'Website text 5');
// $websiteOptions[] = array('textarea', 'wt_website_textarea1', 'Website textarea 1');
// $websiteOptions[] = array('textarea', 'wt_website_textarea2', 'Website textarea 2');
// $websiteOptions[] = array('textarea', 'wt_website_textarea3', 'Website textarea 3');
// $websiteOptions[] = array('rich_text', 'wt_website_footer1', 'Website footer blok 1');
// $websiteOptions[] = array('rich_text', 'wt_website_footer2', 'Website footer blok 2');
// $websiteOptions[] = array('rich_text', 'wt_website_footer3', 'Website footer blok 3');
$websiteOptions[] = array('image', 'wt_website_logo', 'Website Logo');
$websiteOptions[] = array('media_gallery', 'wt_website_background_images', 'Website achtergrond afbeeldingen');
$websiteOptions[] = array('text', 'wt_website_meta_title', 'Website (meta) Title');
$websiteOptions[] = array('text', 'wt_website_meta_description', 'Website (meta) Description');
$websiteOptions[] = array('rich_text', 'wt_website_footer', 'Website footer');
$websiteOptions[] = array('text', 'wt_ticket_price', 'Ticket price (use dot for decimals, like: 19.95)');
$websiteOptions[] = array('rich_text', 'wt_checkout_ok', 'Aanmelding succesvol');
$websiteOptions[] = array('textarea', 'wt_client_email', 'E-mail bericht na ticket aankoop');
// $websiteOptions[] = array('file', 'wt_algemene_voorwaarden', 'Algemene voorwaarden');

$carbonFieldsArgs['websiteOptions'] = $websiteOptions;



if (!current_user_can('administrator')) {
    add_filter('bulk_actions-edit-page', 'remove_from_bulk_actions');
    add_filter('page_row_actions', 'remove_page_row_actions', 10, 2);
    add_action('admin_head', 'customBackendStyles');
    add_action('admin_footer', 'customBackendScriptsEditorRol');
    add_filter('carbon_fields_theme_options_container_admin_only_access', '__return_false');
    add_filter('wp_rest_cache/settings_capability', 'wprc_change_settings_capability', 10, 1);
}
add_action('admin_footer', 'customBackendScripts');

add_action('add_meta_boxes', 'set_default_page_template', 1);
add_action('init', 'remove_editor_init');
add_action('carbon_fields_register_fields', function() use ( $carbonFieldsArgs ) { crbRegisterFields( $carbonFieldsArgs ); });
add_action('carbon_fields_theme_options_container_saved', 'deleteWebsiteOptionsRestCache');
// add_action('admin_head', 'loadAxios');
// function loadAxios() {
//     echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.24.0/axios.min.js" integrity="sha512-u9akINsQsAkG9xjc1cnGF4zw5TFDwkxuc9vUp5dltDWYCSmyd0meygbvgXrlc/z7/o4a19Fb5V0OUE58J7dcyw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
// }
function crbRegisterFields($args) {
    Container::make( 'post_meta', __( 'Section Options' ) )
        ->where( 'post_type', '=', 'page' )
        ->where( 'post_template', '=', 'template-section-based.php' )
        ->add_fields( array(
            Field::make( 'complex', 'crb_sections', 'Sections' )->set_visible_in_rest_api($visible = true)
                ->add_fields( 'text', 'Tekst', array(
                    // Field::make( 'select', 'color', __( 'Choose background color' ) )
                    // ->set_options( array(
                    //     '' => __( 'White' ),
                    //     'blue' => __( 'Blue' ),
                    //     'gold' => __( 'Gold' ),
                    // ) ),
                    Field::make( 'rich_text', 'text', 'Text' ),
                    // Field::make( 'media_gallery', 'crb_media_gallery', __( 'Images' ) . ' (' . __( 'optional' ) . ')' )
                    //     ->set_type( array( 'image', ) ),
                        // ->set_value_type( 'url' ),
                ) )
                ->add_fields( 'order_form', 'Aanmeld formulier', array(
                    Field::make( 'checkbox', 'crb_show_order_form', 'Aanmeld formulier met betalingsopties weergeven' ),
                ) )
                // ->add_fields( 'text_green', 'Tekst (Groene achtergrond)', array(
                //     Field::make( 'rich_text', 'text', 'Text' ),
                // ) )
                // ->add_fields( 'text_gold', 'Tekst (Gouden achtergrond)', array(
                //     Field::make( 'rich_text', 'text', 'Text' ),
                // ) )
                // ->add_fields( 'images', 'Afbeeldingen', array(
                //     Field::make( 'image', 'image1', 'Afbeelding 1' )->set_value_type( 'url' ),
                //     Field::make( 'image', 'image2', 'Afbeelding 2' )->set_value_type( 'url' ),
                //     Field::make( 'image', 'image3', 'Afbeelding 3' )->set_value_type( 'url' ),
                // ) )
                // ->add_fields( 'hero', 'Hero', array(
                //     Field::make( 'image', 'image', 'Afbeelding' )->set_value_type( 'url' ),
                //     // Field::make( 'image', 'image', 'Afbeelding' ),
                //     // Field::make( 'rich_text', 'text', 'Tekst' ),
                // ) )

                // ->add_fields( 'iconBoxes', 'Icoon boxen', array(
                //     // Field::make( 'image', 'image', 'Afbeelding' )->set_value_type( 'url' ),
                //     // Field::make( 'image', 'image', 'Afbeelding' ),
                //     Field::make( 'rich_text', 'text', 'Tekst' ),
                // ) )
                // ->add_fields( 'solutions', __( 'Solutions' ) . ' (full-width blue background, icon + text)', array(
                //     Field::make( 'complex', 'icon_boxes', 'Text and an icon from fontawesome.com (use the icon \'name\')' )
                //         ->add_fields( array(
                //             Field::make( 'text', 'icon', __( 'Icon' ) ),
                //             Field::make( 'rich_text', 'text' , __( 'Text' )),
                //         )),
                // ))
                // ->add_fields( 'activities', __( 'Activities' ) . ' (blue text fields)', array(
                //     Field::make( 'complex', 'activity_fields', 'Activity Text' )
                //         ->add_fields( array(
                //             Field::make( 'rich_text', 'text' , __( 'Text' )),
                //         )),
                // ))
                // ->add_fields( 'services', __( 'Services' ) . ' (full-width gold background, icon + text)', array(
                //     Field::make( 'complex', 'icon_boxes', 'Text and an icon from fontawesome.com (use the icon \'name\')' )
                //         ->add_fields( array(
                //             Field::make( 'text', 'icon', __( 'Icon' ) ),
                //             Field::make( 'rich_text', 'text' , __( 'Text' )),
                //         )),
                // ))


                // bumper (?)
                // ->add_fields( 'bumper', 'Bumper', array(
                //     Field::make( 'text', 'titel', 'Titel' ),
                //     Field::make( 'rich_text', 'text', 'Text' ),
                //     Field::make( 'text', 'icon', 'Icoon' ),
                //     Field::make( 'image', 'image', 'Afbeelding' ),
                // ) )

                // // Second group will be a list of files for users to download
                // ->add_fields( 'file_list', 'File List', array(
                //     Field::make( 'complex', 'files', 'Files' )
                //         ->add_fields( array(
                //             Field::make( 'file', 'file', 'File' ),
                //         ) ),
                // ) )

                // // Third group will be a list of manually selected posts
                // // used as a simple curated "Related posts" listing
                // ->add_fields( 'related_posts', 'Related Posts', array(
                //     Field::make( 'association', 'posts', 'Posts' )
                //         ->set_types( array(
                //             array(
                //                 'type' => 'post',
                //                 'post_type' => 'post',
                //             ),
                //         ) ),
                // ) ),
                ) );

            // Container::make( 'post_meta', __( 'Page options' ) )
            //     ->where( 'post_type', '=', 'page' )
            //     // ->where( 'post_template', '=', 'template-section-based.php' )
            //     ->add_fields( array(Field::make( 'text', 'crb_alt_url', __( 'Alternative URL' ))) );

        Container::make('term_meta', 'Woo Category Options')
        ->where('term_taxonomy', '=', 'product_cat')
        // ->add_tab( __( 'Profile' ), array(
        ->add_fields( array(
            Field::make( 'radio', 'crb_catalogus_type', __( 'Choose catalogus type' ) )->set_visible_in_rest_api($visible = true)
            ->set_options( array(
                'shop' => 'Shop',
                'list' => 'List',
            ) ),
            Field::make( 'rich_text', 'crb_category_text', __( 'Text' ) )->set_visible_in_rest_api($visible = true),
        ) )
                // ) )
        // ->add_tab( __( 'Notification' ), array(
        //     Field::make( 'text', 'crb_email', __( 'Notification Email' ) ),
        //     Field::make( 'text', 'crb_phone', __( 'Phone Number' ) ),
        // ) )
    ;

    $fieldsToAdd = array();
    foreach($args['websiteOptions'] as $opt) {
        $fieldsToAdd[] = Field::make($opt[0], $opt[1], __($opt[2]));
    }
    Container::make('theme_options', 'Website Options')->add_fields($fieldsToAdd );

}

function remove_editor_init() {
    remove_post_type_support('page', 'editor');
}
function wprc_change_settings_capability( $capability ) {
    return 'edit_posts'; // Change the capability to users who can edit posts.
}
function set_default_page_template() {
    global $post;
    $currentScreen = get_current_screen();
    if($post->post_type == 'page' && $currentScreen->action == 'add') {
        $post->page_template = "template-section-based.php";
    }
}
function deleteWebsiteOptionsRestCache() {
    \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->delete_cache_by_endpoint( '/_mcfu638b-cms/index.php/wp-json/wtcustom/website-options' );
}
/* Remove bulk actions for type: page */
function remove_from_bulk_actions($actions) {
    return array();
}
/* Remove row actions for type: page */
function remove_page_row_actions($actions, $post) {
    if ($post->post_type == 'page') {
        $actions = array();
    }
    return $actions;
}
function customBackendStyles() {
    ?>
    <style type="text/css">
      /* #major-publishing-actions #delete-action { display: none; } */
      #taxonomy-category #taxonomy-category-new { display: none; }
      .cf-complex__inserter-button {
          border: 1px solid red;
      }
    </style>
    <?php
}
function customBackendScriptsEditorRol() {
    ?>
    <script>
        jQuery(document).ready(function($) {	
            jQuery('input[value="[HOMEPAGE]"]').attr('disabled', 'disabled').parent().next().find('button').remove();
            jQuery('input[value="Producten"]').attr('disabled', 'disabled').parent().next().find('button').remove();
            jQuery('input[value="Afspraak maken"]').attr('disabled', 'disabled').parent().next().find('button').remove();
            if(
                jQuery('input[value="[HOMEPAGE]"]').length ||
                jQuery('input[value="Producten"]').length ||
                jQuery('input[value="Afspraak maken"]').length
            ) jQuery('#major-publishing-actions #delete-action').remove();


            jQuery('.term-display-type-wrap').remove(); // wooCommerce category display type
            jQuery('.term-thumbnail-wrap').remove(); // wooCommerce category thumbnail
            jQuery('h2.nav-tab-wrapper a#settings').remove();
            jQuery('h2.nav-tab-wrapper a#endpoint-api').remove();
            jQuery('input[value="Clear REST Cache"]').parent().remove();
            jQuery('li#menu-settings').remove(); // remove settings-menu, clear-rest-cache button is active
            jQuery('select#dropdown_product_type').remove();
            jQuery('ul.subsubsub li.byorder').remove();
            jQuery('div.row-actions span.inline').remove();
            jQuery('div.row-actions span.view').remove();
            jQuery('a#add-bookly-form').remove();
            jQuery('div#woocommerce-product-data div.postbox-header h2 label[for="_virtual"]').remove();
            jQuery('div#woocommerce-product-data div.postbox-header h2 label[for="_downloadable"]').remove();
            jQuery('div#woocommerce-product-data select#product-type option[value="grouped"]').remove();
            jQuery('div#woocommerce-product-data select#product-type option[value="external"]').remove();
            jQuery('div#woocommerce-product-data select#product-type option[value="variable"]').remove();
            jQuery('ul.product_data_tabs li.shipping_options').remove();
            jQuery('ul.product_data_tabs li.linked_product_options').remove();
            jQuery('ul.product_data_tabs li.advanced_options').remove();
            jQuery('span.description a.sale_schedule').remove();
            jQuery('div#inventory_product_data div.show_if_simple.show_if_variable').remove();
            jQuery('input#attribute_public').parent().parent().remove();
            jQuery('select#attribute_orderby').parent().remove();
            let woomsg = jQuery('.wrap.woocommerce div#message').text();
            if(woomsg.indexOf("With the release of WooCommerce 4.0, these reports are being replaced. There is a new and better Analytics section")) jQuery('.wrap.woocommerce div#message').remove();
            jQuery('aside#woocommerce-activity-panel').remove();
            jQuery('a#post-preview').remove();
            jQuery('a:contains("Preview page")').remove();
            jQuery('a:contains("View page")').remove();
            jQuery('select#post_status option[value=pending]').remove();
            jQuery('div#misc-publishing-actions div#visibility').remove();
            jQuery('div#pageparentdiv p.post-attributes-label-wrapper.menu-order-label-wrapper').remove();
            jQuery('div#pageparentdiv input#menu_order').remove();
            jQuery('div#pageparentdiv p.post-attributes-help-text').remove();
        });
    </script>
    <?php
}
function customBackendScripts() {
    ?>
    <script>
        jQuery('#add_description h2').text('Meta Description');
        customizeCarbonFieldsPlugin();
        customizeNestedPagesPlugin();
        flushSimplePagesCacheOnDrag();

        function customizeCarbonFieldsPlugin() {
            let divStyles = {
                width: '87%',
                display: 'block',
            };
            let addBtnStyles = {
                backgroundColor : '#b3edb3',
                border: '2px solid #000',
                width: '100%',
                fontSize: '24px',
                color: '#000',
            };
            let collapseBtnStyles = {
                marginLeft: 'auto',
                marginTop: '10px',
                marginRight: '10px',
                padding: '0',
                paddingLeft: '5px',
                paddingRight: '5px',
                minHeight: '20px',
                lineHeight: '20px',
            };
            jQuery(document).ready(function($) {	
                jQuery('.cf-container__fields > .cf-complex--grid > .cf-field__body > .cf-complex__placeholder > .cf-complex__inserter').css(divStyles);
                jQuery('.cf-container__fields > .cf-complex--grid > .cf-field__body > .cf-complex__placeholder > .cf-complex__inserter > .cf-complex__inserter-button').css(addBtnStyles).text('Content toevoegen');
                jQuery('.cf-container__fields > .cf-complex--grid > .cf-field__body > .cf-complex__actions > .cf-complex__inserter').css(divStyles);
                jQuery('.cf-container__fields > .cf-complex--grid > .cf-field__body > .cf-complex__actions > .cf-complex__inserter > .cf-complex__inserter-button').css(addBtnStyles).text('Content toevoegen');
            });
        }
        function customizeNestedPagesPlugin() {
            jQuery(document).ready(function($) {	
                jQuery('.wrap.nestedpages .action-buttons').remove();
                jQuery('.wrap.nestedpages .nestedpages-list-header').remove();
                jQuery('.wrap.nestedpages .np-bulk-checkbox').remove();
                jQuery('.wrap.nestedpages .nestedpages-listing-title a').remove();
            });
        }

        function flushSimplePagesCacheOnDrag() {
            let menuEls = document.querySelectorAll('.wrap.nestedpages .post-type-page');
            menuEls.forEach(el => {
                el.addEventListener("mousedown", function(event) {
                    // axios.get('/_mcfu638b-cms/wp-content/themes/wtcustom/ajax/flushSimplePagesRestCache.php');
                    jQuery.ajax('/_mcfu638b-cms/wp-content/themes/wtcustom/ajax/flushSimplePagesRestCache.php');
                });
            });
        }

    </script>
    <?php
}

/** Register endpoints so they will be cached. */
add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_simple_pages_endpoint', 10, 1);
add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_simple_posts_endpoint', 10, 1);
add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_website_options_endpoint', 10, 1);
add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_simple_media_endpoint', 10, 1);
// add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_head_content_endpoint', 10, 1); /** Somehow head-content is not cached. Could be due to no json-response(?). Caching is not important, it is just for the developers **/

/*uitgezet voor rotterdamsehorecawandeling.nl, zodat ticket stock_quantity direct up-to-date is na bestellen */
// add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_woo_custom_filter_products', 10, 1);

add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_woo_custom_attributes_terms', 10, 1);
add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_woo_v3_endpoints', 10, 1);

// add_filter('wp_rest_cache/allowed_endpoints', 'wprc_add_woo_v3_endpoints_term_test', 10, 1);
function wprc_add_simple_pages_endpoint($allowed_endpoints) {
    if(!isset($allowed_endpoints['wtcustom']) || !in_array('simple-pages', $allowed_endpoints['wtcustom'])) $allowed_endpoints['wtcustom'][] = 'simple-pages';
    return $allowed_endpoints;
}
function wprc_add_simple_posts_endpoint($allowed_endpoints) {
    if(!isset($allowed_endpoints['wtcustom']) || !in_array('simple-posts', $allowed_endpoints['wtcustom'])) $allowed_endpoints['wtcustom'][] = 'simple-posts';
    return $allowed_endpoints;
}
function wprc_add_website_options_endpoint($allowed_endpoints) {
    if(!isset($allowed_endpoints['wtcustom']) || !in_array('website-options', $allowed_endpoints['wtcustom'])) $allowed_endpoints['wtcustom'][] = 'website-options';
    return $allowed_endpoints;
}
function wprc_add_simple_media_endpoint($allowed_endpoints) {
    if(!isset($allowed_endpoints['wtcustom']) || !in_array('simple-media', $allowed_endpoints['wtcustom'])) $allowed_endpoints['wtcustom'][] = 'simple-media';
    return $allowed_endpoints;
}
/** Somehow head-content is not cached. Could be due to no json-response(?). Caching is not important, it is just for the developers **/
// function wprc_add_head_content_endpoint($allowed_endpoints) {
  // if(!isset($allowed_endpoints['wtcustom']) || !in_array('head-content', $allowed_endpoints['wtcustom'])) $allowed_endpoints['wtcustom'][] = 'head-content';
  // return $allowed_endpoints;
// }
function wprc_add_woo_custom_filter_products($allowed_endpoints) {
    if(!isset($allowed_endpoints['wtcustom']) || !in_array('filter-products', $allowed_endpoints['wtcustom'])) $allowed_endpoints['wtcustom'][] = 'filter-products';
    return $allowed_endpoints;
}
function wprc_add_woo_custom_attributes_terms($allowed_endpoints) {
    if(!isset($allowed_endpoints['wtcustom']) || !in_array('attributes-terms', $allowed_endpoints['wtcustom'])) $allowed_endpoints['wtcustom'][] = 'attributes-terms';
    return $allowed_endpoints;
}
function wprc_add_woo_v3_endpoints($allowed_endpoints) {
    if(!isset($allowed_endpoints['wc']) || !in_array('v3', $allowed_endpoints['wc'])) $allowed_endpoints['wc'][] = 'v3';
    return $allowed_endpoints;
}
// function wprc_add_woo_v3_endpoints_term_test($allowed_endpoints) {
//     if(!isset($allowed_endpoints['wc/v3']) || !in_array('products/attributes/5/terms', $allowed_endpoints['wc/v3'])) $allowed_endpoints['wc/v3'][] = 'products/attributes/5/terms';
//     return $allowed_endpoints;
// }

add_action('rest_api_init', function () {
    register_rest_route('wtcustom', '/simple-pages', array(
        'methods' => 'GET',
        'callback' => 'getPagesSimplified',
    ));
});
add_action('rest_api_init', function () {
    register_rest_route('wtcustom', '/simple-posts', array(
        'methods' => 'GET',
        'callback' => 'getPostsSimplified',
    ));
});
add_action('rest_api_init', function () {
    register_rest_route('wtcustom', '/website-options', array(
        'methods' => 'GET',
        'callback' => 'getWebsiteOptions',
    ));
});
add_action('rest_api_init', function () {
    register_rest_route('wtcustom', '/simple-media', array(
        'methods' => 'GET',
        'callback' => 'getMediaSimplified',
    ));
});
/** display <head> section, (for copy-pasting plugin css and js includes) **/
add_action('rest_api_init', function () {
  register_rest_route( 'wtcustom', '/head-content',array(
    'methods'  => 'GET',
    'callback' => 'getHeadContent'
  ));
});

function getMediaSimplified(WP_REST_Request $request) {
    $media = get_posts([
        'numberposts' => -1,
        'post_type' => 'attachment',
    ]);
    $aRes = [];
    foreach ($media as $item) {
        $oP = new stdClass();
        $oP->id = $item->ID;
        $oP->url = $item->guid;
        $topic = '';
        $alt = '';
        if(isset(get_post_meta($item->ID, 'attach_to_topic')[0])) $topic = get_post_meta($item->ID, 'attach_to_topic')[0];
        if(isset(get_post_meta($item->ID, '_wp_attachment_image_alt')[0])) $alt = get_post_meta($item->ID, '_wp_attachment_image_alt')[0];
        $oP->topic = $topic;
        $oP->alt = $alt;
        $aRes[] = $oP;
    }
    $response = new WP_REST_Response($aRes);
    $response->set_status(200);
    return $response;
}
function getPagesSimplified(WP_REST_Request $request) {
    $pages = get_pages();
// var_dump($pages);
    $aRes = getPagesCollectionAttrs($pages);
    $response = new WP_REST_Response($aRes);
    $response->set_status(200);
    return $response;
}
function getPostsSimplified(WP_REST_Request $request) {
    $parameters = $request->get_params();
    $orderby = 'date';
    $order = 'DESC';
    if (isset($parameters['orderby'])) {
        $orderby = $parameters['orderby'];
    }
    if (isset($parameters['order'])) {
        $order = $parameters['order'];
    }
    $posts = get_posts([
        'numberposts' => -1,
        'orderby' => $orderby,
        'order' => $order,
    ]);
    $aRes = getPostsCollectionAttrs($posts);
    $response = new WP_REST_Response($aRes);
    $response->set_status(200);
    return $response;
}

function getWebsiteOptions() {
    global $carbonFieldsArgs; // using global. Importing does not work: https://stackoverflow.com/questions/11086773/php-function-use-variable-from-outside
    $aOptions = array();
    foreach($carbonFieldsArgs['websiteOptions'] as $opt) {
        $aOptions[$opt[1]] = carbon_get_theme_option($opt[1]);
    }
    $response = new WP_REST_Response($aOptions);
    $response->set_status(200);
    return $response;
}
function getHeadContent() {
  $res = do_action( 'wp_head' );
  $response = new WP_REST_Response($res);
  $response->set_status(200);
  return $response;
}
function getPagesCollectionAttrs($coll) {
    $aRes = [];
    foreach ($coll as $item) {
        $oP = new stdClass();
        $oP->id = $item->ID;
        $oP->title = $item->post_title;
        $oP->slug = $item->post_name;
        $oP->parent = $item->post_parent;
        $oP->order = $item->menu_order;
        $oP->status = $item->post_status;
        $oP->date = $item->post_date;
        $altUrl = carbon_get_post_meta($item->ID, 'crb_alt_url');
        $oP->alt_url = $altUrl;
        $aRes[] = $oP;
    }
    return $aRes;
}
function getPostsCollectionAttrs($coll) {
    $aRes = [];
    foreach ($coll as $item) {
        $oP = new stdClass();

        $tags = get_the_tags($item->ID);
        $aTags = array();
        if($tags) {
            foreach ($tags as $oTag) {
                $aTags[$oTag->slug] = $oTag->name;
            }
        }

        $groups = get_post_meta($item->ID, 'esplendor_group');
        $group = false;
        if($groups) {
            $group = $groups[0];
        }

        $metaTopics = get_post_meta($item->ID, 'topics');
        $topics = array();
        if($metaTopics && count(array_filter($metaTopics))) {
            $topics = $metaTopics[0];
        }

        $oP->id = $item->ID;
        $oP->title = $item->post_title;
        $oP->slug = $item->post_name;
        $oP->parent = $item->post_parent;
        $oP->order = $item->menu_order;
        $oP->status = $item->post_status;
        $oP->date = $item->post_date;
        $oP->category = get_the_category($item->ID)[0]->name;
        $oP->tags = $aTags;
        $oP->esplendor_group = $group;
        $oP->topics = $topics;
        $aRes[] = $oP;
    }
    return $aRes;
}


// Custum REST API for all attributes + terms for current category
add_action('rest_api_init', 'wp_rest_attributes_terms');
function wp_rest_attributes_terms($request) {
    register_rest_route('wtcustom', '/attributes-terms', array(
        'methods' => 'GET',
        'callback' => 'wp_rest_attributes_terms_handler',
    ));
}
function wp_rest_attributes_terms_handler($request = null) {
    $output = array();
    $params = $request->get_params();
    $category = $params['category'];

    // Use default arguments.
    $args = [
        'post_type'         => 'product',
        // 'posts_per_page'    => 10,
        'post_status'       => 'publish',
        'fields'            => 'ids',
        // 'paged'             => 1,
        // 'no_found_rows'     => true, // can make the query faster ?!?! https://wordpress.stackexchange.com/questions/177908/return-only-count-from-a-wp-query-request
    ];
    $args['tax_query']['relation'] = 'AND';
    // Category filter.
    if ( ! empty( $category ) ) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_cat',
        //   'field'    => 'slug',
            'terms'    => [ $category ],
        ];
    }

    $the_query = new \WP_Query( $args );

    if ( ! $the_query->have_posts() ) {
        return $output;
    }

    $data = array();
    while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $product = wc_get_product( get_the_ID() );  
        foreach( $product->get_attributes() as $taxonomy => $attribute ){
            if(substr($taxonomy, 0, 3) != 'pa_') continue;
            $attribute_name = wc_attribute_label( $taxonomy ); // Attribute name
            $data[$taxonomy]['name'] = $attribute_name;
            foreach ( $attribute->get_terms() as $term ){
                $data[$taxonomy]['values'][$term->term_id]['name'] = $term->name;
                // $data[$taxonomy]['values'][$term->name]['name'] = $term->name;
            }
        }

    }
    $output = $data;

    wp_reset_postdata();

    // return new WP_REST_Response($output, 123);
    $response = new WP_REST_Response($output);
    $response->set_status(200);
    return $response;
}




// Create Custom REST API for Filter (https://stackoverflow.com/questions/59135291/filter-product-list-by-mutiple-attribute-and-its-attribute-terms-in-woocommerce/66421170)
add_action('rest_api_init', 'wp_rest_filterproducts_endpoints');
function wp_rest_filterproducts_endpoints($request) {
    // register_rest_route('wp/v3', 'filter/products', array(
    register_rest_route('wtcustom', '/filter-products', array(
        'methods' => 'GET',
        'callback' => 'wp_rest_filterproducts_endpoint_handler',
    ));
}

function wp_rest_filterproducts_endpoint_handler($request = null) {
    $output = array();
    $params = $request->get_params();

    $category = $params['category'];
    $filters  = $params['filter'];
    $per_page = $params['per_page'];
    $offset   = $params['offset'];
    $order    = $params['order'];
    $orderby  = $params['orderby'];
    $count    = $params['count'];
    
    // Use default arguments.
    $args = [
        'post_type'         => 'product',
        // 'posts_per_page'    => 10,
        'post_status'       => 'publish',
        // 'paged'             => 1,
        // 'no_found_rows'     => true, // can make the query faster ?!?! https://wordpress.stackexchange.com/questions/177908/return-only-count-from-a-wp-query-request
    ];

    if ( ! empty( $count ) ) { // when counting totals, select only IDs
        $args['fields'] = 'ids';
    }

    // Posts per page.
    if ( ! empty( $per_page ) ) {
      $args['posts_per_page'] = $per_page;
    }
    // Pagination, starts from 1.
    if ( ! empty( $offset ) ) {
      $args['paged'] = $offset;
    }
    // Order condition. ASC/DESC.
    if ( ! empty( $order ) ) {
      $args['order'] = $order;
    }
    // Orderby condition. Name/Price.
    if ( ! empty( $orderby ) ) {
      if ( $orderby === 'price' ) {
        $args['orderby'] = 'meta_value_num';
      } else {
        $args['orderby'] = $orderby;
      }
    }
    // If filter buy category or attributes.
    if ( ! empty( $category ) || ! empty( $filters ) ) {
      $args['tax_query']['relation'] = 'AND';
      // Category filter.
      if ( ! empty( $category ) ) {
        $args['tax_query'][] = [
          'taxonomy' => 'product_cat',
        //   'field'    => 'slug',
          'terms'    => [ $category ],
        ];
      }
      // Attributes filter.
      if ( ! empty( $filters ) ) {
        foreach ( $filters as $filter_key => $filter_value ) {
          if ( $filter_key === 'min_price' || $filter_key === 'max_price' ) {
            continue;
          }

          $args['tax_query'][] = [
            'taxonomy' => $filter_key,
            'field'    => 'term_id',
            // 'field'    => 'slug',
            'terms'    => \explode( ',', $filter_value ),
          ];
        }
      }
      // Min / Max price filter.
      if ( isset( $filters['min_price'] ) || isset( $filters['max_price'] ) ) {
        $price_request = [];
        if ( isset( $filters['min_price'] ) ) {
          $price_request['min_price'] = $filters['min_price'];
        }
        if ( isset( $filters['max_price'] ) ) {
          $price_request['max_price'] = $filters['max_price'];
        }
        $args['meta_query'][] = \wc_get_min_max_price_meta_query( $price_request );
        }
    }
    
    $the_query = new \WP_Query( $args );

    if ( ! $the_query->have_posts() ) {
      return $output;
    }

    if ( ! empty( $count ) ) {
        $output['total'] = $the_query->found_posts;
    } else {
        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            $product = wc_get_product( get_the_ID() );  
    
            // Product Properties
            $wcproduct['id'] = $product->get_id();
            $wcproduct['name'] = $product->get_name();
            $wcproduct['price'] = $product->get_price();
            $wcproduct['regular_price'] = $product->get_regular_price();
            $wcproduct['sale_price'] = $product->get_sale_price();
            $wcproduct['slug'] = $product->get_slug();
            $wcproduct['short_description'] = $product->get_short_description();
            $mainImageId = $product->get_image_id();
            $imageGalleryIds = $product->get_gallery_image_ids();
            $AllImgSrcs = bundleProductImages($mainImageId, $imageGalleryIds);
            $wcproduct['images'] = $AllImgSrcs;
            $wcproduct['stock_quantity'] = $product->get_stock_quantity();
                        
            $output[] = $wcproduct;
        }
    }

    wp_reset_postdata();

    // return new WP_REST_Response($output, 123);
    $response = new WP_REST_Response($output);
    $response->set_status(200);
    return $response;
}
function bundleProductImages($mainId, $galleryIds) {
    $images = array();
    if($mainId) $images[] = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', wp_get_attachment_url($mainId));
    foreach($galleryIds as $imgId) $images[] = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', wp_get_attachment_url($imgId));
    return $images;
}

// add_action('admin_menu', 'wt_website_options');
// add_action('admin_init', 'wt_register_settings');

/*
function wt_website_options() {
    add_menu_page(
        'Website options', // page <title>Title</title>
        'Website options', // menu link text
        'edit_pages', // capability to access the page
        'wt-website-options', // page URL slug
        'wt_website_options_content', // callback function /w content
        'dashicons-superhero', // menu icon
        20// priority
    );
}
function wt_website_options_content() {
  if(!current_user_can('edit_pages')) return; // check user capabilities
  
  if(isset( $_GET['settings-updated'])) {
    \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->delete_cache_by_endpoint( '/_mcfu638b-cms/index.php/wp-json/wtcustom/website-options' );
    add_settings_error( 'wt_messages', 'wt_message', __( 'Settings Saved', 'wporg' ), 'updated' ); // add settings saved message with the class of "updated"
  }
  settings_errors('wt_messages'); // show error/update messages
  ?>
  <div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form method="post" action="options.php">
      <?php
        settings_fields( 'wt_website_settings' ); // settings group name
        do_settings_sections( 'wt-website-options' ); // just a page slug
        submit_button();
      ?>
    </form>
  </div>
  <?php
}
*/
/*
function wt_register_settings(){
  // add_settings_section() attrs => section ID, title (if needed), callback function (if needed), page slug
	add_settings_section('wt_settings_section_id', '', '', 'wt-website-options');

  // register_setting() attrs => settings group name, option name [, sanitization function]
	register_setting('wt_website_settings', 'website_text1');
	register_setting('wt_website_settings', 'website_text2');
	register_setting('wt_website_settings', 'website_text3');
	register_setting('wt_website_settings', 'website_text4');
	register_setting('wt_website_settings', 'website_text5');
	register_setting('wt_website_settings', 'website_textarea1');
	register_setting('wt_website_settings', 'website_textarea2');
	register_setting('wt_website_settings', 'website_textarea3');

  // add_settings_field() attrs => Reference name, Label, function which prints the field, page slug, section ID
	add_settings_field('website_text1', 'Website text 1', 'wt_text_field_html1', 'wt-website-options', 'wt_settings_section_id', array('label_for' => 'website_text1', 'class' => 'wt-option-class'));
	add_settings_field('website_text2', 'Website text 2',	'wt_text_field_html2', 'wt-website-options', 'wt_settings_section_id',	array('label_for' => 'website_text2', 'class' => 'wt-option-class'));
	add_settings_field('website_text3', 'Website text 3',	'wt_text_field_html3', 'wt-website-options', 'wt_settings_section_id',	array('label_for' => 'website_text3', 'class' => 'wt-option-class'));
	add_settings_field('website_text4', 'Website text 4',	'wt_text_field_html4', 'wt-website-options', 'wt_settings_section_id',	array('label_for' => 'website_text4', 'class' => 'wt-option-class'));
	add_settings_field('website_text5', 'Website text 5',	'wt_text_field_html5', 'wt-website-options', 'wt_settings_section_id',	array('label_for' => 'website_text5', 'class' => 'wt-option-class'));
	add_settings_field('website_textarea1', 'Website textarea 1',	'wt_textarea_field_html1', 'wt-website-options', 'wt_settings_section_id',	array('label_for' => 'website_textarea1', 'class' => 'wt-option-class'));
	add_settings_field('website_textarea2', 'Website textarea 2',	'wt_textarea_field_html2', 'wt-website-options', 'wt_settings_section_id',	array('label_for' => 'website_textarea2', 'class' => 'wt-option-class'));
	add_settings_field('website_textarea3', 'Website textarea 3',	'wt_textarea_field_html3', 'wt-website-options', 'wt_settings_section_id',	array('label_for' => 'website_textarea3', 'class' => 'wt-option-class'));
}
function wt_text_field_html1() { ?><input type="text" id="website_text1" name="website_text1" size="60" value="<?= esc_attr(get_option( 'website_text1' )) ?>" /><?php }
function wt_text_field_html2() { ?><input type="text" id="website_text2" name="website_text2" size="60" value="<?= esc_attr(get_option( 'website_text2' )) ?>" /><?php }
function wt_text_field_html3() { ?><input type="text" id="website_text3" name="website_text3" size="60" value="<?= esc_attr(get_option( 'website_text3' )) ?>" /><?php }
function wt_text_field_html4() { ?><input type="text" id="website_text4" name="website_text4" size="60" value="<?= esc_attr(get_option( 'website_text4' )) ?>" /><?php }
function wt_text_field_html5() { ?><input type="text" id="website_text5" name="website_text5" size="60" value="<?= esc_attr(get_option( 'website_text5' )) ?>" /><?php }
function wt_textarea_field_html1() { ?><textarea id="website_textarea1" name="website_textarea1" rows="8" cols="130" style="font-size:11px;"><?= esc_attr(get_option( 'website_textarea1' )) ?></textarea><?php }
function wt_textarea_field_html2() { ?><textarea id="website_textarea2" name="website_textarea2" rows="8" cols="130" style="font-size:11px;"><?= esc_attr(get_option( 'website_textarea2' )) ?></textarea><?php }
function wt_textarea_field_html3() { ?><textarea id="website_textarea3" name="website_textarea3" rows="8" cols="130" style="font-size:11px;"><?= esc_attr(get_option( 'website_textarea3' )) ?></textarea><?php }
*/

// function getWebsiteOptions() {
//     global $carbonFieldsArgs; // using global. Importing does not work: https://stackoverflow.com/questions/11086773/php-function-use-variable-from-outside
//     // var_dump($cfArgs['websiteOptions']);
//     // var_dump($args);
//     // var_dump($cfArgs->attributes['args']);
//     // echo '******';
//     // var_dump($cfArgs);
//     // echo '??????';
// // die();
//     // $aOptions = [
//     //     'website_text1' => '',
//     //     'website_text2' => '',
//     //     'website_text3' => '',
//     //     'website_text4' => '',
//     //     'website_text5' => '',
//     //     'website_textarea1' => '',
//     //     'website_textarea2' => '',
//     //     'website_textarea3' => '',
//     //     'crb_first_name' => '',
//     // ];
//     // foreach ($aOptions as $k => &$v) {
//     //     $v = get_option($k);
//     // }

//     // $aOptions['test'] = carbon_get_theme_option( 'crb_first_name' );
//     // $aOptions['test'] = $cfArgs->attributes['args']['websiteOptions'];
//     $aOptions = array();
//     foreach($carbonFieldsArgs['websiteOptions'] as $opt) {
//         $aOptions[$opt[1]] = carbon_get_theme_option($opt[1]);
//     }

//     $response = new WP_REST_Response($aOptions);
//     $response->set_status(200);
//     return $response;
// }

?>