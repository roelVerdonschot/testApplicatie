<?php
/**
 * User: Roel Verdonschot
 * Date: 19-8-13
 * Time: 16:01
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_DELETEUSER'];
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
        $deleteUser = true;
        $group = $group->GetGroupById($_GET['gid']);

        if(isset($_GET['uid'])){
            if($group->checkUser($_GET['uid'])){
                $instant = new cost();
                $userIdAmount = $instant->CalculateGroupSaldo($group->id);
                if(!isset($userIdAmount)){
                    if($group->DeleteUserFromGroup($_GET['uid'], $group)){
                        // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                        $lb = new Logbook(null, $group->id, $user->id, null, null, null, null, 'GUD', $DBObject->GetUserNameById($_GET['uid']));
                        $DBObject->AddLogbookItem($lb);
                        $notification = '<div class="l-box pure-u-1 notification-bar">'.$lang['USER_IS_REMOVED'].'<br></div>';
                    }
                    else{
                        $notification = '<div class="l-box pure-u-1 error-bar">'.$lang['USER_ISNOT_REMOVED'].'<br></div>';
                    }
                }
                else{
                    foreach($userIdAmount as $u){
                        if($u->id == $_GET['uid']){
                            if($u->amount > -0.05 && $u->amount < 0.05){
                                if($group->DeleteUserFromGroup($_GET['uid'], $group)){
                                    // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                                    $lb = new Logbook(null, $group->id, $user->id, null, null, null, null, 'GUD', $DBObject->GetUserNameById($_GET['uid']));
                                    $DBObject->AddLogbookItem($lb);
                                    $notification = '<div class="l-box pure-u-1 notification-bar">'.$lang['USER_IS_REMOVED'].'<br></div>';
                                }
                                else{
                                    $notification = '<div class="l-box pure-u-1 error-bar">'.$lang['USER_ISNOT_REMOVED'].'<br></div>';
                                }
                            }
                            else{
                                $notification =  '<div class="l-box pure-u-1 error-bar">'.$lang['USER_HAS_SALDO'].'<br></div>';
                            }
                        }
                    }
                }
            }
            else{
                $notification = '<div class="l-box pure-u-1 error-bar">'.$lang['USER_ISNOT_REMOVED'].'<br></div>';
            }
        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

if($showform){
    $link = '<a href="'.$settings['site_url'].'checkout/'.$group->id.'/">'.$lang['DELETE_USER_INFO_2'].'</a>';
    echo '<div class="normal-content">
	<div class="pure-g">';
	if(isset($notification))
	{
		echo $notification;
	}
	echo '
		<div class="l-box pure-u-3-4">
    <h1>'.$lang['DELETE_USER'].'</h1>
    <a href="'.$settings['site_url'].'group/'.$group->id.'/">&larr; '.$lang['BACK_TO_GROUPPAGE'].'</a><br>
    <p>'.sprintf($lang['DELETE_USER_INFO'], $group->currency, $link).'</p>
    <ul>';
    $users = $DBObject->GetUsersFromGroup($group->id);
    foreach ($users as $u){
        echo '<li><a href="'.$settings['site_url'].'delete-user.php?gid='.$group->id.'&uid='.$u->id.'" onClick="return confirm(\''.$lang['ARE_YOU_SURE_DELETE_USER'].'\')">'.$u->getFullName().'</a></li>';
    }
    echo '</ul>
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