<?php
/**
 * User: Roel Verdonschot
 * Date: 28-8-13
 * Time: 16:12
 * To change this template use File | Settings | File Templates.
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_IMPORT'];
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
        $group = $group->GetGroupById($_GET['gid']);
        $showform = true;

        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            $users = $group->getUsers();
            $amount = $_POST["amount"];
            $cost = new Cost();
            $saldo = 0;
            $isNumric = true;
            if(isset($amount)){
                foreach($amount as $s){
                    $s = str_replace(',', '.', $s);
                    if(is_numeric($s)){
                        $saldo = $saldo + $s;
                    }
                    else{
                        $isNumric = false;
                    }
                }
                if($isNumric == true){
                    if($saldo < 0.02 && $saldo > -0.02){
                        if(isset($users)){
                            foreach($users as $u){
                                $amountUser = str_replace(',', '.', $_POST["amount"][$u->id]);
                                $date = date("Y-m-d");
                                $costId = $cost->AddCost($amountUser,'Import',$u->id,$group,2,$date);

                                $DBObject->UpdateGroupUserSaldo($group->id);
                                // $cost->AddUserCost($u,$costId,1);
                                // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                                $lb = new Logbook(null, $group->id, $user->id, null, $costId, null, null, 'CA', 'Import');
                                $DBObject->AddLogbookItem($lb);
                            }
                            header('Location: ' . $settings['site_url'].'cost/'.$group->id.'/');
                        }
                    }
                    else{
                        $notification = '<div class="error-bar">'.($lang['_LANG_CODE'] == 'en' ? sprintf($lang['ERROR_SALDO_NOT_NULL_1'], $group->currency) :
                                sprintf($lang['ERROR_SALDO_NOT_NULL_1'], $group->currency,$group->currency)).'</div>';; // die op '.$group->currency.' 0.00 uitkomen</div>';
                    }
                }
                else{
                    $notification = '<div class="error-bar">'.$lang['ERROR_IMPORT_NUMRIC'].'</div>';
                }
            }
            else{
                $notification = '<div class="error-bar">'.$lang['ERROR_IMPORT'].'</div>';
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
		<div class="l-box pure-u-3-4">';
    if($group->checkCost()){
        if(isset($notification)) {
            echo $notification;
        }
        $users = $group->getUsers();
        if(isset($users)){
            echo '<h1>'.$lang['IMPORT_COSTS'].'</h1>';
            echo '<form method="post" action="">
            <p>'.sprintf($lang['IMPORT_INFO_1'], $group->currency,$group->currency).'</p>';
            $count = 0;
            foreach($users as $u){
                $count++;
                echo '
        <span>
            <label>'.$u->getFullName()  .': </label>
            <span>'.$group->currency.'</span> <input type="text" name="amount['.$u->id.']" class="amount" id="basicCalculator" value="" tabindex="'.$count.'"/>
        </span>';
            }
            echo '<span class="clear"><input type="submit" value="'.$lang['SAVE'].'" tabindex="'.($count + 1).'">
            <input type="button" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'cost.php?gid='.$_GET['gid'].'\'"tabindex="'.($count + 2).'"/></span></form>';
        }
    }
    else{
        echo '<div class="error-bar">'.$lang['ERROR_COST_NOTACTIVE'].'<a href="'.$settings['site_url'].'editgroup.php?gid='.$group->id.'">'.$lang['CLICKHERE'].'</a>'.$lang['EDIT_GROUP_MODULES'].'</div>';
    }
    echo '</div>';
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