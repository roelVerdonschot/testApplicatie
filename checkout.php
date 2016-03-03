<?php
/**
 * User: Roel Verdonschot
 * Date: 8-8-13
 * Time: 15:22
 */
ob_start();
require_once("inc/config.inc.php");
$showform = false;
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
    $showform = false;
}
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_CHECHOUT'];
require_once("inc/header.inc.php");


if(isset($_GET['gid'])){
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group;
    if($group->AuthenticationGroup($user, $_GET['gid'])){
        $group = $group->GetGroupById($_GET['gid']);
        $showform = true;

        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            $instant = new cost();
            $ToPay = $instant->PayOff($_GET['gid']);
            $checkId = $DBObject->AddCheckout($_GET['gid']);
            foreach($ToPay as $p){
                //$idgroup,$idcheck,$idpayer, $idreceiver, $amount
                error_log($_GET['gid'].'-'.$checkId.'-'.$p->idpayer.'-'.$p->idreceiver.'-'.$p->amount);
                $DBObject->AddCheckoutData($_GET['gid'],$checkId,$p->idpayer,$p->idreceiver,$p->amount);
            }
            $userIdAmount = $instant->CalculateGroupSaldo($group->id);
            if($instant->PayOffDB($_GET['gid'])){
                // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                $lb = new Logbook(null, $group->id, $user->id, null, null, null, null, 'PO', null);
                $DBObject->AddLogbookItem($lb);
                foreach($userIdAmount as $u){
                    $pay = '';
                    $get = '';
                    foreach($ToPay as $p){
                        if($u->id == $p->idpayer){
                            $pay = $pay . $lang['TO_PAY_AMOUNT'].$DBObject->GetUserNameById($p->idreceiver).': '.$group->currency.' '.$p->amount.'<br>';
                        }
                        if($u->id == $p->idreceiver){
                            $get = $get . $lang['TO_RECIEVE_AMOUNT'].$DBObject->GetUserNameById($p->idpayer).': '.$group->currency.' '.$p->amount.'<br>';
                        }
                    }
                    Email_Handler::mailPayOff($pay,$get,$group->name,$u->id,$group->id);
                }
                $notification = '<div class="l-box pure-u-1 notification-bar">'.$lang['CHECKOUT_SUCCESSFUL'].$group->currency. '0,-<br></div>';				
                $DBObject->UpdateGroupUserSaldo($group->id); // TODO is this correct? YES
            }
            else{
               $notification = '<div class="l-box pure-u-1 error-bar">'.$lang['CHECKOUT_FAILED'].'<br></div>';
            }
        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

if($showform){
    $instant = new cost();
	echo '<div class="normal-content">
	<div class="pure-g">';
	if(isset($notification))
	{
		echo $notification;
	}
	echo '<div class="l-box pure-u-3-4">';
    $instant->ShowCheckOutTable($group,$_GET['gid']);
    echo '</div>
	<aside class="l-box pure-u-1-4">';
    $group->ShowSideBarStickyNotes();
    $group->ShowSideBarSettings();
    echo '</aside>';
	echo '	</div>';
	echo '</div>';

}
else{
    header ('Location: '.$settings['site_url']);
}

include_once("inc/footer.inc.php");
?>