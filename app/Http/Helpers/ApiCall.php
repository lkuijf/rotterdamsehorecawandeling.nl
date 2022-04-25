<?php
namespace App\Http\Helpers;

class ApiCall {
    public $id;
    // public $cmspath = 'https://mironmarine.wtgroup.nl/_mcfu638b-cms';
    // public $cmspath = 'https://mironmarine.nl/_mcfu638b-cms';
    public $cmspath = 'https://rotterdamsehorecawandeling.nl/_mcfu638b-cms';
    // public $cmspath = '/_mcfu638b-cms';
    public $endpoint;
    public $method = 'GET';
    public $payload;
    public $parameters;
    public $headers = array();
    public $httpBasicAuth;
    public $parent;
    public $res;
    public function __construct() {
    }
    public function get() {
        $curl = curl_init();

        $params = '';
        if($this->parameters) $params = '?' . http_build_query($this->parameters);

// echo "\n" . '['.$this->cmspath . $this->endpoint . $params.']' . "<br />\n";
        curl_setopt($curl, CURLOPT_URL, $this->cmspath . $this->endpoint . $params);
        if($this->method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->payload);
        }
        if($this->method == 'PUT') {
            curl_setopt($curl, CURLOPT_PUT, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->payload);
        }
        if(count($this->headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        if($this->httpBasicAuth) curl_setopt($curl, CURLOPT_USERPWD, $this->httpBasicAuth['username'] . ":" . $this->httpBasicAuth['password']);
// die('after httpBasicAuth');
        // $check_ping_resolve = ["mironmarine.nl:80:10.250.8.10"];
        // curl_setopt($curl, CURLOPT_RESOLVE, $check_ping_resolve);

        $data = curl_exec($curl);
// dd($data);
// var_dump($data);
        $headers = curl_getinfo($curl);

        curl_close($curl);
    
        $this->res = json_decode($data);
// var_dump($this->res);
// die();
        $this->postProcess(); // for additional processing of the data
        return $this->res;
    }
    public function postProcess() {}
    public function setParent($id) {
        $this->parent = $id;
    }
}
