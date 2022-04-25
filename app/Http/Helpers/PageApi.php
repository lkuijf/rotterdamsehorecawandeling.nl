<?php
namespace App\Http\Helpers;

class PageApi extends ApiCall {
    public function __construct($id) {
        $this->endpoint = '/index.php/wp-json/wp/v2/pages/' . $id;
    }
    public function postProcess() {
        $this->res->content->rendered = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $this->res->content->rendered);
    }
}
