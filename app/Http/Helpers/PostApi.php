<?php
namespace App\Http\Helpers;

class PostApi extends ApiCall {
    public function __construct($id) {
        $this->endpoint = '/index.php/wp-json/wp/v2/posts/' . $id;
    }
}
