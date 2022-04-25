<?php
namespace App\Http\Helpers;

class WooCategoryApi extends WooApiCall {
    // public $categoriesPerParent = array();
    public function __construct($id) {
        $this->endpoint = '/index.php/wp-json/wc/v3/products/categories/' . $id;
    }
    // public function getCategoryBreadcrumbs() {
    //     $parentId = $this->parent;
    //     while($parentId != 0) {

    //     }
    // }
}
