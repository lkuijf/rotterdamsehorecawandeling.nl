<?php
namespace App\Http\Helpers;

class SimplePostsApi extends ApiCall {
    public function __construct() {
        $this->endpoint = '/index.php/wp-json/wtcustom/simple-posts';
    }
    public function setParameters($oFilters) {
        if(isset($oFilters->sort[0])) {
            if($oFilters->sort[0] == 'newest-first') { $this->parameters['orderby'] = 'date'; $this->parameters['order'] = 'desc'; }
            if($oFilters->sort[0] == 'oldest-first') { $this->parameters['orderby'] = 'date'; $this->parameters['order'] = 'asc'; }
        }
    }
    public function getPosts($amountPerPage = -1, $category = ['Articles'], $oFilters = false, $pageNr = 1) {
        $data = new \stdClass();
        $data->totalItems = 0;
        $data->postsToShow = array();
        // $data = {
        //     'totalItems' => 0,
        //     'postsToShow' => array()
        // }

        $aPosts = array();
        $aIdsToShow = array();
        foreach($this->res as $simplepost) {
// var_dump($oFilters);
// var_dump($simplepost);

            $correctCat = false;
            $correctTag = true;
            $correctGroup = true;
// var_dump($simplepost->category);
// var_dump($category);
            // if($simplepost->category == $category) $correctCat = true;
            if(in_array($simplepost->category, $category)) $correctCat = true;
            if(isset($oFilters->tag)) {
                $correctTag = false;
                foreach($simplepost->tags as $slug => $name) {
                    if(in_array($slug, $oFilters->tag)) $correctTag = true;
                }
            }
            if(isset($oFilters->group)) {
                $correctGroup = false;
                if($simplepost->esplendor_group) {
                    $postGroup = '';
                    if($simplepost->esplendor_group == 'design')      $postGroup = 'gd';
                    if($simplepost->esplendor_group == 'systems')     $postGroup = 'gs';
                    if($simplepost->esplendor_group == 'cortex')      $postGroup = 'gc';
                    if($simplepost->esplendor_group == 'elevate')     $postGroup = 'ge';
                    if($simplepost->esplendor_group == 'industries')  $postGroup = 'gi';
                    if(in_array($postGroup, $oFilters->group)) $correctGroup = true;
                }
            }
            if($correctCat && $correctTag && $correctGroup) {
                $info['id'] = $simplepost->id;
                $info['cat'] = $simplepost->category;
                $aIdsToShow[] = $info;
            }
        }

        $data->totalItems = count($aIdsToShow);

        $iStart = ($amountPerPage * $pageNr) - $amountPerPage; // 0, 4
        $iEnd = ($iStart + $amountPerPage) - 1; // 3, 7
        for($x=$iStart;$x<=$iEnd;$x++) {
            if(isset($aIdsToShow[$x])) {
                $post = new PostApi($aIdsToShow[$x]['id']);
                $oPost = $post->get();
                $oPost->categoryName = $aIdsToShow[$x]['cat'];
                $aPosts[] = $oPost;
            }
        }

        $data->postsToShow = $aPosts;

        return $data;
    }
    public function getAllSlugs() {
        $slugs = array();
        foreach($this->res as $post) {
            $slugs[$post->slug] = $post->id;
        }
        return $slugs;
    }
    public function getTags() {
        $tags = array();
        foreach($this->res as $post) {
            foreach($post->tags as $slug => $tag) {
                $tags[$slug] = $tag;
            }
        }
        ksort($tags); // order tags by key. tags can jump arround after sorting posts when not ordered.
        return $tags;
    }
    public function getTopics() {
        $topics = array();
        foreach($this->res as $post) {
            foreach($post->topics as $topic) {
                $topics[] = $topic;
            }
        }
        $topics = array_unique($topics);
        return $topics;
    }
}
