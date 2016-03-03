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
$_page_title = $lang['PAGENAME_EDIT_ACCOUNT'];
require_once("inc/header.inc.php");

if(Authentication_Controller::IsAuthenticated())
{
    $user = Authentication_Controller::GetAuthenticatedUser();
    if($user->dateOfBirth != null){
        list($yyyy,$mm,$dd) = explode('-',$user->dateOfBirth);
        $date = $dd.'-'.$mm.'-'.$yyyy;
    }
    else{
        $date = '';
    }
    ?>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <link rel="stylesheet" href="/resources/demos/style.css" />
    <script>
        $(function() {
            $( "#datepicker" ).datepicker();
            $( "#datepicker" ).datepicker("option", "dateFormat", "dd-mm-yy");
            $( "#datepicker" ).datepicker( "setDate", "<?php echo $date ?>" );
        });
    </script>
    <?php

    if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
        $showForm = true;

    } else {

        $showForm = false;

        if (!preg_match("/[0-9]{2}-[0-9]{2}-[0-9]{4}/", $_POST['date']))
        {
            $showForm=true;
            echo '<div class="error-bar">'.$lang['INPUT_DATE_NOT_CORRECT'].'<br></div>';
        }
        else{
            list($dd,$mm,$yyyy) = explode('-',$_POST['date']);
            if (!checkdate($mm,$dd,$yyyy)) {
                $showForm = true;
                echo '<div class="error-bar">'.$lang['INPUT_DATE_NOT_CORRECT'].'<br></div>';
            }
            else{
                $date = $yyyy.'-'.$mm.'-'.$dd;
                if($_POST['firstname'] != null && strlen(trim($_POST['firstname'])) >= 2){
                    $user->firstName = $_POST['firstname'];
                }
                $user->setDateOfBirth($date);
                if($_POST['address'] != null){
                    $user->address = $_POST['address'];
                }

                if($_POST['zipcode'] != null){
                    $user->zipcode = $_POST['zipcode'];
                }

                if($_POST['city'] != null){
                    $user->city = $_POST['city'];
                }

                if($_POST['countryCode'] != null){
                    $user->country = $_POST['countryCode'];
                }

               /* if($_POST['bankacc'] != null){
                    $user->bankAccount = $_POST['bankacc'];
                }*/

                if($_POST['langCode'] != null){
                    $user->preferredLanguage = $_POST['langCode'];
                }

                if($_POST['study'] != null){
                    $user->school = $_POST['study'];
                }

                if($_POST['surname'] != null && strlen(trim($_POST['surname'])) >= 2){
                    $user->surname = $_POST['surname'];
                }

                $user->EditUserData($user);
                $notification = '<div class="notification-bar">'.$lang['USER_DATE_CHANGED'].'<a href="'.$settings['site_url'].'my-account">'.$lang['CLICKHERE'].'</a>'.$lang['TO_CONTINUE'].'<br></div>';
            }
        }
    }

    if($showForm){
    echo '<div class="normal-content"><div class="pure-g">';
        if(isset($notification)) {
            echo $notification;
        }

      echo '<div class="l-box pure-u-3-4">
	<h1>'.$lang['WELCOME'].$user->firstName.'</h1>
	<p>'.$lang['EDIT_ACCOUNT_INFO'].'</p>
	<form method="post" action="">
	    <span>
	        <label>'.$lang['FIRSTNAME'].'</label>
	        <input type="text" name="firstname" value="'.(isset($_POST['firstname']) ? $_POST['firstname'] : $user->firstName).'">
	    </span>
	    <span>
	        <label>'.$lang['LASTNAME'].'</label>
	        <input type="text" name="surname" x-autocompletetype="famely-name" value="'.(isset($_POST['surname']) ? $_POST['surname'] : $user->surname).'">
	    </span>
	    <span class="clear">
	        <label>'.$lang['ADDRESS'].'</label> <input type="text" name="address" x-autocompletetype="street-address" value="'.(isset($_POST['address']) ? $_POST['address'] : $user->address).'" tabindex="1">
	    </span>
	    <span>
		    <label>'.$lang['ZIPCODE'].'</label>	<input type="text" name="zipcode" x-autocompletetype="postal-code" value="'.(isset($_POST['zipcode']) ? $_POST['zipcode'] : $user->zipcode).'" tabindex="2">
		</span>
		<span class="clear">
		    <label>'.$lang['PLACE'].'</label> <input type="text" name="city" x-autocompletetype="city" value="'.(isset($_POST['city']) ? : $user->city).'" tabindex="3">
		</span>
		<span class="clear">
		    <label>'.$lang['LAND'].'</label> '.countryArray('countryCode',($user->country == null ? 'NL': $user->country)).'
		</span>
		<span class="clear">
		    <label>'.$lang['DATEOFBIRTH'].'</label><input type="text" name="date" id="datepicker" tabindex="5">
		</span>
		<span class="clear">
		    <label>'.$lang['EDUCATION'].'</label><input type="text" name="study" value="'.(isset($_POST['study']) ? $_POST['study'] : $user->school).'" tabindex="6">
		</span>';
		//<span class="clear">
		  //  <label>'.$lang['ACCOUNT'].'</label><input type="text" name="bankacc" value="'.(isset($_POST['bankacc']) ? $_POST['bankacc'] : $user->bankAccount).'" tabindex="7">
		//</span>
		echo '<span class="clear">
		    <label>'.$lang['PREFFERD_LANGUAGE'].'</label>'.languageArray('langCode',$lang['_LANG_CODE']).'
		</span>
		<span class="clear">
            <input type="submit" class="alt_btn" value="'.$lang['SAVE'].'" tabindex="9">
            <input type="button" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'my-account\'" tabindex="10"/>
		</span>

		<span class="clear">
		    <a href="'.$settings['site_url'].'delete-account" onClick="return confirm(\''.$lang['ARE_YOU_SURE_DELETE_ACCOUNT'].'\')">'.$lang['DELETE_USERACCOUNT'].'</a>
		</span>
	</form>
	</div>
	<aside class="l-box pure-u-1-4">
        <div class="box">
            <p>'.$lang['NEW_FREE_GROUP'].'</p>
            <a href="'.$settings['site_url'].'new-group" class="btn btn-white">'.$lang['NEW_GROUP'].'</a>
        </div>
    </aside>';
	echo '	</div>';
	echo '</div>';
    }
}
include_once("inc/footer.inc.php");
?>

