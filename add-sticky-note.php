<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_ADDSTICHYNOTE'];

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

        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
            $showform = true;
        }
        else {
            $showform = false;
            $title = trim(strip_tags($_POST["stickynote-title"]));
            $message = trim(strip_tags($_POST["stickynote-message"]));

            if($message != null){
                $stickynote = new Sticky_Note(0,$title,$message,null,$user->id,$group->id);
                $stickynote->save();
            }

            $path = trim(strip_tags($_POST["current-page"]));

            header('Location: '.$settings['site_url'].$path);
        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

if($showform){
    require_once("inc/header.inc.php");
    echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">
    <h1>'.$lang['NEW_STICKY_NOTE'].'</h1>
	    <form method="post" action="">
	    <span>
            <label>'.$lang['TITLE'].'</label>
            <input type="text" name="stickynote-title" value="" tabindex="1">
	    </span>
	    <span class="clear">
	        <label>'.$lang['DESCRIPTION'].'</label>
	        <input type="text" name="stickynote-message" value="" tabindex="2">
	    </span>
	    <span class="clear">
            <input type="submit" class="alt_btn" value="'.$lang['SAVE'].'" tabindex="3">
            <input type="submit" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'group/'.$group->id.'\'" tabindex="4"/>
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