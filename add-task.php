<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_ADDTASK'];
require_once("inc/header.inc.php");

$showform = false;
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
    $showform = false;
}

if(isset($_GET['gid'])){
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group;
    if($group->AuthenticationGroup($user, $_GET['gid'])){
        $showform = true;
        $group = $group->GetGroupById($_GET['gid']);

        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
            $showform = true;
        } else {
            $showform = false;
            $name = trim(strip_tags($_POST["name"]));
            if(empty($name))
            {
                $error = $lang['ERROR_FILL_IN_TASK'];
                $showform = true;
            }

            if($showform == false)
            {
                $description = trim(strip_tags($_POST["description"]));
                $DBObject->AddTask($name,$description,$group->id);
                // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                $lb = new Logbook(null, $group->id, $user->id, null, null, null, null, 'TA', $name);
                $DBObject->AddLogbookItem($lb);

                header('Location: '.$settings['site_url'].'tasks/'.$group->id.'/added');
            }
        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

if($showform){
    echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">
    <h1>'.$lang['NEW_TASK'].'</h1>
        '.(isset($error) ? '<div class="error-bar">'.$error.'</div>' : '').'

	    <form method="post" action="">
	    <span>
            <label>'.$lang['TASK_NAME'].'</label>
            <input type="text" name="name" value="" tabindex="1">
	    </span>
	    <span class="clear">
	        <label>'.$lang['DESCRIPTION'].'</label>
	        <input type="text" name="description" value="" tabindex="2">
	    </span>
	    <span class="clear">
            <input type="submit" class="alt_btn" value="'.$lang['SAVE'].'" tabindex="3">
            <input type="button" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'tasks/'.$group->id.'\'" tabindex="4"/>
		</span>
		</div>';

    echo '<aside class="l-box pure-u-1-4">';
    $group->ShowSideBarStickyNotes();
    $group->ShowSideBarSettings();
    echo '		</aside>';
	echo '	</div>';
	echo '</div>';
}
else{
     header ('Location: '.$settings['site_url']);
}

include_once("inc/footer.inc.php");
?>