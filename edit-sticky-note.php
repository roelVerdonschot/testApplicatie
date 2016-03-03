<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_EDITSTICHYNOTE'];

$showform = false;
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
}

if(isset($_GET['gid'])){
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group;
    if($group->AuthenticationGroup($user, $_GET['gid'])){
        $showform = true;
        $group = $group->GetGroupById($_GET['gid']);

        if(isset($_GET['sid'])){
            $note = $DBObject->GetStickyNotes($_GET['sid'],$group->id);
            if($note == null){
                header ('Location: '.$settings['site_url']);
            }
        }
        else{
            header ('Location: '.$settings['site_url']);
        }

        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
            $showform = true;
        }
        else {
            $showform = false;
            $title = trim(strip_tags($_POST["stickynote-title"]));
            $message = trim(strip_tags($_POST["stickynote-message"]));

            if($message == null){
                $error[] = 'Enter message';
                $showform = true;
            }

            if($showform === false){
                $DBObject->EditStickyNote($note->id,$title,$message,$group->id);
                header('Location: '.$settings['site_url']."sticky-notes/".$group->id);
            }

        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

if($showform){
    $title = $note->title;
    $desc = $note->message;
    require_once("inc/header.inc.php");
    echo '
<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">
    <h1>'.$lang['EDIT_STICKY_NOTE'].'</h1>
	    <form method="post" action="">
	    <span>
            <label>'.$lang['TITLE'].'</label>
            <input type="text" name="stickynote-title" value="'.(isset($title) ? $note->title : '').'" tabindex="1">
	    </span>
	    <span class="clear">
	        <label>'.$lang['DESCRIPTION'].'</label>
	        <input type="text" name="stickynote-message" value="'.(isset($desc) ? $note->message : '').'" tabindex="2">
	    </span>
	    <span class="clear">
            <input type="submit" class="alt_btn" value="'.$lang['EDIT'].'" tabindex="3">
            <input type="button" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'group/'.$group->id.'\'" tabindex="4"/>
        </span>
        </form>';
    echo '</div>';
    echo '<aside class="l-box pure-u-1-4">';
    $group->ShowSideBarStickyNotes();
    $group->ShowSideBarSettings();
    echo '		</aside>';
	echo '	</div>';
	echo '</div>';
}
else{
    // header ('Location: '.$settings['site_url']);
}

include_once("inc/footer.inc.php");
?>