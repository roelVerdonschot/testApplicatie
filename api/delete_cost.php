<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId | [2] == cost id
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group;
    $cost = new Cost();
    if($group->AuthenticationGroup($result[0], $result[1])){
        if($cost->DeleteCostById($result[2])){
            $group = $group->GetGroupById($result[1]);
            $DBObject->UpdateGroupUserAvgCooked($group);
            $DBObject->UpdateGroupUserSaldo($group->id);

            // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
            $lb = new Logbook(null, $result[1], $result[0]->id, null, $result[2], null, null, 'CD', null);
            $DBObject->AddLogbookItem($lb);

            //All Users
            $usersOutput = array();
            $users = $group->getUsers();
            if(isset($users))
            {
                foreach( $users as $u ) {
                    $usersOutput[] = new UserData($u->id, $u->firstName, $DBObject->GetUserSaldo($group->id,$u->id), $u->preferredLanguage);
                }
            }

            echo json_encode($usersOutput);
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