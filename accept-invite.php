<?php
/**
 * User: Roel Verdonschot
 * Date: 7-8-13
 * Time: 3:42
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_ACCEPTINVITE'];

if(isset($_GET['e']) && isset($_GET['gid'])){
    $message = '';
    $email = $_GET['e'];
    $groupId = $_GET['gid'];
    $user = new User();
    $userId = $user->GetUserIdByEmail($email);
    //error_log($userId);
    if($userId == null){
        $message = '<div class="notification-bar">'.$lang['NO_ACCOUNT_YET'].'</div>';
        header("refresh:3; url=".$settings['site_url'].$lang['_LANG_CODE'].'/register/'.$groupId.'/'.$email.'/', true);
    }
    else{
        if($user->DeleteUserFromInvite($email,$groupId,$userId)){
            // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
            $lb = new Logbook(null, $groupId, $userId, null, null, null, null, 'UA', $email);
            $DBObject->AddLogbookItem($lb);
            $message=  '<div class="notification-bar">'.$lang['ADDED_TO_GROUP'].'<br />
                    <a href="'.$settings['site_url'].'">'.$lang['CLICKHERE'].'</a>'.$lang['TO_GO_ON'].'</div>';
        }
        else{
            $message = '<div class="error-bar">'.$lang['USER_CANNOT_BE_ADDED'].'<br></div>';
        }
    }
    //error_log($_GET['e']." ".$_GET['gid']);
    require_once("inc/header.inc.php");
    echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">';
    echo $message;
    echo '</div>';
    echo '	</div>';
    echo '</div>';
    require_once("inc/footer.inc.php");
}elseif(Authentication_Controller::IsAuthenticated())
{
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group();


    if(isset($_GET['dele']) && isset($_GET['gid'])){
        if($group->AuthenticationGroup($user, $_GET['gid'])){
            $DBObject->DeleteInviteByGid($_GET['gid'],$_GET['dele']);
            header('Location: ' . $settings['site_url'] . 'add-user/'.$_GET['gid']);
        }
        header('Location: '.$settings['site_url']);
    }

    if(isset($_GET['del'])){
        $DBObject->DeleteInviteById($_GET['del'],$user->email);
        header('Location: ' . $settings['site_url'] . 'my-account/');
    }

    if(isset($_GET['inv']) && isset($_GET['gid'])){
        $DBObject->DeleteUserFromInviteById($_GET['inv'],$user->id,$_GET['gid']);
        header('Location: ' . $settings['site_url'] . 'my-account/');
    }
}
else
{
    header ('Location: '.$settings['site_url'].'login/?ref=http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}
?>