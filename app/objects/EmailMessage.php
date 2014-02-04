<?php

class EmailMessage
{
    /*
     * @param User $user
     * @param string $url The url for resetting the password
     */
    public static function buildForgotAuth(User $user, $url)
    {
        
        $name = $user->getFirstName();
        $email = $user->getEmail();
        
        $msg = <<<END
        
Hi $name,

We're sorry you're having trouble with your Ape System login information. Your username for the site is the email address we sent this message to ($email). 

If you can't recall your password, click the link below to enter a new one. This link will expire after 24 hours.

$url

Thank you,

The Ape System Staff
        
END;
        
        return $msg;
    }
    
    public static function buildPlatformReg(User $user) {
        $name = $user->getFirstName();
        $email = $user->getEmail();
        
        $msg = <<<END
        
Hi $name,

You have successfully registered with Ape System - congratulations! 

Remember, your username for the Ape System dashboard is your email address: $email

You can log in at www.ape-system.com.

Thank you for taking the time to register with us,

The Ape System Staff
        
END;
        
        return $msg;
    }
    

}
?>
