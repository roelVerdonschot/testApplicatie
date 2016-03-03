<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_DINNER'];

$showform = false;
if (!Authentication_Controller::IsAuthenticated()) {
    header('Location: ' . $settings['site_url'] . 'login');
    $showform = false;
}
elseif($_GET['gid']){
    $user = Authentication_Controller::GetAuthenticatedUser();
    if ($DBObject->AuthenticationGroup($user->id, $_GET['gid'])) {
        $group = new Group();
        $group = $group->GetGroupById($_GET['gid']);
        // gets the users from the selected group
        $users = $group->getUsers();
        $showform = true;
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

require_once("inc/header.inc.php");

if ($showform) {
    if ($group->CheckDinner()) {
        echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">';
        $dinner = new Dinner(null,null,null,null,null,null);
        $weeks = (isset($_GET['p']) ? $_GET['p'] : 1);
        $weeks = ($weeks <= 0 ? 1 : $weeks);
        echo '<h1>'.$lang['MENU_DINNER_LIST'].'</h1>
        <p><a href="'.$settings['site_url'].'group/'.$group->id.'/">&larr; '.$lang['BACK_TO_GROUPPAGE'].'</a></p>';
        echo '<input type="button" class="alt_btn" value="Vorige week" onclick="window.location.href=\''.$settings['site_url'].'dinner-history/'.$group->id.'/'.($weeks +1).'/\'"/> ';
        echo '<input type="button" class="alt_btn" value="Volgende week" onclick="window.location.href=\''.$settings['site_url'].'dinner-history/'.$group->id.'/'.($weeks -1).'/\'"/><br /><br />';
        echo '<div class="double-scroll">';

        $set = $group->GetSetting('tbd',$user->id);
        if($set != null){
            if($set->value == 'top'){
                $dinner->ShowDinnerHistoryTableTop($users,$group,$weeks);
            }
            else{
                $dinner->ShowDinnerHistoryTableLeft($users,$group,$weeks);
            }
        }
        else {
            if(count($users) > 8)
            {
                $dinner->ShowDinnerHistoryTableLeft($users,$group,$weeks);
            }
            else
            {
                $dinner->ShowDinnerHistoryTableTop($users,$group,$weeks);
            }
        }
        echo '</div>';
        echo '</div>
        <aside class="l-box pure-u-1-4">';
        echo '<div class="clear"></div>';
        $group->ShowSideBarStickyNotes();
        $group->ShowSideBarSettings();
        $group->ShowSideBarLegend();
        echo '		</aside>';
        echo '	</div>';
        echo '</div>';
    } else {
        echo '<div class="notification-bar">'.$lang['ERROR_DINNER_NOTACTIVE'].'<a href="'.$settings['site_url'].'settings-group/' . $group->id . '/">'.$lang['CLICKHERE'].'</a>'.$lang['EDIT_GROUP_MODULES'].'<br></div>';
    }
} else {
    header('Location: ' . $settings['site_url']);
}

include_once("inc/footer.inc.php");
?>