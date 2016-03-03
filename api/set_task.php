<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == group ID | [2] == user_task object
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group;
    if($group->AuthenticationGroup($result[0], $result[1])){
        $userTask = new User_Task($result[3],$result[2],$result[4],$result[5]);
        $group = $group->GetGroupById($result[1]);
        $DBObject->SetUserTask($userTask,$group,$result[0]->id);
        echo json_encode(true);
    }
    else{
        echo json_encode(false);
    }
}
?>