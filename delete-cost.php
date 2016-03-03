<?php
/**
 * User: Roel Verdonschot
 * Date: 13-8-13
 * Time: 15:43
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_DELETECOST'];
require_once("inc/header.inc.php");

if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
}

if(isset($_GET['gid']) && isset($_GET['cid'])){
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group;
    if($group->AuthenticationGroup($user, $_GET['gid'])){
       $cost = new Cost;
        $costBool = false;
        $cost = $cost->GetCostById($_GET['cid']);
        if($cost->idGroup == $group->id){
            $costBool = true;
        }
        if($costBool == false){
            header ('Location: '.$settings['site_url'].'cost/'.$group->id);
        }
        if($cost->DeleteCostById($_GET['cid'])){
            $group = $group->GetGroupById($_GET['gid']);
            $DBObject->UpdateGroupUserAvgCooked($group);
            $DBObject->UpdateGroupUserSaldo($group->id);

            // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
            $lb = new Logbook(null, $_GET['gid'], $user->id, null, $_GET['cid'], null, null, 'CD', null);
            $DBObject->AddLogbookItem($lb);
            header ('Location: '.$settings['site_url'].'cost/'.$_GET['gid']);
        }
        else
        {
            echo '<div class="normal-content">';
            echo '<div class="error-bar">'.$lang['DELETE_COST_FAILED'].'<a href="'.$settings['site_url'].'cost-history/'.$_GET['gid'].'">'.$lang['CLICK_HERE_TO_GO_BACK'].'</a></div></div>';
        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}
else
{
    header ('Location: '.$settings['site_url'].'cost/'.$_GET['gid']);
}
include_once("inc/footer.inc.php");
?>