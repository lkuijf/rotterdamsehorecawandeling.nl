<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use App\Http\Helpers\ApiCall;
use App\Http\Helpers\SimplePagesApi;
use App\Http\Helpers\SimplePostsApi;
use App\Http\Helpers\SimpleMediaApi;
use App\Http\Helpers\Menu;
use App\Http\Helpers\PageApi;
use App\Http\Helpers\PostApi;
use App\Http\Helpers\WebsiteOptionsApi;
use App\Http\Helpers\WooApiCall;
use App\Http\Helpers\WooCategoriesApi;
use App\Http\Helpers\WooCategoryApi;
use App\Http\Helpers\WooProductsApi;
use App\Http\Helpers\WooFilterProductsApi;
// use App\Http\Helpers\WooAttributesApi;
// use App\Http\Helpers\WooAttributeTermsApi;
use App\Http\Helpers\WooAttributesTermsCategoryApi;
use App\Http\Helpers\WooCreateOrderApi;
use App\Http\Helpers\WooCreateCustomerApi;
use App\Http\Helpers\WooGetCustomersApi;
use App\Http\Helpers\WooUpdateCustomerApi;

class ShopController extends Controller
{

    public function groupPaging($group, $pageNumber)                                            { return $this->showCategory($group, false, false, false, $pageNumber); }
    public function divisionPaging($group, $division, $pageNumber)                              { return $this->showCategory($group, $division, false, false, $pageNumber); }
    public function categoryPaging($group, $division, $category, $pageNumber)                   { return $this->showCategory($group, $division, $category, false, $pageNumber); }
    public function groupFiltering($group, $filter)                                             { return $this->showCategory($group, false, false, $filter); }
    public function divisionFiltering($group, $division, $filter)                               { return $this->showCategory($group, $division, false, $filter); }
    public function categoryFiltering($group, $division, $category, $filter)                    { return $this->showCategory($group, $division, $category, $filter); }
    public function groupFilteringPaging($group, $filter, $pageNumber)                          { return $this->showCategory($group, false, false, $filter, $pageNumber); }
    public function divisionFilteringPaging($group, $division, $filter, $pageNumber)            { return $this->showCategory($group, $division, false, $filter, $pageNumber); }
    public function categoryFilteringPaging($group, $division, $category, $filter, $pageNumber) { return $this->showCategory($group, $division, $category, $filter, $pageNumber); }

    public function showCategory($group, $division = false, $category = false, $filter = false, $pageNumber = 1) {
// dd($group, $division, $category, $filter, $pageNumber);

        $wooCategories = new WooCategoriesApi();
        $wooCategories->setHttpBasicAuth();
        $wooCategories->get();
        $allCatSlugsNested = $wooCategories->getAllSlugs();
// dd($allCatSlugsNested);
        if(!isset($allCatSlugsNested[$group]) || ($division && !isset($allCatSlugsNested[$group]['children'][$division])) || ($category && !isset($allCatSlugsNested[$group]['children'][$division]['children'][$category]))) {
            return abort(404);
        }

        $childrenCats = array();
        $aBreadCrumbs = array();
        $aBreadCrumbs['/producten'] = 'Producten';
        $parentUrl = '';
        
        $cat = $allCatSlugsNested[$group];
        $aBreadCrumbs['/producten' . '/' . $group] = $allCatSlugsNested[$group]['name'];
        if($division) {
            $cat = $allCatSlugsNested[$group]['children'][$division];
            $aBreadCrumbs['/producten' . '/' . $group . '/' . $division] = $allCatSlugsNested[$group]['children'][$division]['name'];
        }
        if($category) {
            $cat = $allCatSlugsNested[$group]['children'][$division]['children'][$category];
            $aBreadCrumbs['/producten' . '/' . $group . '/' . $division . '/' . $category] = $allCatSlugsNested[$group]['children'][$division]['children'][$category]['name'];
        }
// dd($aBreadCrumbs);
        $catId = $cat['id'];
        $catTotalCount = $cat['count'];

        if($division && !$category) {
            if(isset($allCatSlugsNested[$group]['children'][$division]['children'])) $childrenCats = $allCatSlugsNested[$group]['children'][$division]['children'];
            $parentUrl = '/' . $group . '/' . $division;
        }
        if($group && !$division) {
            if(isset($allCatSlugsNested[$group]['children'])) $childrenCats = $allCatSlugsNested[$group]['children'];
            $parentUrl = '/' . $group;
        }
        $simplePages = new SimplePagesApi();
        $htmlMenu = new Menu($simplePages->get());
        $htmlMenu->generateUlMenu();
        $options = PagesController::getWebsiteOptions();
        $cartTotalItems = $this->getTotalCartItems();
        $loggedInUserId = $this->getLoggedinUser();

        $categoryIntroText = $this->getCategoryIntroductionText($catId);

        $minPrice = 0;
        $maxPrice = 50000;
        $minPriceVal = false;
        $maxPriceVal = false;

        // $attributes = $this->getAllProductAttributes();
        $attributesTerms = $this->getAllProductAttributesTerms($catId);
// dd($attributesTerms);
        $activeFilters = $this->getFilters($filter);

        if(isset($activeFilters->min_price)) $minPriceVal = $activeFilters->min_price[0];
        if(isset($activeFilters->max_price)) $maxPriceVal = $activeFilters->max_price[0];
        $aPriceFilter = $this->setPrices($minPrice, $maxPrice, $minPriceVal, $maxPriceVal);
// dd($aPriceFilter);
        // $aFilters = $this->populateAttributesWithTerms($attributes, $activeFilters);
// dd($aFilters);
        $aFilters = $this->markActiveFilters($attributesTerms, $activeFilters);
// dd($aFilters);
        $products = $this->getProducts($catId, $activeFilters, $options['wt_shop_products_per_page'], $pageNumber);

        $prodCount = $catTotalCount; // when no active filter, use cat-count to save a query
        if(count((array)$activeFilters) != 0) {
            $pCount = $this->getProductsCount($catId, $activeFilters);
            $prodCount = 0;
            if(isset($pCount->total)) $prodCount = $pCount->total;
        }

// dd($activeFilters);
// dd($products);
// dd($aFilters);
// dd($prodCount->total);

        $aProducts = array();
        // $content = '';
        foreach($products as $prod) {
            $productData = array();
            $productData['name'] = $prod->name;
            $productData['price'] = number_format((float)$prod->price, 2, ',', '');
            $productData['regular_price'] = number_format((float)$prod->regular_price, 2, ',', '');
            $productData['sale_price'] = number_format((float)$prod->sale_price, 2, ',', '');
            // $content .= '<h2>' . $prod->name . '</h2>';
            // $content .= '<a href="/product/' . $prod->id . '/' . $prod->slug . '">Detailpagina</a>';
            $productData['url'] = '/product/' . $prod->id . '/' . $prod->slug;
            // $content .= $prod->short_description;
            $productData['shortDescription'] = $prod->short_description;
            $productData['image'] = ($prod->images[0]?$prod->images[0]:'');
            $aProducts[] = $productData;
        }

        $data= [
            'head_title' => $categoryIntroText->catName . '. Een overzicht van alle producten in deze categorie van de Miron Marine Service webwinkel.',
            'meta_description' => 'Bekijk alle ' . $categoryIntroText->catName . '. Maak gebruik van de filters voor het selecteren van het perfecte product.',
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            'cart_total' => $cartTotalItems,
            'user_logged_in' => $loggedInUserId,
            // 'main_content' => $content,
            'catIntro' => $categoryIntroText->text,
            'subCats' => $childrenCats,
            'parentCatUrl' => $parentUrl,
            'breadcrumbs' => $aBreadCrumbs,
            'products' => $aProducts,
            'filters' => $aFilters,
            'pricing' => $aPriceFilter,
            'paginationTotalCount' => $prodCount,
            'paginationPerPageCount' => $options['wt_shop_products_per_page'],
            'paginationSelectedPage' => $pageNumber,
        ];
// dd($data);
        // return $products;
        return view('shop-category')->with('data', $data);
    }
    public function showProduct($pId, $pSlug) {
        $wooProduct = new WooProductsApi($pId);
        $wooProduct->setHttpBasicAuth();
        $product = $wooProduct->get();
        
        // $wooCategories = new WooCategoriesApi();
        // $wooCategories->setHttpBasicAuth();
        // $cats = $wooCategories->get();

        $aBreadCrumbs = $this->getProductBreadcrumbs($product);
// dd($aBreadCrumbs, $product);
        if(isset($product->data->status) && $product->data->status == 404)  return abort(404);
        if($product->slug != $pSlug)                                        return abort(404);

        $simplePages = new SimplePagesApi();
        $htmlMenu = new Menu($simplePages->get());
        $htmlMenu->generateUlMenu();
        $options = PagesController::getWebsiteOptions();
        $cartTotalItems = $this->getTotalCartItems();
        $loggedInUserId = $this->getLoggedinUser();

        $data= [
            'head_title' => $product->name . ' - Bestel bij Mironmarine.nl',
            'meta_description' => $product->meta_data[0]->value,
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            'cart_total' => $cartTotalItems,
            'user_logged_in' => $loggedInUserId,
            'breadcrumbs' => $aBreadCrumbs,
            'p_id' => $product->id,
            'p_name' => $product->name,
            'p_price' => number_format((float)$product->price, 2, ',', ''),
            'p_regular_price' => number_format((float)$product->regular_price, 2, ',', ''),
            'p_sale_price' => number_format((float)$product->sale_price, 2, ',', ''),
            'p_short_description' => $product->short_description,
            'p_description' => $product->description,
            'p_attributes' => $product->attributes,
            'p_images' => $product->images,
            'p_stock_status' => $product->stock_status,
        ];

        // return view('shop-page')->with('data', $data);
        return view('shop-detail')->with('data', $data);
        // return $product;
    }
    public function showLogin() {
        $simplePages = new SimplePagesApi();
        $htmlMenu = new Menu($simplePages->get());
        $htmlMenu->generateUlMenu();
        $options = PagesController::getWebsiteOptions();
        $cartTotalItems = $this->getTotalCartItems();
        $loggedInUserId = $this->getLoggedinUser();
        $data= [
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            'cart_total' => $cartTotalItems,
            'user_logged_in' => $loggedInUserId,
        ];
        $data['meta_description'] = 'Inloggen';
        $data['head_title'] = 'Geef uw e-mail adres en wachtwoord op om in te loggen';
        $data['main_content'] = '<h1>Inloggen</h1>';
        return view('shop-login')->with('data', $data);
    }
    public function showLogout() {
        if(session_status() != 2) session_start();
        unset($_SESSION['miron_logged_in_user']);

        $simplePages = new SimplePagesApi();
        $htmlMenu = new Menu($simplePages->get());
        $htmlMenu->generateUlMenu();
        $options = PagesController::getWebsiteOptions();
        $cartTotalItems = $this->getTotalCartItems();
        $loggedInUserId = $this->getLoggedinUser();
        $data= [
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            'cart_total' => $cartTotalItems,
            'user_logged_in' => $loggedInUserId,
        ];
        $data['meta_description'] = 'Uitloggen';
        $data['head_title'] = 'U bent uitgelogd';
        $data['main_content'] = '<h1>Uitloggen</h1><p>U bent met succes uitgelogd.</p>';
        return view('shop-logout')->with('data', $data);
    }
    // public function submitLoginForm(Request $request) {
    //     $toValidate = array(
    //         'Login_Email' => 'required|email',
    //         'Login_Wachtwoord' => 'required',
    //     );
    //     $validationMessages = array(
    //         'Login_Email.required' => 'Geef het e-mail adres op.',
    //         'Login_Email.email' => 'Het e-mail adres is niet juist geformuleerd.',
    //         'Login_Wachtwoord.required' => 'Geef het wachtwoord op.',
    //     );
    //     /*  Using manually created validator, this line:
    //         $validated = $request->validate($toValidate,$validationMessages);
    //         is not redirecting properly when requesting over HTTPS
    //     */
    //     // $validated = $request->validate($toValidate,$validationMessages);
    //     $validator = Validator::make($request->all(), $toValidate, $validationMessages);
    //     if($validator->fails()) {
    //         return redirect(route('login'))
    //                     ->withErrors($validator)
    //                     ->withInput();
    //     }
    //     $getCustomer = new WooGetCustomersApi();
    //     $getCustomer->setHttpBasicAuth();
    //     $getCustomer->parameters['email'] = $request->get('Login_Email');
    //     $result = $getCustomer->get();
    //     if(!$result) {
    //         return redirect(route('login'))->withErrors(['user_not_found' => true])->withInput();
    //     }
    //     dd($result);
    //     // require_once($_SERVER['DOCUMENT_ROOT'] . "/_mcfu638b-cms/wp-load.php");
    //     // $user = get_user_by('email', $result[0]->email);
    //     // echo 'User is ' . $user->first_name . ' ' . $user->last_name;
        
    //     // $pass = 'Sleep18!';
        
    //     // if($user && wp_check_password( $pass, $user->data->user_pass, $user->ID ) ) {
    //         // return redirect(route('login'))->withErrors(['loggedin' => true]);
    //     // } else {
    //         // return redirect(route('login'))->withErrors(['loggedin' => false]);
    //     // }
    // }
    public function showBasket($checkout = false, $success = false) {
        if(session_status() != 2) session_start();
        if($success) {
            session()->flash('success', 'Bedankt voor uw bestelling!');
            unset($_SESSION['miron_cart']);
        }
        $simplePages = new SimplePagesApi();
        $htmlMenu = new Menu($simplePages->get());
        $htmlMenu->generateUlMenu();
        $options = PagesController::getWebsiteOptions();
        $cartTotalItems = $this->getTotalCartItems();
        $loggedInUserId = $this->getLoggedinUser();
// dd($cartTotalItems);
        // $customerEmail = '';
        $customerBilling = [];
        $customerShipping = [];
        if($loggedInUserId) {
            $custApi = new WooGetCustomersApi($loggedInUserId);
            $custApi->setHttpBasicAuth();
            $customer = $custApi->get();
            // $customerEmail = $customer->email;
            $customerBilling = (array)$customer->billing;
            $customerShipping = (array)$customer->shipping;
    }
// dd($customer, $customerBilling, $customerShipping);
        $data= [
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            'cart_total' => $cartTotalItems,
            'user_logged_in' => $loggedInUserId,
            // 'user_email' => $customerEmail,
            'user_billing' => $customerBilling,
            'user_shipping' => $customerShipping,
        ];
        $data['meta_description'] = 'Geplaatste producten in uw winkelmand';
        $data['head_title'] = 'Bekijk uw producten';
        $data['main_content'] = '<h1>Winkelmand</h1>';
        if($checkout) {
            $data['meta_description'] = 'Afrekenen van producten in winkelmand';
            $data['head_title'] = 'Bestel uw producten';
            $data['main_content'] = '<h1>Bestellen</h1>';
        }
        $data['shop_basket'] = array();
        $totalBasketSum = 0;
        if(isset($_SESSION['miron_cart'])) {
            foreach($_SESSION['miron_cart'] as $id => $total) {
                $wooProduct = new WooProductsApi($id);
                $wooProduct->setHttpBasicAuth();
                $product = $wooProduct->get();
// dd($product);
// var_dump((float)$product->price, $total);
                $data['shop_basket'][$id]['name'] = $product->name;
                $data['shop_basket'][$id]['url'] = '/product/' . $id . '/' . $product->slug;
                $data['shop_basket'][$id]['desc'] = $product->short_description;
                $data['shop_basket'][$id]['image'] = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $product->images[0]->src);
                $data['shop_basket'][$id]['price'] = (float)$product->price;
                $data['shop_basket'][$id]['total'] = $total;
                $totalPrice = (float)$product->price * $total;
                $data['shop_basket'][$id]['total_price'] = number_format($totalPrice, 2, ',', '');
                $totalBasketSum += $totalPrice;
            }
        }
        $data['basket_total_sum'] = number_format($totalBasketSum, 2, ',', '');
        if($checkout) return view('shop-checkout')->with('data', $data);
        return view('shop-basket')->with('data', $data);
    }
    public function submitCheckoutForm(Request $request) {
        $loggedInUserId = $this->getLoggedinUser();
        $toValidate = array(
            'Factuuradres_Voornaam' => 'required',
            'Factuuradres_Achternaam' => 'required',
            'Factuuradres_StraatEnHuisnummer' => 'required',
            'Factuuradres_Postcode' => 'required',
            'Factuuradres_Woonplaats' => 'required',
            'Betaling' => 'required',
            // 'Emailadres' => 'required|email',
        );
        $validationMessages = array(
            'Factuuradres_Voornaam.required' => 'Geef een voornaam op voor het factuuradres.',
            'Factuuradres_Achternaam.required' => 'Geef een achternaam op voor het factuuradres.',
            'Factuuradres_StraatEnHuisnummer.required' => 'Geef een straat en huisnummer op voor het factuuradres.',
            'Factuuradres_Postcode.required' => 'Geef een postcode op voor het factuuradres.',
            'Factuuradres_Woonplaats.required' => 'Geef een woonplaats op voor het factuuradres.',
            'Betaling.required' => 'Geef aan hoe u wilt betalen.',
            // 'Emailadres.required' => 'Geef een e-mail adres op.',
            // 'Emailadres.email' => 'Het e-mail adres is niet juist geformuleerd.',
        );
        if(!$loggedInUserId) {
            $toValidate['Emailadres'] = 'required|email';
            $validationMessages['Emailadres.required'] = 'Geef een e-mail adres op.';
            $validationMessages['Emailadres.email'] = 'Het e-mail adres is niet juist geformuleerd.';
        }
        if($request->get('toggleDeliveryAddr') == 'Ja') {
            $toValidate['Bezorgadres_Voornaam'] = 'required';
            $toValidate['Bezorgadres_Achternaam'] = 'required';
            $toValidate['Bezorgadres_StraatEnHuisnummer'] = 'required';
            $toValidate['Bezorgadres_Postcode'] = 'required';
            $toValidate['Bezorgadres_Woonplaats'] = 'required';
            $validationMessages['Bezorgadres_Voornaam.required'] = 'Geef een voornaam op voor het bezorgadres.';
            $validationMessages['Bezorgadres_Achternaam.required'] = 'Geef een achternaam op voor het bezorgadres.';
            $validationMessages['Bezorgadres_StraatEnHuisnummer.required'] = 'Geef een straat en huisnummer op voor het bezorgadres.';
            $validationMessages['Bezorgadres_Postcode.required'] = 'Geef een postcode op voor het bezorgadres.';
            $validationMessages['Bezorgadres_Woonplaats.required'] = 'Geef een woonplaats op voor het bezorgadres.';
        }
        if($request->get('togglePass') == 'Ja') {
            $toValidate['Password'] = array(
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    // ->symbols()
                    ->uncompromised()
            );
            $validationMessages['Password.required'] = 'Stel een wachtwoord in.';
        }

        /*  Using manually created validator, this line:
            $validated = $request->validate($toValidate,$validationMessages);
            is not redirecting properly when requesting over HTTPS
        */
        // $validated = $request->validate($toValidate,$validationMessages);
        $validator = Validator::make($request->all(), $toValidate, $validationMessages);
        if($validator->fails()) {
            return redirect(route('checkout'))
                        ->withErrors($validator)
                        ->withInput();
        }

        // if(1==1) {
        //     return redirect(route('checkout'))
        //                 ->withErrors(['user_exists' => true])
        //                 ->withInput();
        // }
//         if($request->get('togglePass') == 'Ja') {
//             // $customers = WooGetCustomersApi();
//             $custApi = new WooGetCustomersApi();
//             $custApi->setHttpBasicAuth();
//             $customers = $custApi->get();
// dd($customers);
//         }

        $shipping = array();
        $billing = array();
        $items = array();

        $billing['first_name'] = $request->get('Factuuradres_Voornaam');
        $billing['last_name'] = $request->get('Factuuradres_Achternaam');
        $billing['address_1'] = $request->get('Factuuradres_StraatEnHuisnummer');
        $billing['city'] = $request->get('Factuuradres_Postcode');
        $billing['postcode'] = $request->get('Factuuradres_Woonplaats');
        if($request->get('toggleDeliveryAddr') == 'Ja') {
            $shipping['first_name'] = $request->get('Bezorgadres_Voornaam');
            $shipping['last_name'] = $request->get('Bezorgadres_Achternaam');
            $shipping['address_1'] = $request->get('Bezorgadres_StraatEnHuisnummer');
            $shipping['city'] = $request->get('Bezorgadres_Woonplaats');
            $shipping['postcode'] = $request->get('Bezorgadres_Postcode');
        } else {
            // $billing = $shipping;
            $shipping = $billing;
        }
        $customerEmail = $request->get('Emailadres');
        if($loggedInUserId) {
            $custApi = new WooGetCustomersApi($loggedInUserId);
            $custApi->setHttpBasicAuth();
            $customer = $custApi->get();
            $customerEmail = $customer->email;
        }
        $billing['email'] = $customerEmail;
        // if($request->get('togglePass') == 'Ja') $billing['password'] = $request->get('Password');
        if(session_status() != 2) session_start();
        if(isset($_SESSION['miron_cart'])) {
            foreach($_SESSION['miron_cart'] as $id => $total) {
                $itemOrdered = array();
                $itemOrdered['product_id'] = $id;
                $itemOrdered['quantity'] = $total;
                $items[] = $itemOrdered;
            }
        }
// dd($shipping, $billing, $items);
        if($request->get('togglePass') == 'Ja') {
            $wooCustomer = $this->createWooCustomer($shipping, $billing, $request->get('Password'));
            if(isset($wooCustomer->code) && $wooCustomer->code == 'registration-error-email-exists') {
                return redirect(route('checkout'))
                    ->withErrors(['user_exists' => true])
                    ->withInput();
            }
        }
        if($loggedInUserId) { //edit
            $wooEditCustomer = $this->editWooCustomer($shipping, $billing, $loggedInUserId);
        }
        $wooOrder = $this->createWooOrder($shipping, $billing, $items, $loggedInUserId);
// die('woo order created!');
        $to_email = 'leon.kuijf@gmail.com';
        // $to_email = 'frans@tamatta.org, rense@tamatta.org';
        $subject = 'Bestelling vanaf Mironmarine.nl';

        // $clientEmail = $request->get('Emailadres');
        $clientSubject = 'Bevestiging van uw bestelling op Mironmarine.nl';

        $orderMessage = '
            * Bestelnummer *
            ' . $wooOrder->id . '
            ';
            $orderMessage .= '
            * Bezorgadres *
            Voornaam: ' . $shipping['first_name'] . '
            Achternaam: ' . $shipping['last_name'] . '
            Straat en huisnummer: ' . $shipping['address_1'] . '
            Postcode: ' . $shipping['postcode'] . '
            Woonplaats : ' . $shipping['city'] . '
            ';
            if($request->get('toggleDeliveryAddr') != 'Ja') {
            $orderMessage .= '
            * Factuuradres *
            Voornaam: ' . $billing['first_name'] . '
            Achternaam: ' . $billing['last_name'] . '
            Straat en huisnummer: ' . $billing['address_1'] . '
            Postcode: ' . $billing['postcode'] . '
            Woonplaats : ' . $billing['city'] . '
            ';
            }
            $orderMessage .= '
            * E-mail adres *
            ' . $billing['email'] . '
            Aanmelden nieuwsbrief: ' . ($request->get('AanmeldenNieuwsbrief')=='Ja'?'Ja':'Nee') . '
            ';
            $orderMessage .= '
            * Bestelde producten *';
            $totalBasketSum = 0;
            if(count($items)) {
                foreach($items as $prod) {
                    $wooProduct = new WooProductsApi($prod['product_id']);
                    $wooProduct->setHttpBasicAuth();
                    $product = $wooProduct->get();
                    $totalPrice = (float)$product->price * $prod['quantity'];
                    $totalBasketSum += $totalPrice;
                    $orderMessage .= '
                    ' . $prod['quantity'] . 'x ' . $product->name . ' (id: ' . $prod['product_id'] . ') - €' . number_format((float)$product->price, 2, ',', '') . ' per stuk - Totaalprijs: €' . number_format($totalPrice, 2, ',', '');
                }
            }
            $orderMessage .= '

            * Extra informatie *
            ' . (trim($request->get('Bericht'))!=''?$request->get('Bericht'):'-') . '
            ';
            $orderMessage .= '
            * Totaalbedrag bestelling *
            €' .  number_format($totalBasketSum, 2, ',', '') . '
            ';
            $orderMessage .= '
            * Betaalmethode *
            ' . $request->get('Betaling') . '
            ';

        $headers = array(
            "From: bestelformulier@mironmarine.nl",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=ISO-8859-1",
            "X-Priority: 1",
        );
        $headers = implode("\r\n", $headers);
        mail($to_email, $subject, "De volgende informatie is ingevuld:\n" . $orderMessage);
        mail($customerEmail, $clientSubject, "Bevestiging van uw bestelling:\n" . $orderMessage);
        // return back()->with('success', 'Bedankt voor uw bestelling!');
        return $this->showBasket(false, true);
    }
    public function showCheckout() {
        return $this->showBasket(true);
    }
    public function getProducts($cId, $filters, $prodsPerPage, $pageNr) {
        $wooProducts = new WooFilterProductsApi();
        $wooProducts->setHttpBasicAuth();
        $wooProducts->parameters['category'] = $cId;
        $wooProducts->parameters['per_page'] = $prodsPerPage;
        $wooProducts->parameters['offset'] = $pageNr;
        foreach($filters as $filter => $values) {
            $wooProducts->parameters['filter[' . $filter . ']'] = $values[0];
        }
// dd($wooProducts->parameters);
        $allProducts = $wooProducts->get();
        return $allProducts;
    }
    public function getProductsCount($cId, $filters) {
        $wooProducts = new WooFilterProductsApi();
        $wooProducts->setHttpBasicAuth();
        $wooProducts->parameters['category'] = $cId;
        // $wooProducts->parameters['per_page'] = $prodsPerPage;
        // $wooProducts->parameters['offset'] = $pageNr;
        foreach($filters as $filter => $values) {
            $wooProducts->parameters['filter[' . $filter . ']'] = $values[0];
        }
        $wooProducts->parameters['count'] = true;
// dd($wooProducts->parameters);
        $allProducts = $wooProducts->get();
        return $allProducts;
    }
    // public function getAllProductAttributes() {
    //     $wooAttributes = new WooAttributesApi();
    //     $wooAttributes->setHttpBasicAuth();
    //     $allAttributes = $wooAttributes->get();
    //     return $allAttributes;
    // }
    public function getAllProductAttributesTerms($catId) {
        $wooAttributesTerms = new WooAttributesTermsCategoryApi();
        $wooAttributesTerms->parameters['category'] = $catId;
        $wooAttributesTerms->setHttpBasicAuth();
        $allAttributesTerms = $wooAttributesTerms->get();
        return $allAttributesTerms;
    }
//     public function populateAttributesWithTerms($attributes, $active) {
//         $filters = array();
//         foreach($attributes as $attr) {
//             $wooAttributeTerms = new WooAttributeTermsApi($attr->id);
//             $wooAttributeTerms->setHttpBasicAuth();
//             $allAttributeTerms = $wooAttributeTerms->get();
//             $filterVals = array();
//             foreach($allAttributeTerms as $term) {
//                 $filterVals[$term->slug]['name'] = $term->name;
//                 if(isset($active->{$attr->slug}[0]) && $active->{$attr->slug}[0] == $term->slug) $filterVals[$term->slug]['active'] = true;
//             }
//             $filters[$attr->slug]['name'] = $attr->name;
//             $filters[$attr->slug]['values'] = $filterVals;
//         }
// // dd($attributes, $active, $filters);
//         return $filters;
//     }
    public function markActiveFilters($attrsTerms, $active) {
        foreach($attrsTerms as $attrKey => $attr) {
            $filterVals = array();
            foreach($attr->values as $termId => $term) {
                if(isset($active->{$attrKey}) && isset($active->{$attrKey}[0]) && $active->{$attrKey}[0] == $termId) $attrsTerms->{$attrKey}->values->{$termId}->active = true;
            }
        }
        return json_decode(json_encode($attrsTerms), true);
        // return $attrsTerms;
    }
    public function getFilters($filter) {
        // Op dit moment een enkele keuze per filter: /articles/filter/group_es~tag_strategic-design~sort_date
        // Maar misschien in de toekomst meerdere keuzes per filter: /articles/filter/group_es.ed.ei~tag_strategic-design.mobile~sort_date
        $res = new \stdClass();
        if($filter) {
            $aFilters = explode('~', $filter);
            foreach($aFilters as $f) {
                $aFilterTypeVals = explode(':', $f);
                $res->{$aFilterTypeVals[0]} = explode('.', $aFilterTypeVals[1]);
            }
        }
        return $res;
    }
    public function setPrices($minP, $maxP, $minVal, $maxVal) {
        $prices = array();
        $prices['minPrice'] = $prices['minPriceValue'] = $minP;
        $prices['maxPrice'] = $prices['maxPriceValue'] = $maxP;
        if($minVal) $prices['minPriceValue'] = $minVal;
        if($maxVal) $prices['maxPriceValue'] = $maxVal;
        return $prices;
    }
    public function getCategoryIntroductionText($id) {
        $res = new \stdClass();
        $wooCat = new WooCategoryApi($id);
        $wooCat->setHttpBasicAuth();
        $category = $wooCat->get();
// dd($category);
        $text = '<h1>' . $category->name . '</h1>';
        if(isset($category->crb_category_text) && $category->crb_category_text) $text = $category->crb_category_text;
        $res->text = $text;
        $res->catName = $category->name;
        return $res;
    }
    public function getProductBreadcrumbs($prod) {
        $res = array();
        $wooCategories = new WooCategoriesApi();
        $wooCategories->setHttpBasicAuth();
        $wooCategories->get();
        $wooCategories->getAllCatsById();
        $aCrumbs = array();
        $catId = $prod->categories[0]->id;
        $aCrumbs[0]['slug'] = $wooCategories->categoriesById[$catId]['slug'];
        $aCrumbs[0]['name'] = $wooCategories->categoriesById[$catId]['name'];
        $parentId = $wooCategories->categoriesById[$catId]['parent'];
        while($parentId != 0) {
            $aC = array();
            $aC['slug'] = $wooCategories->categoriesById[$parentId]['slug'];
            $aC['name'] = $wooCategories->categoriesById[$parentId]['name'];
            $aCrumbs[] = $aC;
            $parentId = $wooCategories->categoriesById[$parentId]['parent'];
        }
        $aCrumbs = array_reverse($aCrumbs);
        $urlBuilder = '/producten';
        $res[$urlBuilder] = 'Producten';
        foreach($aCrumbs as $crumb) {
            $urlBuilder .= '/' . $crumb['slug'];
            $res[$urlBuilder] = $crumb['name'];
        }
        $res['/product/' . $prod->id . '/' . $prod->slug] = $prod->name;
        return $res;
    }
    public function getTotalCartItems() {
        if(session_status() != 2) session_start();
        $total = 0;
        if(isset($_SESSION['miron_cart'])) {
            foreach($_SESSION['miron_cart'] as $count) {
                $total += $count;
            }
        }
        return $total;
    }
    public function getLoggedinUser() {
        if(session_status() != 2) session_start();
        if(isset($_SESSION['miron_logged_in_user'])) {
            return $_SESSION['miron_logged_in_user'];
        }
        return false;
    }
    public function createWooOrder($ship, $bill, $products, $customerId) {
        $wooOrder = new WooCreateOrderApi();
        $wooOrder->setHttpBasicAuth();
        $data = [
            'payment_method' => 'bacs',
            'payment_method_title' => 'Direct Bank Transfer',
            'set_paid' => true,
            'billing' => $bill,
            'shipping' => $ship,
            'line_items' => $products,
            'customer_id' => $customerId,
            // 'shipping_lines' => [
            //     [
            //         'method_id' => 'flat_rate',
            //         'method_title' => 'Flat Rate',
            //         'total' => '10.00',
            //     ]
            // ]
        ];
        $wooOrder->payload = json_encode($data);
        $wooOrder->headers[] = 'Content-Type: application/json';
        
        $result = $wooOrder->get();
// echo json_encode($data) . "\n\n";
// dd(json_encode($data), $result);
        return $result;
    }
    public function createWooCustomer($ship, $bill, $password) {
        $wooCustomer = new WooCreateCustomerApi();
        $wooCustomer->setHttpBasicAuth();
        $data = [
            'email' => $bill['email'],
            'username' => $bill['email'],
            'password' => $password,
            'first_name' => $bill['first_name'],
            'last_name' => $bill['last_name'],
            'date_created' => date("Y-m-d h:i:s"),
            'billing' => $bill,
            'shipping' => $ship,
        ];
        $wooCustomer->payload = json_encode($data);
// dd(json_encode($data));
        $wooCustomer->headers[] = 'Content-Type: application/json';
        $result = $wooCustomer->get();
        return $result;
    }
    public function editWooCustomer($ship, $bill, $userId) {
// dd($ship, $bill, $userId);
        $wooCustomer = new WooUpdateCustomerApi($userId);
        $wooCustomer->setHttpBasicAuth();
        $data = [
            // 'email' => $bill['email'],
            // 'username' => $bill['email'],
            // 'password' => $password,
            'first_name' => $bill['first_name'],
            'last_name' => $bill['last_name'],
            // 'date_created' => date("Y-m-d h:i:s"),
            'billing' => $bill,
            'shipping' => $ship,
        ];
        $wooCustomer->payload = json_encode($data);
// dd(json_encode($data));
        $wooCustomer->headers[] = 'Content-Type: application/json';
        $result = $wooCustomer->get();
        return $result;
    }
}
