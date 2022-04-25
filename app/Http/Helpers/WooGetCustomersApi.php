<?php
namespace App\Http\Helpers;

class WooGetCustomersApi extends WooApiCall {
    public function __construct($id = false) {
        $idUrlPart = '';
        if($id) $idUrlPart = '/' . $id;
        $this->endpoint = '/index.php/wp-json/wc/v3/customers' . $idUrlPart;
    }
}
