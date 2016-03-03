<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractNonLoginString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  email
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    if($DBObject->ResetPassword($result[0])){
        echo json_encode(true);
    }
    else{
        echo json_encode(false);
    }
}
?>