<?php
require_once("classes/config.mobile.inc.php");


$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] String feedback | [2] String WindowsPhone/IOS/Android
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    if(Email_Handler::mailFeedback($result[0]->email,$result[1],$result[2]))
    {
        Email_Handler::mailFeedbackThanks($result[0]->email);
        echo json_encode(true);
    }
    else{
        echo json_encode(false);
    }
}
?>