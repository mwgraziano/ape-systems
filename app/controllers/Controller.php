<?php

class Controller {
    
    private static $instance;
    
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDOUT;
    
    public $public_views = array();
    
    public static function init() {
        
        if(is_null(self::$instance)) {
        
            $app = App::init();
            
            if(self::exists($app->request->controller)) {
                $controller = $app->request->controller;
            }
            else {
                $controller = DEFAULT_CONTROLLER;
            }
            
            self::$instance = new $controller();
            
        }
        
        return self::$instance;
        
    }
    
    //overridable authorization function per controller
    public function authorize() {
        return true;
    }
    
    public function route() {
            
        $app = App::init();
        
        $function = $app->request->method ."_". $app->request->function;
        
        if($this::AUTH_LEVEL_REQUIRED) {
            $auth_level = $app->isAuth();
            if((!$auth_level || ($auth_level < $this::AUTH_LEVEL_REQUIRED)) || !$this->authorize()) {
                //Check the explicitly public functions
                if(!in_array($function, $this->public_views)) {
                    $app->unauthorized();
                    return;
                }
            }
        }
        
        if(!is_callable(array($this, $function))) $app->redirect("/not_found");
        
        else call_user_func_array(array($this, $function), $app->request->args);
    }
    
    public static function exists($controller) {
        if(empty($controller)) return false;
        return class_exists($controller) && is_subclass_of($controller, "Controller");
    }
    
    public function respondView($view, $args = array()) {
        
        include_once(VIEW_PATH . $view .".php");
        
    }
    
    public function respondJSON($data, $skip_headers = false) {
        
        $function = uncamel(App::init()->request->function);
        
        $json = new JSON();
        
        $json->addResponse($function, $data);
        
        $json->send($skip_headers);
    }
    
    
    public function checkFields($fields, $validations = array(), $filter_post_vars = true) {
        try {
            
            $this->checkFieldsExist($fields);
            if(!empty($validations)) $this->checkFieldsValid($validations);
            
            if($filter_post_vars) {
                $app = App::init();
                
                $app->request->data = array_intersect_key($app->request->data, $fields);
                
            }
            
            return true;
            
        } catch(Exception $e) {
            
            switch($e->getCode()) {
                case ERROR_FIELD_MISSING:
                case ERROR_FIELD_INVALID:
                    $fields = json_decode($e->getMessage());
                    $this->respondJSON(new FormError($fields, $e->getCode()));
                    break;
                default:
                    $this->respondJSON(new Error(DEFAULT_ERROR_MESSAGE));
            }
        }
    }
    
    public function checkFieldsExist($fields) {
            
        $data = App::init()->request->data;
        
        $missing = array();
        
        foreach($fields as $field => $required) {
            if($required && (!isset($data[$field]) || empty($data[$field]))) $missing[] = $field;
        }
        
        if(empty($missing)) return true;
        
        throw new Exception(json_encode($missing), ERROR_FIELD_MISSING);
    }
    
    public function checkFieldsValid($fields) {
        
        $app = App::init();
        
        $failed = array();
        
        foreach($fields as $field => $validation) {
            if(empty($app->request->data[$field])) continue; // We have to go through an existence check first!!
            switch ($validation) {
                case 'email':
                    if(filter_var($app->request->data[$field], FILTER_VALIDATE_EMAIL) === false) $failed[] = $field;
                    break;
                case 'date':
                    $d = date_create_from_format("Y-m-d", $app->request->data[$field]);
                    if(!$d) $failed[] = $field;
                    break;
                case 'md5':
                    if(!is_md5($app->request->data[$field])) $failed[] = $field;
                    break;
                    break;
                case 'password':
                    if(!is_password($app->request->data[$field])) $failed[] = $field;
                    break;
                case 'checked':
                    if(empty($app->request->data[$field])) $failed[] = $field;
                    break;
                case 'session_key':
                    if($app->request->data[$field] != Session::getRequestKey()) $failed[] = $field;
                    break;
                case 'yn':
                    $app->request->data[$field] = preg_match('/^y(es)?$/i', $app->request->data[$field]) ? 1 : 0;
                    break;
                case 'phone':
                    if(!$app->request->data[$field] = is_phone($app->request->data[$field])) $failed[] = $field;
                    break;
                case "time":
                    $ct = substr_count($app->request->data[$field], ":");
                    if($ct == 1)
                        $t = date_create_from_format("H:i", $app->request->data[$field]);
                    else if($ct == 2)
                        $t = date_create_from_format("H:i:s", $app->request->data[$field]);
                    else
                        $t = false;
                    
                    if(!$t || !is_a($t, "DateTime")) $failed[] = $field;
                    break;
                case "int":
                    if(filter_var($app->request->data[$field], FILTER_VALIDATE_INT) === false) $failed[] = $field;
                    break;
            }
        }
        
        if(empty($failed)) return true;
        
        throw new Exception(json_encode($failed), ERROR_FIELD_INVALID);
    }
}

?>