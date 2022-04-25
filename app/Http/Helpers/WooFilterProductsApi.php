<?php
namespace App\Http\Helpers;

class WooFilterProductsApi extends WooApiCall {
    public function __construct() {
        $this->endpoint = '/index.php/wp-json/wtcustom/filter-products';
    }
}
