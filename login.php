<?php
/**
 * User: Pascal Worek
 * Date: 11-7-13
 * Time: 12:09
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_LOGIN'];
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

    if (!Authentication_Controller::IsAuthenticated()) {
    // Anti-flood, na 5 keer verkeerd inloggen wordt je IP adres 24 uur geband
        if (Authentication_Controller::IsTemporarilyBanned()) { // Controleren of je bent geband
            echo '<div class="error-bar">'.$lang['ERROR_ATEMPT'].'<br></div>';
        } else {

            if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
                $showForm = true;
            } else {
                $showForm = false;
                if (true) {//ctype_digit($_POST['form']) && time() < strtotime("+8 minute", $_POST['form'])) {
                    $login = Authentication_Controller::Login($_POST['username'], $_POST['password']);

                    if(!is_bool($login) && 'notActivated' == $login) {
                        $error = $lang['ACCOUNT_NOT_ACTIVE'];
                        $showForm = true;
                    } else {
                        if ($login === true) {
                            $goTo = $settings['site_url'];
                            if(isset($_GET["ref"]))
                            {
                                $goTo = trim($_GET["ref"]);
                            }
                            header('Location: ' .$goTo . '');
                        } else {
                            $error = $lang['ERROR_PASS_EMAIL'].' <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/reset-password/">'.$lang['LOGINBOX_FORGOT_PASSWORD'].'</a>';
                            $showForm = true;
                        }
                    }
                } else {
                    $error = $lang['SESSIE_EXPIRED'];
                    $showForm = true;
                }

            }
            if ($showForm) {
				if(!empty($error))
				{
					echo '<div class="error-bar">'.$error.'</div>';
				}
                
                echo '
		<div id="login-box">
		<form action="" method="post">					
			<fieldset>
			<p><input type="email" name="username" value="'.(isset($_POST['username']) ? htmlspecialchars($_POST['username']) : "").'" placeholder="'.$lang['EMAILADRES'].'" autofocus="autofocus" ></p>
				<p><input type="password" name="password" placeholder="'.$lang['PASSWORD'].'" ></p>
				<p><button type="submit" class="btn btn-red btn-lg">Log in</button></p>
				<p><a href="'.$settings['site_url'].'" class="btn btn-grey btn-lg">Terug</a></p>
				<input type="hidden" name="form" value="' . time() . '" />
				
				<p class="clear"><br />Nog geen account? <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/register/">Registeer nu snel!</a><br />
				<a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/reset-password/">'.$lang['FORGOT_PASSWORD'].'</a></p>
		  
			</fieldset>
		</form>
		
        </div>';
            }
        }
    } else {
        header('Location: ' . $settings['site_url'] . '');
    }
	?>		

	</div>
</div>
	<?php
$_no_footer = true;
require_once("inc/footer.inc.php");
?>