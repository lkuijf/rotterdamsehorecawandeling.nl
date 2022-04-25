<?php
namespace App\Http\Helpers;

class WooUpdateCustomerApi extends WooApiCall {
    public function __construct($id = false) {
        $idUrlPart = '';
        if($id) $idUrlPart = '/' . $id;
        $this->endpoint = '/index.php/wp-json/wc/v3/customers' . $idUrlPart;
        $this->method = 'POST';
    }
}
