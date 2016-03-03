<?php
require_once('../inc/config.inc.php');

$check = true;
if(Authentication_Controller::IsAuthenticated())
{
    $user = Authentication_Controller::GetAuthenticatedUser();
}
if($_POST['gid'] != null && $_POST['date'] != null && $_POST['time'] != null){
    if(!$DBObject->AuthenticationGroup((isset($user) ? $user->id : $settings['DEFAULT_DEMO_USER']), $_POST['gid'])) {
        $check = false;
    }
    if($_POST['time'] == '18:00'){
        $check = false;
    }

    if(!preg_match("/0[1-9]|1[0-9]|2[0-3]:[0-5][0-9]$/", $_POST['time'])){
        $check = false;
    }

    list($yyyy,$mm,$dd) = explode('-',$_POST['date']);
    if (!checkdate($mm,$dd,$yyyy)) {
        $check = false;
    }

    if($check){
        $DBObject->UpdateGroupDinnerTime($_POST['gid'],$_POST['date'],$_POST['time']);
        echo 'true';
    }
    else{
        echo 'false';
    }
}
else{
    echo 'false';
}

?>