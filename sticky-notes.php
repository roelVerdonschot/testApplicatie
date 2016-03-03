<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_STICKYNOTES'];
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
            $stickynotes = $group->getAllStickyNotes();

            // Delete sticky note
            if(isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['snid'])){
                //$DBObject->GetUserTask($_GET['uid'],$_GET['tid'],$_GET['date']);
                $DBObject->DeleteStikyNote($_GET['snid']);
                header ('Location: '.$settings['site_url'].'sticky-notes/'.$group->id.'/');
            }

            ?>
            <div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">
                <h1><?php echo $lang['PRICKBORD'];?></h1>
                <?php
                if($stickynotes != null) {
                    foreach ($stickynotes as $sn) {
                        echo '
                        <article class="stickynote">
                            <h2>'.$sn->title.' <span><a name="'.$sn->id.'" onClick="javascript:return confirm(\''.$lang['YOU_USER_DELETE_NOTE'].'\')" href="'.$settings['site_url'].'sticky-notes.php?gid='.$group->id.'&snid='.$sn->id.'&action=del"><img src="'.$settings['site_url'].'images/icons/remove.png" alt="delete" /></a><span></h2>
                            <p>'.$sn->message.'</p>
                            <span class="info">'.$sn->date.' - '.$DBObject->GetUserNameById($sn->idUser).'  <a href="'.$settings['site_url'].'edit-sticky-note/'.$group->id.'/'.$sn->id.'/">'.$lang['EDIT'].'</a></span>
                        </article>';
                    }
                } else {
                    echo '<p>'.$lang['NOT_FOUND_PRICKBORD'].'</p>';
                }
                ?>
            </div>

            <aside class="l-box pure-u-1-4">
                <?php
                $group->ShowSideBarStickyNotes();
                $group->ShowSideBarSettings();
                ?>
            </aside>
		</div>
	</div>
        <?php
        } else {
            header ('Location: '.$settings['site_url']);
        }
    } else {
        header ('Location: '.$settings['site_url']);
    }
}
require_once("inc/footer.inc.php");
?>