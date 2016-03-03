<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_GROUPTASK'];
require_once("inc/header.inc.php");

if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
} else {
    if(isset($_GET['gid'])){
        $user = Authentication_Controller::GetAuthenticatedUser();
        $group = new Group;
        if($group->AuthenticationGroup($user, $_GET['gid'])){
            $group = $group->GetGroupById($_GET['gid']);
            $users = $group->getUsers();
            $tasks = $group->getAllTasks();

            if(isset($_GET['date']) && isset($_GET['uid']) && isset($_GET['tid'])){
                if($group->checkUser($_GET['uid']))
                {
                    if($_GET['date'] == date('Y-m-d', strtotime('last Monday -1 week')) || $_GET['date'] == date('Y-m-d', strtotime('last Monday + 0 week')) || $_GET['date'] == date('Y-m-d', strtotime('last Monday + 1 week')))
                    {
                        //$DBObject->GetUserTask($_GET['uid'],$_GET['tid'],$_GET['date']);
                        $userTask = new User_Task($_GET['uid'],$_GET['tid'],$_GET['date'],1);
                        $DBObject->SetUserTask($userTask,$group,$user->id);
                    }
                    header ('Location: '.$settings['site_url'].'tasks/'.$group->id);
                }
            }

            if(isset($_GET['action']) && $_GET['action'] == "emailreminder"){
                $settingsId = isset($_GET['sid']) ? $_GET['sid'] : 0;
                $setting = Setting::forGroupUser($settingsId, 'etr', 1, $_GET['gid'],$user->id);
                $DBObject->SetEmailTaskReminderSetting($setting);
                header ('Location: '.$settings['site_url'].'tasks/'.$group->id);
            }
            if($group->CheckTask()) {

            echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">';
            if(isset($_GET['task'])){
                echo '<div class="success-bar">'.$lang['TASK_SUCCESFULLY_ADDED'].'</div>';
            }
            echo '
        <h1>'.$lang['TASKLIST'].'</h1>
                
        <p><a href="'.$settings['site_url'].'group/'.$group->id.'/">&larr; '.$lang['BACK_TO_GROUPPAGE'].'</a></p> ';
        if($tasks != null)
        {
            echo '<div class="double-scroll">
            <table>
                <tr><th></th>';

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
                    echo ' <td class="date onepx">'.$lang['THIS_WEEK'].'</td>';
                    $usertasks = $group->getUserTasksByWeek($mondaydate);
                }
                if ($w >= 2) {
                    echo '<td class="date onepx">'.$lang['WEEK'] . date('W, d-m', strtotime('last Monday + ' . ($w-1) . ' week')) . '</td>';
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
                        echo '<td class="done"><a href="'.$settings['site_url'].'tasks.php?gid='.$group->id.'&tid='.$usertasks[$u->id]->id.'&date='.$mondaydate.'&uid='.$u->id.'" title="'.$usertasks[$u->id]->description.'" name="'.$group->id.'|'.$usertasks[$u->id]->id.'|'.$mondaydate.'|'.$u->id.'">' . $usertasks[$u->id]->name . '</a></td>';
                        $t++;
                    }
                    else
                    {
                        if( isset($tasksForOverview[$taskNumber]) &&
                            $tasksForOverview[$taskNumber] != null) // controleer of er een taak is voor de persoon / week
                        {
                            if($w==0 || $w==1)
                            {
                                echo '<td class="not-done"><a href="'.$settings['site_url'].'tasks.php?gid='.$group->id.'&tid='.$tasksForOverview[$taskNumber]->id.'&date='.$mondaydate.'&uid='.$u->id.'" title="'.$tasksForOverview[$taskNumber]->description.'" name="'.$group->id.'|'.$tasksForOverview[$taskNumber]->id.'|'.$mondaydate.'|'.$u->id.'"
                                >' . $tasksForOverview[$taskNumber]->name . '</a></td>';
                            }
                            elseif($w==2)
                            {
                                echo '<td><a href="'.$settings['site_url'].'tasks.php?gid='.$group->id.'&tid='.$tasksForOverview[$taskNumber]->id.'&date='.$mondaydate.'&uid='.$u->id.'" title="'.$tasksForOverview[$taskNumber]->description.'" name="'.$group->id.'|'.$tasksForOverview[$taskNumber]->id.'|'.$mondaydate.'|'.$u->id.'">' . $tasksForOverview[$taskNumber]->name . '</a></td>';
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
            echo '<tr><td>'.$lang['REMINDER'].':</td>';
            $emailReminders = $DBObject->GetEmailSettingsByUserGroup($group->id);
            foreach ($users as $u) {
                if($u->id != $user->id) //check of het de ingelogde gebruiker is
                {
                    echo '<td>'.((isset($emailReminders[$u->id])) ? $lang['ON'] : $lang['OFF']).'</td>';
                    continue; // naar volgende in foreach
                }
                if(isset($emailReminders[$u->id]))
                {
                    echo '<td><a href="'.$settings['site_url'].'tasks.php?gid='.$group->id.'&sid='.$emailReminders[$u->id]->id.'&action=emailreminder" title="'.$lang['TURN_OFF_EMAIL_REMINDER'].'">'.$lang['ON'].'</a></td>';
                }
                else
                {
                    echo '<td><a href="'.$settings['site_url'].'tasks.php?gid='.$group->id.'&action=emailreminder" title="'.$lang['TURN_ON_EMAIL_REMINDER'].'">'.$lang['OFF'].'</a></td>';
                }
            }

            echo'</tr>';
            echo '</table></div>';
        }
        else
        {
           echo '<p>'.$lang['MAKE_NEW_TASK_FIRST'].'</p>';
        }

        echo '<p>'.$lang['REMINDER_INFO'].'</p>

    </div>

    <aside class="l-box pure-u-1-4">
        <a class="buttonExtra" href="'.$settings['site_url'].'task-history/'.$group->id.'/">'.$lang['HISTORY'].'</a>
        <a class="buttonExtra" href="javascript:if(window.print)window.print()">'.$lang['PRINT_TASKS'].'</a>
        <a class="buttonExtra" href="'.$settings['site_url'].'task-ical/'.$group->id.'/">'.$lang['LINK_TASKLIST'].'</a>
		<div class="clear"></div>
        <h2>'.$lang['TASKS'].'</h2>
        <div class="box">
            <p>'.$lang['CLICK_TO_CHANGE_TASK'].'</p>
            <ul class="small">';
                if($tasks != null)
                {
                    foreach($tasks as $task)
                    {
                        echo '<li><a href="'.$settings['site_url'].'edit-task/'.$group->id.'/'.$task->id.'" title="'.$task->description.'">'.$task->name.'</a></li>';
                    }
                }
                else
                {
                    echo '<li>'.$lang['NO_TASKS'].'</li>';
                }

            echo '</ul>
            <a href='.$settings['site_url'].'add-task/'.$group->id.' class="btn btn-white">'.$lang['NEW_TASK'].'</a>
        </div>';
                $group->ShowSideBarStickyNotes();
                $group->ShowSideBarSettings();
                echo '		</aside>';
				echo '	</div>';
				echo '</div>';

            }
            else{
                echo '<div class="notification-bar">'.$lang['ERROR_TASK_NOTACTIVE'].' <a href="'.$settings['site_url'].'settings-group/'.$group->id.'/">'.$lang['CLICKHERE'].'</a>'.$lang['EDIT_GROUP_MODULES'].'<br></div>';
            }
        }
        else{
            header ('Location: '.$settings['site_url']);
        }
    }
    else {
        header ('Location: '.$settings['site_url']);
    }
}
require_once("inc/footer.inc.php");
?>