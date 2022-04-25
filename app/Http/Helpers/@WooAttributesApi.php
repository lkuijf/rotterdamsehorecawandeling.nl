<?php
namespace App\Http\Helpers;

class WooAttributesApi extends WooApiCall {
    public function __construct() {
        $this->endpoint = '/index.php/wp-json/wc/v3/products/attributes';
    }
}
