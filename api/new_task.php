<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId | [2] == [0] taskName [1] task Description
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group;
    if($group->AuthenticationGroup($result[0], $result[1])){
        $description = trim(strip_tags($result[2][1]));
        $DBObject->AddTask($result[2][0],$description,$result[1]);
        // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
        $lb = new Logbook(null, $result[1], $result[0]->id, null, null, null, null, 'TA', $result[2][0]);
        $DBObject->AddLogbookItem($lb);

        $group = $group->GetGroupById($result[1]);
        $users = $group->getUsers();
        $tasks = $group->getAllTasks();

        $output = array();


        $verschil = (count($users) - count($tasks));
        $tasksForOverview = $tasks;
        if($verschil > 0)
        {
            $tasksForOverview= array_merge($tasks,array_fill(0,$verschil,null));
        }
        for ($w = 0; $w < 5; $w++) {
            $weeklist = new WeekListData();
            $usertasks = null;
            $weeknumber = date('W', strtotime('last Monday + ' . ($w-1) . ' week'));
            $mondaydate = date('Y-m-d', strtotime('last Monday + ' . ($w-1) . ' week'));

            if ($w == 0) {
                $weeklist->setNameOfWeek($lang['LAST_WEEK']);
                $weeknumber = date('W', strtotime('last Monday - 1 week'));
                $mondaydate = date('Y-m-d', strtotime('last Monday -1 week'));
                $usertasks = $group->getUserTasksByWeek($mondaydate);
            }
            if ($w == 1) {
                $weeklist->setNameOfWeek($lang['THIS_WEEK']);
                $usertasks = $group->getUserTasksByWeek($mondaydate);
            }
            if ($w >= 2) {
                $weeklist->setNameOfWeek($lang['WEEK'] . date('W, d-m', strtotime('last Monday + ' . ($w-1) . ' week')));
            }

            if ($w == 2)
            {
                $usertasks = $group->getUserTasksByWeek($mondaydate);
            }
            $weeklist->setDateOfWeek($mondaydate);
            $i = 0;
            $tasklist = array();
            foreach($users as $u){

                $task = new TaskData();
                $taskNumber = ($weeknumber+$i) % count($tasksForOverview); // THIS IS NOT THE TASKID!

                $task->setUserId($u->id);
                $task->setUserName($u->firstName);
                if(isset($usertasks[$u->id]))
                {
                    //task uit database en done

                    $task->setTaskId($usertasks[$u->id]->id);
                    $task->setTaskName($usertasks[$u->id]->name);
                    $task->setIsComplete(true);
                }
                else
                {
                    if( isset($tasksForOverview[$taskNumber]) && $tasksForOverview[$taskNumber] != null) // controleer of er een taak is voor de persoon / week
                    {

                        //$tasksForOverview[$taskNumber]->id
                        $task->setTaskId($tasksForOverview[$taskNumber]->id);//$usertasks[$u->id]->getId);
                        $task->setTaskName($tasksForOverview[$taskNumber]->name); //$usertasks[$u->id]->name);
                        $task->setIsComplete(false);

                    }
                    else
                    {
                        // geen taak voor de gebruiker deze week
                        $task->setTaskId(null);
                        $task->setTaskName(null);
                    }
                }
                $i++;
                $tasklist[] = $task;
            }
            $weeklist->setTaskList($tasklist);
            $output[] = $weeklist;
        }
        echo json_encode($output);
    }
    else{
        echo json_encode(false);
    }
}
?>