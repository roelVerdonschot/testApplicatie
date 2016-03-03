<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group;
    if($group->AuthenticationGroup($result[0], $result[1])){
        $tasks = $DBObject->GetTasksByGroupId($result[1]);
        $output = array();
        foreach($tasks as $o)
        {
            $task = new TaskData();
            $task->TaskId = $o->id;
            $task->TaskName = $o->name;
            $task->TaskDescription = $o->description;
            $output[] = $task;
        }
        echo json_encode($output);
    }
    else{
        echo json_encode(false);
    }
}
?>

