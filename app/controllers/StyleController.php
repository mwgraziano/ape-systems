<?php

class StyleController extends Controller {
    
    public function get_index() {
        
        $app = App::init();
        
        $styles = $app->getStyle();
        
        $style_file = file_get_contents(VIEW_PATH ."ape.css");
        
        $style_file = str_replace(array_keys($styles), array_values($styles), $style_file);
        
        header("Content-Type: text/css");
        echo $style_file;
        exit;
    }
}
