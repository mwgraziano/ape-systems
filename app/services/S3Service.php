<?php

class S3Service extends Service {
    
    
    public static function push($filepath, $push_dir, $perms="r")
    {
        $filename = basename($filepath);
        
        if($perms == "r") $perms = S3::ACL_PUBLIC_READ;
        else $perms = S3::ACL_PRIVATE;
        
        try {
        
            $s3 = new S3(S3_ACCESS_KEY, S3_ACCESS_SECRET);
            
            return $s3->putObjectFile($filepath, S3_BUCKET, $push_dir.'/'.$filename, $perms);
            
        } catch (Exception $e) {
            
            return false;
            
        }
    }
    
    public static function deleteFile($filepath) {
        
        $s3 = new S3(S3_ACCESS_KEY, S3_ACCESS_SECRET);
        return $s3->deleteObject(S3_BUCKET, $filepath);
        
    }
    
    public static function getfile($filepath) {
        
        try {
            
            $s3 = new S3(S3_ACCESS_KEY, S3_ACCESS_SECRET);
        
            if($s3->getObjectInfo(S3_BUCKET, $filepath, false)) {
                $o = $s3->getObject(S3_BUCKET, $filepath);
                
                return $o;
            }
            
            return false;
            
        } catch (Exception $e) {
            
            return false;
            
        }
        
    }
}
