<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Pascal
 * Date: 6-8-13
 * Time: 17:07
 * To change this template use File | Settings | File Templates.
 **/
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_EDIT_LOGIN'];
require_once("inc/header.inc.php");
// include dropdown for country/language

if (Authentication_Controller::IsAuthenticated()) {

    $user = Authentication_Controller::GetAuthenticatedUser();
echo '<div class="normal-content">
        <div class="pure-g">';
    if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
        $showForm = true;
    } else {
        $showForm = false;
        // check if emails are the same and not empty to change email
        if ($_POST['email'] != $_POST['email2']) {

            echo '<div class="l-box-top pure-u-1"><div class="error-bar">'.$lang['ERROR_EMAIL_NOT_SAME'].'<br></div></div>';
            $showForm = true;
        }

        if ($_POST['email'] != $user->email && $_POST['email'] == $_POST['email2']) {
            $user->EditEmail($_POST['email'], $user->id);
            $showForm = false;
            echo '<div class="l-box-top pure-u-1"><div class="notification-bar">'.$lang['EMAIL_CHANGED_AND_SEND'] . $_POST['email'] . '.<br />
            <a href="' . $settings['site_url'] . '">'.$lang['CLICKHERE'].'</a>'.$lang['BACK_TO_ACCOUNT'].'</br></div></div>';
        }

        // check if both old and new pass fields are filled
        if (!empty($_POST['oldpass']) && !empty($_POST['newpass'])) {
            //check if passwords match
            if ($_POST['newpass'] == $_POST['newpass2']) {
                $newpass = $_POST['newpass'];
                $oldpass = $_POST['oldpass'];
                $showForm = false;
                // method returns false if old password was wrong
                if (!($user->EditPassword($oldpass, $newpass, $user->id))) {
                    echo '<div class="l-box-top pure-u-1"><div class="error-bar">'.$lang['ERROR_PASSWORD_NOT_SAME'].'<br /></div></div>';
                    $showForm = true;
                } else {
                    echo '<div class="l-box-top pure-u-1"><div class="notification-bar">'.$lang['PASSWORD_CHANGED'].'<br>
                <a href="' . $settings['site_url'] . '">'.$lang['CLICKHERE'].'</a>'.$lang['BACK_TO_ACCOUNT'].'<br /></div></div>';
                }

            } else {
                echo '<div class="l-box-top pure-u-1"><div class="error-bar">'.$lang['ERROR_PASSWORD_NOT_SAME'].'<br /></div></div>';
                $showForm = true;
            }
        }
		else
		{
			echo '<div class="l-box-top pure-u-1"><div class="error-bar">'.$lang['ERROR_PASSWORD_NOT_SAME'].'<br /></div></div>';
			$showForm = true;
		}		
    }
    if ($showForm) {
        echo '
	<div class="pure-u-3-4">
        <div class="l-box-top pure-u-1">
            <h1>'.$lang['SETTINGS_TITLE'].'</h1>
            <p>&nbsp;</p>
        </div>';
        $user->ShowSettingsLeftSideBar("login");
        echo '
        <div class="l-box middle-box pure-u-17-24">
	<h2>'.$lang['SETTINGS_LOGIN_TITLE'].'</h2>
	<form method="post" action="">
        <span class="clear">
            <label>'.$lang['EMAIL'].'</label>
            <input type="email" name="email" value="' . $user->email . '" tabindex="1">
        </span>
        <span>
            <label>'.$lang['REPEAT_EMAIL'].'</label>
            <input type="text" name="email2"  value="' . $user->email . '" tabindex="2">
        </span>
        <span class="clear">
            <label>'.$lang['OLD_PASSWORD'].'</label>
            <input type="password" name="oldpass" tabindex="3"> </br>
        </span>
        <span class="clear">
            <label>'.$lang['NEW_PASSWORD'].'</label>
            <input type="password" name="newpass" tabindex="4"></br>
        </span>
        <span>
            <label>'.$lang['REPEAT_PASSWORD'].'</label>
            <input type="password" name="newpass2" tabindex="5"></br>
        </span>
        <span class="clear">
            <input type="submit" class="alt_btn" value="'.$lang['SAVE'].'" tabindex="6">
            <input type="button" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'my-account\'" tabindex="7"/>
        </span>
	</form>
	</div>
	</div>';
    }
	
echo '	</div>';
echo '</div>';
}
require_once("inc/footer.inc.php");
?>

