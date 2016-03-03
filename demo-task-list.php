<?php
/**
 * User: roel
 * Date: 16-10-13
 * Time: 15:22
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_GROUPTASK'];
$_page_description = $lang['DESCRIPTION_DEMO_TASK'];
$_page_keywords = $lang['KEYWORDS_DEMO_TASK'];
$_demo = true;
require_once("inc/header.inc.php");

$group = new Group;
$group = $group->GetGroupById($settings['DEFAULT_DEMO_GROUP']);

$users = $group->getUsers();
$tasks = $group->getAllTasks();

if(isset($_GET['date']) && isset($_GET['uid']) && isset($_GET['tid'])){
    if($group->checkUser($_GET['uid']))
    {
        if($_GET['date'] == date('Y-m-d', strtotime('last Monday -1 week')) || $_GET['date'] == date('Y-m-d', strtotime('last Monday + 0 week')) || $_GET['date'] == date('Y-m-d', strtotime('last Monday + 1 week')))
        {
            //$DBObject->GetUserTask($_GET['uid'],$_GET['tid'],$_GET['date']);
            $userTask = new User_Task($_GET['uid'],$_GET['tid'],$_GET['date'],1);
            $DBObject->SetUserTask($userTask,$group,$settings['DEFAULT_DEMO_USER']);
        }
        header ('Location: '.$settings['site_url'].$lang['_LANG_CODE'].'/demo-task-list');
    }
}

echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">';
if(isset($_GET['task']) || isset($_POST['stickynote-title']) || isset($_POST['stickynote-message'])){
    echo '<div class="notification-bar">'.$lang['DEMO_STICKYNOTES'].'</div>';
}
       echo '<h1>'.$lang['DEMO'].' '.$lang['TASKLIST'].'</h1>';

    if($tasks != null)
    {
        echo '<div class="double-scroll">
            <table>
                <tr><th class="onepx"></th>';

        foreach ($users as $u) {
            echo '<th>' . $u->firstName . '</th>';
        }
        echo '</tr>';
        $verschil = (count($users) - count($tasks));
        $tasksForOverview = $tasks;
        if($verschil > 0)
        {
            $tasksForOverview= array_merge($tasks,array_fill(0,$verschil,null));
        }
        for ($w = 0; $w < 25; $w++) {
            if ($w % 2)
            {
                echo "<tr>";
            }
            else
            {
                echo '<tr class="alt">';
            }
            $usertasks = null;
            $weeknumber = date('W', strtotime('last Monday + ' . ($w-1) . ' week'));
            $mondaydate = date('Y-m-d', strtotime('last Monday + ' . ($w-1) . ' week'));

            if ($w == 0) {
                echo ' <td class="date">'.$lang['LAST_WEEK'].'</td>';
                $weeknumber = date('W', strtotime('last Monday - 1 week'));
                $mondaydate = date('Y-m-d', strtotime('last Monday -1 week'));
                $usertasks = $group->getUserTasksByWeek($mondaydate);
            }
            if ($w == 1) {
                echo ' <td class="date">'.$lang['THIS_WEEK'].'</td>';
                $usertasks = $group->getUserTasksByWeek($mondaydate);
            }
            if ($w >= 2) {
                echo '<td class="date">'.$lang['WEEK'] . date('W, d-m', strtotime('last Monday + ' . ($w-1) . ' week')) . '</td>';
            }

            if ($w == 2)
            {
                $usertasks = $group->getUserTasksByWeek($mondaydate);
            }

            $i = 0;
            $t = 0;
            foreach ($users as $u) {
                $taskNumber = ($weeknumber+$i) % count($tasksForOverview); // THIS IS NOT THE TASKID!

                if(isset($usertasks[$u->id]))
                {
                    echo '<td class="done"><a href="?tid='.$usertasks[$u->id]->id.'&date='.$mondaydate.'&uid='.$u->id.'">' . $usertasks[$u->id]->name . '</a></td>';
                    $t++;
                }
                else
                {
                    if( isset($tasksForOverview[$taskNumber]) &&
                        $tasksForOverview[$taskNumber] != null) // controleer of er een taak is voor de persoon / week
                    {
                        if($w==0 || $w==1)
                        {
                            echo '<td class="not-done"><a href="?tid='.$tasksForOverview[$taskNumber]->id.'&date='.$mondaydate.'&uid='.$u->id.'">' . $tasksForOverview[$taskNumber]->name . '</a></td>';
                        }
                        elseif($w==2)
                        {
                            echo '<td><a href="?tid='.$tasksForOverview[$taskNumber]->id.'&date='.$mondaydate.'&uid='.$u->id.'">' . $tasksForOverview[$taskNumber]->name . '</a></td>';
                        }
                        else
                        {
                            echo '<td>' . $tasksForOverview[$taskNumber]->name . '</td>';
                        }
                    }
                    else
                    {
                        echo '<td>'.'</td>';
                    }
                }
                $i++;
            }
            echo '</tr>';
        }
        echo '</table></div>';
    }
    else
    {
        echo '<p>'.$lang['MAKE_NEW_TASK_FIRST'].'</p>';
    }

    //echo '<p>'.$lang['REMINDER_INFO'].'</p>';
    echo '
    </div>

    <aside class="l-box pure-u-1-4">
    <a class="buttonExtra" href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-task-list/demo">'.$lang['HISTORY'].'</a>
	<div class="clear"></div>
        <h2>'.$lang['TASKS'].'</h2>
        <div class="box">
            <p>'.$lang['CLICK_TO_CHANGE_TASK'].'</p>
            <ul class="small">';
    if($tasks != null)
    {
        foreach($tasks as $task)
        {
            echo '<li><a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-task-list/demo">'.$task->name.'</a></li>';
        }
    }
    else
    {
        echo '<li>'.$lang['NO_TASKS'].'</li>';
    }

    echo '</ul>
            <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-task-list/demo" class="btn btn-white">'.$lang['NEW_TASK'].'</a>
        </div>';
    $group->ShowSideBarDemoStickyNotes();
    echo '<div class="box">
                <p>'.$lang['MAKE_NEW_ACCOUNT'].'</P>
                <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/register" class="btn btn-white">'.$lang['REGISTER_NOW'].'</a>
            </div>
            </aside>';
	echo '	</div>';
	echo '</div>';
require_once("inc/footer.inc.php");
?>