<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // // [0] ==  USER object of ERROR | [1] == group ID
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group();
    if($group->AuthenticationGroup($result[0], $result[1])){
        $group = $group->GetGroupById((int)$result[1]);

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
?>