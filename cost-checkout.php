<?php
/**
 * User: Roel Verdonschot
 * Date: 10-11-14
 * Time: 11:51
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_GROUPCHECKOUTHISTORY']; // set in lang file
require_once("inc/header.inc.php");

$showform = false;
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
}

if(isset($_GET['gid'])){
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group;
    if($group->AuthenticationGroup($user, $_GET['gid'])){
        $group = $group->GetGroupById($_GET['gid']);
        $showform = true;
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

if($showform){
    echo '<div class="normal-content">
		<div class="pure-g">
			<div class="l-box pure-u-3-4">';
	echo '<h1>'.$lang['CHECKOUTHISTORY'].'</h1>'; // set in lang file
    $cost = new Cost();
	$checkoutId = $DBObject->GetCheckOutIds($group->id);
	foreach($checkoutId as $id){
        $newDate = date("d-m-Y H:m:s", strtotime($id[1]));
		echo '<h2>'.$newDate.'</h2>';
		$toPay = $DBObject->GetCheckOutData($id[0]);
		$cost->ShowOldCheckOutTable($group,$toPay);
	}
    echo '
    </div>
    <aside class="l-box pure-u-1-4">';
    $group->ShowSideBarStickyNotes();
    $group->ShowSideBarSettings();
    echo '</aside>
	</div>
</div>';
}
else{
    header ('Location: '.$settings['site_url']);
}

include_once("inc/footer.inc.php");
?>

