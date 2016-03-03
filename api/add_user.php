<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == email (array) | [2] groupId
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group();
    if($group->AuthenticationGroup($result[0], $result[2])){
        $group = $DBObject->GetGroupById($result[2]);
        foreach($result[1] as $email){
            if(!empty($email)){
                if($DBObject->CheckInvitedUserGroup($email, $result[2])){
                    Email_Handler::mailBodyInviteToGroup($email,$lang['GROUP_INVITE'],$result[0]->firstName,$group->name,$group->id);
                }
                else{
                    $group->InviteUserToGroup($email,$group->id,$result[0]->id);
                    Email_Handler::mailBodyInviteToGroup($email,$lang['GROUP_INVITE'],$result[0]->firstName,$group->name,$group->id);
                    // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                    $lb = new Logbook(null, $group->id, $result[0]->id, null, null, null, null, 'GUA', $email);
                    $DBObject->AddLogbookItem($lb);
                }
                echo json_encode(true);
            }
        }
    }
}
?>

