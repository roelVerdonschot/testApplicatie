<?php
/**
 * User: roel
 * Date: 9-10-13
 * Time: 10:20
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_DELETEUSERACCOUNT'];
require_once("inc/header.inc.php");
$showform = false;
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
    $showform = false;
}
else{
    $user = Authentication_Controller::GetAuthenticatedUser();
    $showform = true;
    $saldoBool = true;
    if(isset($_GET['da'])){
        $group = new group();
        $groups = $group->GetMyGroup($user->id);
        if(isset($groups)){
            foreach($groups as $g){
                if($g->CheckCost()){
                    $useramount = $g->GetBalanceByUser($user);
                    if($useramount < -0.05 || $useramount > 0.05){
                        $saldoBool = false;
                    }
                }
            }
        }
        if($saldoBool){
            if($_GET['da'] == 'yes' || $_GET['da'] == 'yes/'){
                $Deletedemail = "deleted|".$user->email;
                if($DBObject->DeleteUserAccount($Deletedemail,$user->id)){
                    Authentication_Controller::LogOut();
					header ('Location: '.$settings['site_url']);
					die();
                }
                else{
                    $notification = '<div class="l-box pure-u-1 error-bar">'.$lang['USERACCOUNT_ISNOT_REMOVED'].'<br></div>';
                }
            }
        }
        else{
            $notification = '<div class="l-box pure-u-1 error-bar">'.$lang['USERACCOUNT_HAS_SALDO'].'<br></div>';
        }
    }
}

if($showform){
    echo '<div class="normal-content"><div class="pure-g">';
    if(isset($notification)) {
        echo $notification;
    }
	echo '<div class="l-box pure-u-1">
		 <h1>'.$lang['DELETE_USERACCOUNT'].'</h1>
		 <p>'.$lang['DELETE_ACCOUNT_INFO'].'</p>
		<span class="clear">
				<input type="button" class="secundaire-btn" value="'.$lang['DELETE_USERACCOUNT'].'" onclick="if (confirm(\''.$lang['ARE_YOU_SURE_DELETE_ACCOUNT_DEF'].'\'))window.location.href=\''.$settings['site_url'].'delete-account/?da=yes/\'" tabindex="1"/>
		</span>
    </div>';
	echo '	</div>';
	echo '</div>';
}
else{
    header ('Location: '.$settings['site_url']);
}

include_once("inc/footer.inc.php");
?>