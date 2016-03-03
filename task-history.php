<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_GROUPTASKHISTORY'];
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

            /*if(isset($_GET['date']) && isset($_GET['uid']) && isset($_GET['tid'])){
                if($group->checkUser($_GET['uid']))
                {
                    //$DBObject->GetUserTask($_GET['uid'],$_GET['tid'],$_GET['date']);
                    $userTask = new User_Task($_GET['uid'],$_GET['tid'],$_GET['date'],1);
                    $DBObject->SetUserTask($userTask,$group,$user->id);
                    header ('Location: '.$settings['site_url'].'taskshistory.php?gid='.$group->id);
                }
            }*/

            echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">
                <h1>'.$lang['TASK_HISTORY'].'</h1>
                <p><a href="'. $settings['site_url'].'tasks/'.$group->id.'/">&larr; '.$lang['BACK_TO_TASK'].'</a></p>';
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
                    for ($w = 1; $w < 25; $w++) {
                        if ($w % 2)
                        {
                            echo "<tr>";
                        }
                        else
                        {
                            echo '<tr class="alt">';
                        }
                        if ($w == 1) {
                            echo '<td class="date onepx">'.$lang['LAST_WEEK'].'</td>';
                        }
                        if ($w >= 2) {
                            echo '<td class="date onepx">'.$lang['WEEK'] . date('W, d-m', strtotime('last Monday - ' . $w . ' week')) . '</td>';
                        }
                        $weeknumber = date('W', strtotime('last Monday - ' . $w . ' week'));
                        $mondaydate = date('Y-m-d', strtotime('last Monday - ' . $w . ' week'));

                        $usertasks = $group->getUserTasksByWeek($mondaydate);

                        $i = 0;
                        $t = 0;
                        foreach ($users as $u) {
                            $taskNumber = ($weeknumber+$i) % count($tasksForOverview); // THIS IS NOT THE TASKID!

                            if(isset($usertasks[$u->id]))
                            {
                                echo '<td class="done">' . $usertasks[$u->id]->name . '</td>';
                                $t++;
                            }
                            else
                            {
                                if( isset($tasksForOverview[$taskNumber]) &&
                                    $tasksForOverview[$taskNumber] != null) // controleer of er een taak is voor de persoon / week
                                {
                                    echo '<td class="not-done">' . $tasksForOverview[$taskNumber]->name . '</td>';
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
                    echo'</tr>';
                    echo '</table></div>';
                }
                else
                {
                    echo '<p>'.$lang['MAKE_NEW_TASK_FIRST'].'</p>';
                }

            echo '</div>
            <aside class="l-box pure-u-1-4">
                <h2>'.$lang['TASKS'].'</h2>
                <div class="box">
                    <p>'.$lang['CLICK_TO_CHANGE_TASK'].'</p>
                    <ul class="small">';
                        if($tasks != null)
                        {
                            foreach($tasks as $task)
                            {
                                echo '<li><a href="'.$settings['site_url'].'edit-task/'.$group->id.'/'.$task->id.'/">'.$task->name.'</a></li>';
                            }
                        }
                        else
                        {
                            echo '<li>'.$lang['NO_TASKS'].'</li>';
                        }
                    echo '</ul>
                    <a href='.$settings['site_url'].'add-task/'.$group->id.' class="btn btn-white">'.$lang['NEW_TASK'].'</a>
                </div>';
            echo '<aside class="content">';
            $group->ShowSideBarStickyNotes();
            $group->ShowSideBarSettings();
            echo '		</aside>';
			echo '	</div>';
			echo '</div>';

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