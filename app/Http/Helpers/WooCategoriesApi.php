<?php
namespace App\Http\Helpers;

class WooCategoriesApi extends WooApiCall {
    public $categoriesPerParent = array();
    public $categoriesById = array();
    public function __construct() {
        $this->endpoint = '/index.php/wp-json/wc/v3/products/categories';
    }
    public function postProcess() {
        /* Group children */
        foreach($this->res as $cat) {
            $order = $cat->menu_order;
            if(isset($this->categoriesPerParent[$cat->parent][$order])) $order = count($this->categoriesPerParent[$cat->parent]); /* als de order 0,0,0,0 is bijvoorbeeld */
            $this->categoriesPerParent[$cat->parent][$order] = $cat;
        }
        foreach($this->categoriesPerParent as &$arr) {
            ksort($arr); // Sort an array by key in ascending order
            $arr = array_values($arr); // make logical 0,1,2,3,4 key values (no gaps)
        }
        $this->res = $this->categoriesPerParent;
    }
    public function getAllSlugs($parentId = 0, $url = '') {
        foreach($this->categoriesPerParent[$parentId] as $cat) {
            $slugs[$cat->slug]['id'] = $cat->id;
            $slugs[$cat->slug]['name'] = $cat->name;
            $slugs[$cat->slug]['count'] = $cat->count;
            if(isset($this->categoriesPerParent[$cat->id])) $slugs[$cat->slug]['children'] = $this->getAllSlugs($cat->id);
        }
        return $slugs;
    }
    public function getAllCatsById($parentId = 0) {
        foreach($this->categoriesPerParent[$parentId] as $cat) {
            $this->categoriesById[$cat->id]['slug'] = $cat->slug;
            $this->categoriesById[$cat->id]['name'] = $cat->name;
            $this->categoriesById[$cat->id]['parent'] = $cat->parent;
            if(isset($this->categoriesPerParent[$cat->id])) $this->getAllCatsById($cat->id);
        }
    }
}
