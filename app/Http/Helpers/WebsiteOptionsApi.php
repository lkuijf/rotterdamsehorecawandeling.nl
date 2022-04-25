<?php
namespace App\Http\Helpers;

class WebsiteOptionsApi extends ApiCall {
    public function __construct() {
        $this->endpoint = '/index.php/wp-json/wtcustom/website-options';
    }
}
