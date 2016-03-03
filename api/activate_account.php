<?php
require_once("classes/config.mobile.inc.php");


$result = API_controller::extractNonLoginString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  activationcode
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $user = new User();
    $output = $user->GetEmailByAc($result[0]); // TODO: NOT WORKING ANYMORE, use GetUserByAC

    // if the activationcode is not found in the database this page redirects to login
    if ($user->ActivateUser($result[0])) { // TODO: NOT WORKING ANYMORE! use ActivateUser();
        Email_Handler::activationConfirmation($output[0],$lang['ACTIVATION'].' Monipal', $output[1].' '.$output[2]);
        echo json_encode(true);
    }
    else{
        echo json_encode(false);
    }
}
?>