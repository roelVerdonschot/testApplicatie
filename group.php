<?php
/**
 * User: Roel Verdonschot
 * Date: 6-8-2013
 * Time: 19:55
 */
ob_start();
require_once("inc/config.inc.php");
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
    $showform = false;
}

$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_GROUP'];
require_once("inc/header.inc.php");

$showform = false;

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
    $myGroups = $group->GetMyGroup($user->id);
    echo '  <div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">
	<h1>'.$group->name.'</h1>';
    if(COUNT($myGroups) > 1){
        echo '<p><a href="'.$settings['site_url'].'">&larr; '.$lang['BACK_TO_INDEX'].'</a></p>';
    }
    echo '<table class="index-table">
    <tr><th>'.$lang['NAME'].'</th>'.($group->CheckCost() ? '<th class="balance-column onepx">'.$lang['BALANCE'].'</th>' : '').($group->CheckDinner() ? '<th class="'.($group->CheckTask() ? 'balance-column' : '').' onepx">'.$lang['TODAY'].'</th>' : '').($group->CheckTask() ? '<th class="onepx">'.$lang['TASK'].'</th>' : '').'</tr>'; //
    $users = $group->getUsers();
    $group->setUsers($users);
    $rowBack = 0 ;
    $userSaldo = $DBObject->GetGroupUserSaldo($group->id);
    foreach( $users as $u )
    {
        echo '<tr><td '.($rowBack == 0 ? '' : 'class="datacelltwo"').'>'.$u->getFullName().'</td>';
        if($group->CheckCost()){
            echo '<td class="balance-column '.($rowBack == 0 ? '' : ' datacelltwo ').'">';
            if($userSaldo[$u->id][1] > 0 || $userSaldo[$u->id][1] == 0){
                echo '<a href="'.$settings['site_url'].'cost/'.$group->id.'/"><span class="txtPositive">'.$group->currency.' '.$userSaldo[$u->id][1].'</span></a>';
            }
            else{
                echo '<a href="'.$settings['site_url'].'cost/'.$group->id.'/"><span class="txtNegative">'.$group->currency.' '.$userSaldo[$u->id][1].'</span></a>';
            }
            echo '</td>';
        }

        if($group->CheckDinner()){
            echo '<td class="'.($group->CheckTask() ? 'balance-column' : '').' '.($rowBack != 0 ? ' datacelltwo' : '').'">';
            $group->ShowDinnerByUserDate($group,$u);
            echo '</td>';
        }

        if($group->CheckTask()){
            echo '<td '.($rowBack == 0 ? '' : 'class="datacelltwo"').'>';
            $mondaydate = date('Y-m-d', strtotime('last Monday + 0 week'));
            $usertasks = $group->getUserTasksByWeek($mondaydate);
            if(isset($usertasks[$u->id])){
                echo '<a href="'.$settings['site_url'].'tasks/'.$group->id.'/"><span class="txtPositive">'.$usertasks[$u->id]->name.'</span></a>';
            }
            else{
                echo '<a href="'.$settings['site_url'].'tasks/'.$group->id.'/"><span class="txtNegative">'.$group->GetTaskByUserWeek($u->id).'</span></a>';
            }
            echo '</td>';
        }
        echo '</tr>';
        if($rowBack == 0){
            $rowBack = 1;
        }
        else{
            $rowBack = 0;
        }
    }
    echo '</table><br /><br />
    <h3>'.$lang['LAST_CHANGES'].'</h3>';
    $logbookItems = $DBObject->GetLogbookByGroupIdMax10($group->id);
    $lb = new Logbook(null,null,null,null,null,null,null,null,null);
    $lb->LogBookTable($logbookItems,$group,1,'gtb');
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