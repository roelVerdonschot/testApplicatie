<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId | [2] == task id
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group;
    if($group->AuthenticationGroup($result[0], $result[1])){
        $task = $DBObject->GetTaskByID($result[2]);
        if($task->deleteTask())
        {
            // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
            $lb = new Logbook(null, $result[1], $user->id, null, null, null, null, 'TD', $task->name);
            $DBObject->AddLogbookItem($lb);
            echo json_encode(true);
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