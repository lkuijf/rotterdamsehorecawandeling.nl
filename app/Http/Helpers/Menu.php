<?php
namespace App\Http\Helpers;

class Menu {
    public $allPagesFlattenedPerParent;
    public $html;
    public $bNestPageUrls = true;
    public function __construct($menu) {
        $this->allPagesFlattenedPerParent = $menu;
    }
    public function generateUlMenu($parentId = 0, $url = '') {
        if(!$this->bNestPageUrls) $url = '';
        if($parentId == 0) $this->html .= '<ul itemscope itemtype="http://www.schema.org/SiteNavigationElement">' . "\n";
        else $this->html .= '<ul>' . "\n";
        // foreach($this->allPagesFlattenedPerParent[$parentId] as $page) {
        for($x=0;$x<count($this->allPagesFlattenedPerParent[$parentId]);$x++) { // using for(), key can be an order integer
            $page = $this->allPagesFlattenedPerParent[$parentId][$x];
            if($page->title == '[HOMEPAGE]') continue;
            // if($page->title == '[HOMEPAGE]') $page->title = 'Home';
            $pageUrl = $url . '/' . $page->slug;
            $this->html .= '<li itemprop="name">';
            // if(substr_count($pageUrl, '/') == 2) {
                // $this->html .= '<a href="#">'; // only for Miron Marine Service
            // } else {
                $href = $pageUrl;
                if(isset($page->alt_url) && $page->alt_url) $href = $page->alt_url; // mironmarine.nl
                // $this->html .= '<a itemprop="url" href="' . $href . '">';
                $this->html .= '<a itemprop="url" href="#' . substr($href, 1) . '">'; // rotterdamsehorecawandeling.nl (onepager)
            // }
            $this->html .= $page->title;
            $this->html .= '</a>';
            if(isset($this->allPagesFlattenedPerParent[$page->id])) $this->generateUlMenu($page->id, $pageUrl);
            $this->html .= '</li>' . "\n";
        }
        $this->html .= '</ul>' . "\n";
    }
    public function show() {
        echo $this->html;
    }
}
