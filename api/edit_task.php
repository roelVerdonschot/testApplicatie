<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId | [2] == [0] task id [1] task name [2] task description
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group;
    if($group->AuthenticationGroup($result[0], $result[1])){
        $task = $DBObject->GetTaskByID($result[2][0]);
        $oldTaskName = $task->name;
        $task->setName($result[2][1]);
        $task->setDescription($result[2][2]);
        $DBObject->EditTask($task);

        $lb = new Logbook(null, $task->groupId, $result[0]->id, $result[2][0], null, null, null, 'TE', $oldTaskName);
        $DBObject->AddLogbookItem($lb);
    }
    else{
        echo json_encode(false);
    }
}
?>
