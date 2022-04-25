<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Helpers\ApiCall;
use App\Http\Helpers\SimplePagesApi;
use App\Http\Helpers\SimplePostsApi;
use App\Http\Helpers\SimpleMediaApi;
use App\Http\Helpers\Menu;
use App\Http\Helpers\PageApi;
use App\Http\Helpers\PostApi;
use App\Http\Helpers\WebsiteOptionsApi;
// use App\Http\Helpers\WooApiCall;
// use App\Http\Helpers\WooCategoriesApi;

// use App\Http\Helpers\WooApiCall;
// use App\Http\Helpers\WooCategoriesApi;
// use App\Http\Helpers\WooCategoryApi;
// use App\Http\Helpers\WooProductsApi;
// use App\Http\Helpers\WooFilterProductsApi;
// use App\Http\Helpers\WooAttributesTermsCategoryApi;
// use App\Http\Helpers\WooCreateOrderApi;
// use App\Http\Helpers\WooCreateCustomerApi;
// use App\Http\Helpers\WooGetCustomersApi;
// use App\Http\Helpers\WooUpdateCustomerApi;


class PagesController extends Controller
{
    public function home() {
        return view('page');
    }

    public function showOnePager($orderId = false) {
        $simplePages = new SimplePagesApi();
// dd($simplePages->get());
        $spages = $simplePages->get();
        $htmlMenu = new Menu($spages);
        $htmlMenu->generateUlMenu();
        $options = $this->getWebsiteOptions();

        $simpleMedia = new SimpleMediaApi();
        $simpleMedia->get();
        $allMediaById = $simpleMedia->makeListById();

        // if($orderId) {
        //     var_dump($_SERVER);
        // }

        $options['wt_website_logo'] = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $allMediaById[$options['wt_website_logo']]->url);
        foreach($options['wt_website_background_images'] as &$image) {
            $image = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $allMediaById[$image]->url);
        }

        $products = ShopController::getProducts(0, [], 99, 1);
// dd($products);
        $allCrbSections = array();
        foreach($spages[0] as $sPage) {
// dd($sPage);
            $pageA = [];
            $pageA['type'] = '_anchor';
            $pageA['value'] = $sPage->slug;
            $allCrbSections[] = $pageA;
            $crbSecs = $this->getPageCrbSections($sPage->id);
            $allCrbSections = array_merge($allCrbSections, $crbSecs);
        }

        $data= [
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            'content_sections' => $allCrbSections,
            'tickets' => $products,
        ];
        return view('page')->with('data', $data);
    }
    public function showOnePagerCheckout($orderId = false) {
        $options = $this->getWebsiteOptions();
        $simpleMedia = new SimpleMediaApi();
        $simpleMedia->get();
        $allMediaById = $simpleMedia->makeListById();
        $options['wt_website_logo'] = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $allMediaById[$options['wt_website_logo']]->url);
        foreach($options['wt_website_background_images'] as &$image) {
            $image = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $allMediaById[$image]->url);
        }
        if($orderId) {
            // var_dump($_SERVER);
        }
        $data= [
            'html_menu' => '',
            'website_options' => $options,
            'content_sections' => [],
        ];
        return view('page-checkout')->with('data', $data);
    }

    public function showPage($section, $page, $subpage) {
        $simplePages = new SimplePagesApi();
        $htmlMenu = new Menu($simplePages->get());
        $htmlMenu->generateUlMenu();

        $allSlugsNested = $simplePages->getAllSlugs();

        if(!isset($allSlugsNested[$section]) || ($page && !isset($allSlugsNested[$section]['children'][$page])) || ($subpage && !isset($allSlugsNested[$section]['children'][$page]['children'][$subpage]))) {
            return abort(404);
        } else {
            $pageId = $allSlugsNested[$section];
            if($page) $pageId = $allSlugsNested[$section]['children'][$page];
            if($subpage) $pageId = $allSlugsNested[$section]['children'][$page]['children'][$subpage];
            if(is_array($pageId)) $pageId = $pageId['id'];
        }

        $content = $this->getContent($pageId);
        $options = $this->getWebsiteOptions();
        $cartTotalItems = ShopController::getTotalCartItems();
        $loggedInUserId = ShopController::getLoggedinUser();
// dd($content->contentSections);
        $data= [
            'head_title' => $content->pageTitle,
            'meta_description' => $content->pageMetaDescription,
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            'cart_total' => $cartTotalItems,
            'user_logged_in' => $loggedInUserId,
            'content_sections' => $content->contentSections,
        ];
        if($section == 'contact')
            return view('contact-page')->with('data', $data);
        else if($section == 'producten') {
            $mainCats = $this->getMainMenuItems();
            $data['shop_main_cats'] = $mainCats;
            return view('shop-root-category')->with('data', $data);
        } else if($section == 'afspraak-maken') {
            return view('bookly-page')->with('data', $data);
        } else
        return view('standard-page')->with('data', $data);
    }
    public function getPageCrbSections($id) {
        $reqPage = new PageApi($id);
        $pageData = $reqPage->get();
        $allSections = [];
        if(isset($pageData->crb_sections) && count($pageData->crb_sections)) {
            $sections = $this->handleCrbSections($pageData->crb_sections);
            $allSections = array_merge($allSections, $sections); 
        }
        return $allSections;
    }
    public function handleCrbSections($pCrbSecs) {
        $secs = [];
        foreach($pCrbSecs as $sec) {
            $s = [];
            $s['type'] = $sec->_type;
            if($sec->_type == 'text') {
                // $s['color'] = $sec->color;
                $s['text'] = $sec->text;
                // $s['gallery'] = [];
                // if(isset($sec->crb_media_gallery) && count($sec->crb_media_gallery)) {
                //     foreach($sec->crb_media_gallery as $mediaId) {
                //         $img = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $allMediaById[$mediaId]->url);
                //         $alt = str_replace(['-', '_'], ' ', pathinfo($img, PATHINFO_FILENAME));
                //         if($allMediaById[$mediaId]->alt) $alt = $allMediaById[$mediaId]->alt;
                //         $i['img'] = $img;
                //         $i['alt'] = $alt;
                //         $s['gallery'][] = $i;
                //     }
                // }
            }
            if($sec->_type == 'order_form') {
                $s['checked'] = $sec->crb_show_order_form;
            }
            if($sec->_type == 'hero') {
                $img = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $sec->image);
                $s['img'] = $img;
            }
            if($sec->_type == 'solutions') {
                $s['icon_boxes'] = [];
                if(isset($sec->icon_boxes) && count($sec->icon_boxes)) {
                    foreach($sec->icon_boxes as $box) {
                        $b['icon'] = $box->icon;
                        $b['text'] = $box->text;
                        $s['icon_boxes'][] = $b;
                    }
                }
            }
            if($sec->_type == 'activities') {
                $s['fields'] = [];
                if(isset($sec->activity_fields) && count($sec->activity_fields)) {
                    foreach($sec->activity_fields as $field) {
                        $s['fields'][] = $field->text;
                    }
                }
            }
            if($sec->_type == 'services') {
                $s['icon_boxes'] = [];
                if(isset($sec->icon_boxes) && count($sec->icon_boxes)) {
                    foreach($sec->icon_boxes as $box) {
                        $b['icon'] = $box->icon;
                        $b['text'] = $box->text;
                        $s['icon_boxes'][] = $b;
                    }
                }
            }
            $secs[] = $s;
        }
        return $secs;
    }
    public function getContent($id) {
        $res = new \stdClass();
        $metaDesc = '';
        $hTitle = '';
        $sections = [];
        $reqPage = new PageApi($id);
        $pageData = $reqPage->get();
        foreach($pageData->head_tags as $htag) {
            if(isset($htag->attributes->name) && $htag->attributes->name == 'description') $metaDesc = $htag->attributes->content;
        }
        if($pageData->title->rendered == '[HOMEPAGE]') $hTitle = 'Welkom bij Mironmarine.nl, voor al uw bootonderhoud en -reparatie. Plan een grote of kleine servicebeurt in voor zorgeloos vaarplezier!';
        else $hTitle = $pageData->title->rendered . ' - Mironmarine.nl';

        $simpleMedia = new SimpleMediaApi();
        $simpleMedia->get();
        $allMediaById = $simpleMedia->makeListById();
        if($pageData->content->rendered) {
            $s = [];
            $s['type'] = 'text';
            $s['text'] = $pageData->content->rendered;
            $s['color'] = '';
            $s['gallery'] = [];
            $sections[] = $s;
        }
        if(isset($pageData->crb_sections) && count($pageData->crb_sections)) {
            $sections = $this->handleCrbSections($pageData->crb_sections);
        }

        $res->pageMetaDescription = $metaDesc;
        $res->pageTitle = $hTitle;
        $res->contentSections = $sections;
        return $res;
    }
    public static function getWebsiteOptions() {
        $allWebsiteOptions = new WebsiteOptionsApi();
        $websiteOptions = $allWebsiteOptions->get();
        return (array)$websiteOptions;
    }
    public function getMainMenuItems() {
        $cats = array();
        $wooCats = new WooCategoriesApi();
        $wooCats->setHttpBasicAuth();
        $allCats = $wooCats->get();
        foreach($allCats[0] as $rootCats) {
            if($rootCats->slug == 'uncategorized') continue;
            $cats[$rootCats->slug] = $rootCats->name;
        }
        return $cats;
    }
}
