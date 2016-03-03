<?php
/**
 * User: roel
 * Date: 8-10-13
 * Time: 17:44
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_DELETEGROUP'];
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
        $users = $group->getUsers();
        $saldoBool = true;

        $instant = new cost();
        $userIdAmount = $instant->CalculateGroupSaldo($group->id);
        if(isset($userIdAmount)){
            foreach($userIdAmount as $u){
                if($u->amount < -0.05 || $u->amount > 0.05){
                    $saldoBool = false;
                }
            }
        }
        $delete = true;
        if(isset($userIdAmount)){
            if(!$saldoBool){
                $notification = '<div class="error-bar">'.$lang['GROUP_HAS_SALDO'].'<br></div>';
                $delete = false;
            }
        }
        if(isset($_GET['dg']) && $delete == true){
            if($_GET['dg'] == 'yes'){
                foreach($users as $u){
                    if($group->DeleteUserFromGroup($u->id, $group)){
                        $notification = '<div class="notification-bar">'.$lang['GROUP_IS_REMOVED'].'<br></div>';
                        $showform = false;
                    }
                    else{
                        $notification = '<div class="error-bar">'.$lang['GROUP_ISNOT_REMOVED'].'<br></div>';
                    }
                }
            }
        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

if($showform){
    echo '<div class="normal-content"><div class="pure-g">';
    if(isset($notification)) {
        echo $notification;
    }
	echo '<div class="l-box pure-u-3-4">
    <h1>'.$lang['DELETE_GROUP'].'</h1>
    <span class="clear">';
    if($saldoBool)
    {
        echo '<p>'.$lang['DELETE_GROUP_INFO'].'</p><span class="clear"><input type="button" class="secundaire-btn" value="'.$lang['DELETE_GROUP'].'" onclick="if (confirm(\''.$lang['ARE_YOU_SURE_DELETE_GROUP_DEF'].'\'))window.location.href=\''.$settings['site_url'].'delete-group/'.$group->id.'/yes\'" tabindex="1"/></span>';
    }
    else
    {
        echo '<input type="button" class="secundaire-btn" value="'.$lang['PAYOFF'].'" onclick="window.location.href=\''.$settings['site_url'].'checkout/'.$group->id.'\'" tabindex="1"/>';
    }
    echo '
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