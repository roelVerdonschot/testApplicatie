<?php
/**
 * User: Roel Verdonschot
 * Date: 13-9-13
 * Time: 23:38
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
    echo '<h1>'.$lang['COSTMANAGEMENT_HISTORY'].'</h1>';
    $range = 1;
    if(isset($_GET['range'])){
        $range = $_GET['range'];
    }
    $costs = $DBObject->GetPayedGroupCosts($group->id,$range);
    if(isset($costs) && COUNT($costs) > 0){
        echo '<table border="1">
            <tr><th>'.$lang['PAID_BY'].'</th><th>'.$lang['DESCRIPTION'].'</th><th>'.$lang['AMOUNT'].'</th><th>'.$lang['DATE'].'</th><th>'.$lang['IS_DINNER'].'</th><th>'.
            $lang['WHO_SHARE_COST'].'</th></tr>';
		$w = 0;
        foreach( $costs as $c ){
			$w++;
            $explode = explode("-",$c->date);
            $CDate = $explode[2]."-".$explode[1]."-".$explode[0];
            echo '<tr '.($w % 2 ? '' : 'class="alt"').'><td>'.($c->deleted == '1' ? '<del>'.$DBObject->GetUserNameById($c->idUser).'</del>' : $DBObject->GetUserNameById($c->idUser)).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$c->description.'</del>' : $c->description).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$group->currency.' '.$c->amount.'</del>' : $group->currency.' '.$c->amount).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$CDate.'</del>' : $CDate).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.($c->isDinner == 0 ? $lang['NO'] : $lang['YES']).'</del>' : ($c->isDinner != 1 ? $lang['NO'] : $lang['YES'])).'</td>';
            $ids = $c->users;
            if(isset($ids)){
                echo '<td>'.($c->deleted == '1' ? '<del>': '');
                $count = 0;
                foreach($ids as $u){
                    echo (COUNT($ids) == 1 ? '' : ($count > 0 && $count < COUNT($ids) -1 ? ', ' : '').($count == COUNT($ids) -1 ? ' '.$lang['AND'].' ' : '')).
                        $DBObject->GetUserNameById($u->idUser).($u->numberOfPersons > 1 ? ' '.$u->numberOfPersons.'x' : '');
                    ++$count;
                }
                echo ($c->deleted == '1' ? '</del></td>': '');
            }
            else{
                echo '<td></td>';
            }
        }
        echo "</table>";
        $count = $DBObject->CountCostByGroupId($group->id, "payed", 0);
        if($count > 50){
            $j = 1;
            for($i = 1 ; $i < $count ; $i = $i + 50){
                echo ($range == $j ? '['.$j.'] ' : '<a href="'.$settings['site_url'].'cost-history-payed.php?gid='.$group->id.'&range='.$j.'">'.$j.'</a> '); // class="btn btn-white"
                $j++;
            }
        }
    }
    else{
        echo '<div class="notification-bar">'.$lang['NO_COST_FOUND'].'</div>';
    }
    echo '
    </div>
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