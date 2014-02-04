<?php

class FilesController extends Controller {
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDIN;
    
    
    public function get_index($file_id = false) {
        $app = App::init();
        
        if(!empty($file_id))
        {
            $f = UserFile::load($file_id);
            if($f){
                    
                if(!$app->authObject($f, PERMISSION_READ)) {
                    
                    $this->respondView("unauthorized");
                    
                }
                else 
                {
                    
                    $filename = S3_USER_FILE_DIR ."/". $f->getUserId() ."/". basename($f->getPath());
                    
                    if($file = S3Service::getfile($filename)) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: '. $file->headers['type']);
                        header('Content-Disposition: attachment; filename='. basename($f->getPath()));
                        header('Content-Transfer-Encoding: binary');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . $file->headers['size']);
                        ob_clean();
                        flush();
                        echo $file->body;
                        exit;
                    }
                }
            }
            
            $this->respondView("not_found");
        }
        else $this->respondView("my_files");
    }
    
    public function post_index() {
        
        $app = App::init();
        $user = $app->user;
        
        $file_params = $_FILES["file"];
        $description = $app->request->data['description'];
        
        if(empty($file_params)) $this->respondJSON(new Error("Your image didn't upload. Please try again."),true);
        
        if ($file_params['size'] == 0 || $file_params['size']/1024 > (5*1024))
        {
            $this->respondJSON(new Error("Your image is too large. Try to keep it under 5MB."),true);
        }
        else 
        {
            $uploaddir = APP_PATH ."uploads/";
            $uploaddir = $uploaddir . $app->user->getId();
            
            if(!is_dir($uploaddir)) mkdir($uploaddir, 0777);
            
            $filename = $uploaddir ."/". basename($_FILES['file']['name']);
            
            $ct = 1;
            while(file_exists($filename) && $ct < 10){
                $dir = substr($filename,0,strrpos($filename, "/")+1);
                $file = substr($filename,strrpos($filename, "/")+1);
                $file = explode(".",$file);
                
                if(preg_match("/\[\d+\]/", $file[0])) {
                    $file[0] = preg_replace("/\[\d+\]/","[". $ct ."]", $file[0]);
                }
                else {
                    $file[0] .= "[$ct]";
                }
                
                $ct++;
                $filename = $dir . implode(".", $file);
            }
            
            if($ct == 10) {
                $this->respondJSON(new Error("Please rename your file prior to uploading."), true);
            }
            
            if(move_uploaded_file($_FILES['file']['tmp_name'], $filename) && S3Service::push($filename, S3_USER_FILE_DIR ."/". $app->user->getId(), "p")) {
                    
                $f = new UserFile();
                $f->setUserId($user->getId());
                $f->setName(substr($filename,strrpos($filename, "/")+1));
                $f->setPath($filename);
                $f->setDescription($description);
                if(!$f->save()) {
                    unlink($filename);
                    $this->respondJSON(new Error("There was an unexpected error saving your file."));
                }
                
                unlink($filename);//No need to keep the file since it's on s3
                
                $this->respondJSON(SUCCESS,true);
            }
            else {
                $this->respondJSON(new Error("This file cannot be processed. Please do not upload executable code or scripts."),true);
            }
        }
        
    }

    public function post_delete($file_id) {
        $app = App::init();
        
        $file_id = filter_var($file_id, FILTER_VALIDATE_INT);
        
        $success = false;
        
        if($file_id != false) {
                
            $c = UserFile::load($file_id);
            
            if(!$c) {
                    
                $this->respondJSON(new Error("Invalid File"));
                exit;
                
            } else if(!$app->authObject($c, PERMISSION_DELETE)) {
                
                $this->respondJSON(new Error("Permission Denied"));
                exit;
                
            } else {
                
                $success = $c->delete();
                
                $this->respondJSON($success);
                
            }
            
        } else {
            
            $this->respondJSON(new Error("Invalid File"));
            exit;
        }
    }
}
