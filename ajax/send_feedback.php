<?php
require_once('../inc/config.inc.php');

if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    if(Email_Handler::mailFeedback($_POST['email'],$_POST['message'],$_POST['path']))
    {
        Email_Handler::mailFeedbackThanks($_POST['email']);
        echo 'true';
    }
    else
    {
        echo 'false';
    }

}
else {
    echo 'false:email';
}


?>