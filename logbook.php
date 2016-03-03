<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_LOGBOOK'];
require_once("inc/header.inc.php");

if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
} else {
    if(isset($_GET['gid'])){
        $user = Authentication_Controller::GetAuthenticatedUser();
        $group = new Group;
        if($group->AuthenticationGroup($user, $_GET['gid'])){
            $group = $group->GetGroupById($_GET['gid']);
            $users = $group->getUsers();
            $range = 1;
            $cat = 'all';
            $count = 2;
            if(isset($_GET['range'])){
                $range = $_GET['range'];
            }
            if(isset($_GET['cat'])){
                $logbookItems = $DBObject->GetLogbookByGroupId($group->id,$_GET['cat'],$range);
                $count = $DBObject->CountLogbookByGroupId($group->id,$_GET['cat']);
                $cat = $_GET['cat'];
            }
            else{
                $logbookItems = $DBObject->GetLogbookByGroupId($group->id,'all',$range);
                $count = $DBObject->CountLogbookByGroupId($group->id,'all');
            }

            ?>
<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">
			<h1><?php echo $lang['LOGBOOK'];?></h1>
			<?php
			$lb = new Logbook(null,null,null,null,null,null,null,null, null);
			$lb->LogBookTable($logbookItems,$group,$count,$cat);
			?>

		</div>
            <?php
            echo '<aside class="l-box pure-u-1-4">
            <h2>'.$lang['OPTIONS'].'</h2>
            <div class="box">
            <a href="'.$settings['site_url'].'logbook/'.$group->id.'/all/1" class="btn btn-white">'.$lang['ALLOGBOOK'].'</a>
            <a href="'.$settings['site_url'].'logbook/'.$group->id.'/cost/1" class="btn btn-white">'.$lang['MENU_COSTMANAGEMENT'].'</a>
            <a href="'.$settings['site_url'].'logbook/'.$group->id.'/dinner/1" class="btn btn-white">'.$lang['MENU_DINNER_LIST'].'</a>
            <a href="'.$settings['site_url'].'logbook/'.$group->id.'/task/1" class="btn btn-white">'.$lang['MENU_TASK_LIST'].'</a>
            <a href="'.$settings['site_url'].'logbook/'.$group->id.'/group/1" class="btn btn-white">'.$lang['GROUPLOGBOOK'].'</a></div>';
            $group->ShowSideBarStickyNotes();
            $group->ShowSideBarSettings();
            echo '		</aside>';
			echo '	</div>';
			echo '</div>';
        }
        else{
            header ('Location: '.$settings['site_url']);
        }
    }
    else {
        header ('Location: '.$settings['site_url']);
    }
}
require_once("inc/footer.inc.php");
?>