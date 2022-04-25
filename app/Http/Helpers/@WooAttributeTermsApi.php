<?php
namespace App\Http\Helpers;

class WooAttributeTermsApi extends WooApiCall {
    public function __construct($id) {
        $this->endpoint = '/index.php/wp-json/wc/v3/products/attributes/' . $id . '/terms';
    }
}
