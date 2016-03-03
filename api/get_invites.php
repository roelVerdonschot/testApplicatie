<?php
require_once("classes/config.mobile.inc.php");


$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $user = new User();
    if($user->CheckEmail(strtolower($result[0]->email))){
        $idgroups = $DBObject->CheckInvitedUser(strtolower($result[0]->email));
        $invites = array();
        if($idgroups != null){
            foreach($idgroups as $i){
                $name = $DBObject->GetGroupName($i);
                $invite =  new GroupInvite($i,$name);
                $invites[] = $invite;
            }
            echo json_encode($invites);
        }
        else{
            echo json_encode(false);
        }

    }
    else{
        echo json_encode(false);
    }
}
?>