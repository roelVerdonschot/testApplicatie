<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_LINK_TASKLIST'];
require_once("inc/header.inc.php");
$showform = false;
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
    $showform = false;
}
echo '<div class="normal-content">
	<div class="pure-g">';
if(isset($_GET['gid'])){
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group;
    if($group->AuthenticationGroup($user, $_GET['gid'])){
        $group = $group->GetGroupById($_GET['gid']);
        $showform = true;
        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
            $error = false;

            if(!is_numeric($_POST['dayOfWeek'])){
                $error = true;
            }
            if($_POST['dayOfWeek'] < 0 || $_POST['dayOfWeek'] > 7){
                $error = true;
            }
            if($error === false)
            {
                $unicKey = hash('sha256',$_POST['dayOfWeek'].$user->id.$group->id.uniqid());
                $group->setTaskIcal($user->id,$_POST['dayOfWeek'],$unicKey);
            }
            else
            {
                echo '<div class="error-bar">Voer een geldige dag in<br></div>';
            }
        }
        $taskIcal = $group->getTaskIcal($user->id); // [0] = dayOfWeek, [1] = uniq key
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

if($showform){
    echo '		<div class="l-box pure-u-3-4">
        <h1>'.$lang['TASKLIST_LINK_CALENDAR'].'</h1>
        <form method="post" action="">
        <span class="clear">
	        <label>'.$lang['TASKLIST_LINK_CHOOSE_DAY'].':</label> '.dayOfWeekList('dayOfWeek',$taskIcal[0]).'
	    </span>
        <span class="clear">
            <input type="submit" class="alt_btn" value="'.$lang['GENERATE_LINK'].'" tabindex="2">
            <input type="button" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'tasks/'.$_GET['gid'].'\'" tabindex="3"/>
        </span>
        </form>
        <h2 class="clear">'.$lang['PERSONAL_LINK'].'</h2>
        <p>
        '.$lang['TASKLIST_LINK_DESCRIPTION'].'<br />';
    if($taskIcal[1]!=null)
    {
		echo $settings['site_url'].'tasklist-ical-feed.php?ukey='.$taskIcal[1];
    }
    echo '
        </p>';
    /*
        <span class="clear">
            <label>'.$lang['IS_DINNER'].': </label>'.yesNoArray('is_dinner', $cost->isDinner).'
        </span>
     */
	 echo '		</div>';
    echo '<aside class="l-box pure-u-1-4">';
    $group->ShowSideBarStickyNotes();
    $group->ShowSideBarSettings();
    echo '</aside>';
}
else{
    header ('Location: '.$settings['site_url'].'cost/'.$_GET['gid'].'/');
}
echo '	</div>';
echo '</div>';

include_once("inc/footer.inc.php");
?>