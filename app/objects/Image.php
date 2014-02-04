<?php

class Image {
    public $url;
    public $filename;
    
    public function __construct($url, $filename) {
        $this->url = $url;
        $this->filename = $filename;
    }
}
