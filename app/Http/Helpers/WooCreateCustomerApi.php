<?php
namespace App\Http\Helpers;

class WooCreateCustomerApi extends WooApiCall {
    public function __construct() {
        $this->endpoint = '/index.php/wp-json/wc/v3/customers';
        $this->method = 'POST';
    }
}
