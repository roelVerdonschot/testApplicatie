<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
//header('Content-type: text/calendar; charset=utf-8');
//header('Content-Disposition: attachment; filename=mytasklist-monipal.ics');

if(isset($_GET['ukey'])) {
    $data = $DBObject->getIcalInfoByKey($_GET['ukey']); // [0]id user. [1] groupid, [2] day of week
    if($data != null)
    {
        $group = new Group;
        $user = new User;
        $user->setId($data[0]);
        if($group->AuthenticationGroup($user, $data[1])){
            $group = $group->GetGroupById($data[1]);
            $users = $group->getUsers();
            $tasks = $group->getAllTasks();

            $output = "BEGIN:VCALENDAR
METHOD:PUBLISH
VERSION:2.0
PRODID:-//Monipal//Tasklist//EN\r\n";;
            if (count($tasks) > 0) {
                $verschil = (count($users) - count($tasks));
                $tasksForOverview = $tasks;
                if ($verschil > 0) {
                    $tasksForOverview = array_merge($tasks, array_fill(0, $verschil, null));
                }
                for ($w = 0; $w < 25; $w++) {
                    $weeknumber = date('W', strtotime('last Monday + ' . ($w-1) . ' week'));
                    $mondaydate = date('Y-m-d', strtotime('last Monday + ' . ($w-1) . ' week'));

                    if ($w == 0) {
                        $weeknumber = date('W', strtotime('last Monday - 1 week'));
                        $mondaydate = date('Y-m-d', strtotime('last Monday -1 week'));
                    }

                    $i = 0;
                    foreach ($users as $u) {
                        if ($u->id == $user->id) {
                            $taskNumber = ($weeknumber + $i) % count($tasksForOverview); // THIS IS NOT THE TASKID!
                            if(isset($tasksForOverview[$taskNumber]))
                            {
                                $output .=
'BEGIN:VEVENT
SUMMARY:'.$tasksForOverview[$taskNumber]->name.'
UID:'.uniqid().'
STATUS:CONFIRMED
DTSTART;VALUE=DATE:' . date('Ymd', strtotime($mondaydate.' + '.($data[2]==0?'6':$data[2]-1).' days')) . '
DTEND;VALUE=DATE:' . date('Ymd', strtotime($mondaydate.' + '.($data[2]==0?'7':$data[2]).' days')) . '
LOCATION:'.$group->name.'
END:VEVENT'."\r\n";
                            }
                        }
                        $i++;
                    }

                }
            }
            // close calendar
            $output .= "END:VCALENDAR";
            echo $output;
        }
    }
}
?>