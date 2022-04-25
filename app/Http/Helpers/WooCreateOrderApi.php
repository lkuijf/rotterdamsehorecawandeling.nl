<?php
namespace App\Http\Helpers;

class WooCreateOrderApi extends WooApiCall {
    public function __construct() {
        $this->endpoint = '/index.php/wp-json/wc/v3/orders';
        $this->method = 'POST';
    }
}
