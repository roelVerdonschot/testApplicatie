<?php
/**
 * User: Pascal Worek
 * Date: 9-7-13
 * Time: 12:25
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_RESET_PASSWORD'];
$_no_header = true;
require_once("inc/header.inc.php");
?>
</head>
<body id="login">

<div class="pure-g-r login">

    <div class="pure-u-1">

		<div class="header">
			<a class="logo-login" href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/"></a>
		</div>


<?php
echo '<div id="login-box">'; 
if(!isset($_GET['ac']))
{
    //echo'<div class="fieldLogin">';

    $error = null;
    if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
        $formulier = true;

    } else {
        $user = new User();
        $formulier = false;
        // everything needs to be filled in
        if (!empty($_POST['e-mail'])) {
            // checks if the given emailadres exists in the database
            if (!$user->ResetPassword($_POST['e-mail'])) {
                $error = $lang['NO_ACCOUNT_FOUND'];
                $formulier = true;
            } else {

                echo '<div class="success-bar">'.$lang['RESET_PASS'].'</div>';
            }
        } else {
            $error = $lang['FILL_IN_EMAIL'];
            $formulier = true;
        }

    }
    if ($formulier) {
	
        echo '<h1>'.$lang['LOGINBOX_FORGOT_PASSWORD'].'</h1>
			<p>'.$lang['ENTER_EMAIL_TO_RESET_PASS'].'</p>';
        if(!empty($error))
        {
            echo '<div class="error-bar">'.$error.'</div>';
        }
        echo '
            <form method="post" action="">
				<fieldset>
					<p><input name="e-mail" type="text" placeholder="'.$lang['EMAILADRES'].'" value="'.(isset($_POST['e-mail']) ? htmlspecialchars($_POST['e-mail']) : "").'" /></p>
					<p><button type="submit" class="btn btn-red btn-lg">'.$lang['RESET_PASSWORD'].'</button></p>					
					<p><a href="'.$settings['site_url'].'" class="btn btn-grey btn-lg">Terug</a></p>
				</fieldset>
            </form>';

    }

} else {
    $user = new User();
    if($user->CheckResetCode($_GET['ac']))
    {
        $error = null;
        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
            $formulier = true;
        } else {
            $formulier = false;
            if ($_POST['pass'] == $_POST['pass2']) {
                $user->ChangePassword($_POST['pass'],$_GET['ac']);
                echo '<div class="success-bar">'.$lang['PASSWORD_CHANGED'].' <a href="'.$settings['site_url'].'login">'.$lang['CLICKHERE'].'</a>'.$lang['TO_GO_BACK'];
            } else {
                $error = $lang['ERROR_PASSWORD'];
                $formulier = true;
            }
        }
        if ($formulier) {
            echo '<p>'.$lang['RESET_PASSWORD_INFO'].'</p>';
            if(!empty($error))
            {
                echo '<div class="error-bar">'.$error.'</div>';
            }
            echo '
            <form method="post" action="">
                <span>
                    <label>'.$lang['LOGINBOX_PASSWORD'] .'</label>
                    <input name="pass" type="password" value="'.(isset($_POST['e-mail']) ? htmlspecialchars($_POST['e-mail']) : "").'" tabindex="3" />
                </span>
                <span>
                    <label>'.$lang['REPEAT_PASSWORD'].'</label>
                    <input name="pass2" type="password" value="'.(isset($_POST['e-mail']) ? htmlspecialchars($_POST['e-mail']) : "").'" tabindex="3" />
                </span>
                <span class="clear">
                    <input type="submit" class="submit_btn" value="'.$lang['RESET_PASSWORD'].'" tabindex="5">
                </span>
            </form>';
        }
    }
    else
    {
        echo '<p>'.$lang['NO_VALID_LINK'].'<a href="'.$settings['site_url'].'login">'.$lang['CLICKHERE_TO_GO_TO_INLOG'].'</a></p>';
    }
}
echo '</div>';
$_no_footer = true;
include_once("inc/footer.inc.php");
?>
