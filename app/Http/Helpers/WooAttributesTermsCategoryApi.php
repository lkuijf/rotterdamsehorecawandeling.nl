<?php
namespace App\Http\Helpers;

class WooAttributesTermsCategoryApi extends WooApiCall {
    public function __construct() {
        $this->endpoint = '/index.php/wp-json/wtcustom/attributes-terms';
    }
}
