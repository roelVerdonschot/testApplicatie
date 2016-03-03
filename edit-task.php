<?php
ob_start();
require_once("inc/config.inc.php");

$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_EDITTASK'];
require_once("inc/header.inc.php");

if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
} else {
    if(isset($_GET['tid'])){
        $user = Authentication_Controller::GetAuthenticatedUser();
        $task = $DBObject->GetTaskByID($_GET['tid']);
        if($task == null)
        {
            $showForm = false;
            header ('Location: '.$settings['site_url']);
        }
        $group = new Group;
        if($group->AuthenticationGroup($user, $task->groupId)){
            $group = $group->GetGroupById($task->groupId);
            $showform = true;

            if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
                $showForm = true;
            }
            else {
                $showForm = false;
                $user = Authentication_Controller::GetAuthenticatedUser();
                $oldTaskName = $task->name;
                $task->setName($_POST['name']);
                $task->setDescription($_POST['description']);
                $DBObject->EditTask($task);

                $lb = new Logbook(null, $task->groupId, $user->id, $_GET['tid'], null, null, null, 'TE', $oldTaskName);
                $DBObject->AddLogbookItem($lb);
                header('Location: '.$settings['site_url'].'tasks/'.$task->groupId);

            }
        }
		else{
            header ('Location: '.$settings['site_url']);
        }

    }
    else{
        header ('Location: '.$settings['site_url']);
    }
    if ($showForm) {
        echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">
        <h1>'.$lang['TASK'].': '.$task->name.'</h1>
	    <p>'.$lang['CHANGE_TASK_DATA'].'</p>

	    <form method="post" action="">

	    <span>
	        <label>'.$lang['TASK_NAME'].'</label>
	        <input type="text" name="name" value="'.$task->name.'" tabindex="1">
	    </span>
	    <span class="clear">
		    <label>'.$lang['DESCRIPTION'].'</label>
		    <input type="text" name="description"  value="'.$task->description.'" tabindex="2">
		</span>
		<span class="clear">
            <input type="submit" class="alt_btn" value="'.$lang['SAVE'].'" tabindex="3">
            <input type="button" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'\'" tabindex="4"/>
            <input type="button" class ="alt_btn" value="'.$lang['DELETE_TASK'].'" onclick="if (confirm(\''.$lang['ARE_YOU_SURE_DELETE_TASK'].'\'))window.location.href=\''.$settings['site_url'].'delete-task/'.$task->id.'\'" tabindex="5"/>
        </span>
        </div>
        <aside class="l-box pure-u-1-4">';
		$group->ShowSideBarStickyNotes();
		$group->ShowSideBarSettings();
		echo '		</aside>';
		echo '	</div>';
		echo '</div>';
    }
}
include_once("inc/footer.inc.php");
?>