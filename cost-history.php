<?php
/**
 * User: Roel Verdonschot
 * Date: 12-8-13
 * Time: 18:47
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_GROUPCOSTHISTORY'];
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
    $range = 1;
    if(isset($_GET['range'])){
        $range = $_GET['range'];
    }
    $cost = new Cost();
    $cost->ShowCostHistory($user,$group,$range);
    $count = $DBObject->CountCostByGroupId($group->id, "my", $user->id);
    if($count > 50){
        $j = 1;
        for($i = 1 ; $i < $count ; $i = $i + 50){
            echo ($range == $j ? '['.$j.'] ' : '<a href="'.$settings['site_url'].'cost-history-all.php?gid='.$group->id.'&range='.$j.'">'.$j.'</a> '); // class="btn btn-white"
            $j++;
        }
    }
    echo '</div>
    <aside class="l-box pure-u-1-4">
    <h2>'.$lang['OPTIONS'].'</h2>
    <div class="box">
    <a href="'.$settings['site_url'].'cost-history/'.$group->id.'" class="btn btn-white">'.$lang['OWN_COSTS'].'</a>
    <a href="'.$settings['site_url'].'cost-history-all/'.$group->id.'" class="btn btn-white">'.$lang['.ALL_COSTS.'].'</a>
    <a href="'.$settings['site_url'].'cost-history-payed/'.$group->id.'" class="btn btn-white">'.$lang['PAYED_COSTS'].'</a></div>';
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