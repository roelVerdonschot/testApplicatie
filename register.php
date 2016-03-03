<?php
/**
 * User: Pascal Worek
 * Date: 9-7-13
 * Time: 12:25
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_REGISTER'];
$_page_description = $lang['DESCRIPTION_REGISTER'];
$_page_keywords = $lang['KEYWORDS_REGISTER'];
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
if($settings['site_state'] == SITE_STATE_BETA)
{
    echo '<div class="notification-bar">It\'s not possible to register in BETA mode.</div>';
}
else
{
    if(!Authentication_Controller::IsAuthenticated()) {
        echo '<div id="login-box">
            <h1>'.$lang['NEW_ACCOUNT'].'</h1>
            <p>'.$lang['MAKE_NEW_ACCOUNT'].'</p>';
        $error = null;
        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
            $showForm = true;
        } else {
            $user = new User();
            $showForm = false;
            // everything needs to be filled in
            //var_dump($_POST['pass'] . $_POST['e-mail'] .  $_POST['name']);
            if ( empty($_POST['e-mail']) || empty($_POST['pass']) || empty($_POST['name'])) {
                $error .= ' - '.$lang['ERROR_FIELD'] .'<br />';
                $showForm = true;
            }
            else{

                /*if(empty($_POST['e-mail'])){
                    $error .= ' - '.$lang['ERROR_FIELD'] .'<br />';
                    $showForm = true;
                }

                if(empty($_POST['name'])){
                    $error .= ' - '.$lang['ERROR_FIELD'] .'<br />';
                    $showForm = true;
                }*/

                // passwords should match
                if ($_POST['pass'] != $_POST['pass2']) {
                    $error .= ' - '.$lang['ERROR_PASSWORD'] .'<br />';
                    $showForm = true;
                }

                // checks password rules
                if (strlen( $_POST['pass'] ) < 6)
                {
                    $error .= ' - '.$lang['ERROR_PASSWORD_RULES'] .'<br />';
                    $showForm = true;
                }

                // checks if the given emailadres already exists in the database
                if ($user->CheckEmail($_POST['e-mail'])) {
                    $error .= ' - '.$lang['ERROR_EMAIL'] .'<br />';
                    $showForm = true;
                }

                if(!isset($_POST['accept-terms']) || $_POST['accept-terms'] != 'accept')
                {
                    $error .= ' - '.$lang['ERROR_TERMS'] .'<br />';
                    $showForm = true;
                }
            }


            if($showForm === false)
            {
                if(filter_var(strtolower($_POST['e-mail']), FILTER_VALIDATE_EMAIL)){
                    // sha1 email=activationcode
                    $user->AddUser($_POST['name'], strtolower($_POST['e-mail']), Bcrypt::hash($_POST['pass']), sha1($_POST['e-mail']), $langCode, true);
                    // sends the activation email
                    Email_Handler::mailActivationRequest($_POST['e-mail'],$lang['SUBJECT_REG'],$_POST['name'],sha1($_POST['e-mail']));

                    if(isset($_GET['gid'])){
                        $userId = $user->GetUserIdByEmail(strtolower($_POST['e-mail']));
                        $groupId = $_GET['gid'];

                        if($user->DeleteUserFromInvite(strtolower($_POST['e-mail']),$groupId, $userId)){
                            // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                            $lb = new Logbook(null, $groupId, $userId, null, null, null, null, 'UA', strtolower($_POST['e-mail']));
                            $DBObject->AddLogbookItem($lb);
                        }
                    }
                    echo '<div class="success-bar">'.$lang['CONFIRMATION'] .'
                        <br /><a href=" '.$settings['site_url'].'login">'.$lang['CLICKHERE'].'</a> '.$lang['TO_LOGIN'].'</div>';
                }
                else{
                    $error.= ' - '.$lang['ERROR_EMAIL_NOT_CORRECT'].'<br />';
                }

            }
        }
        if ($showForm) {
            if(!empty($error))
            {
                echo '<div class="error-bar">'.$error.'</div>';
            }
            echo '
            <form method="post" action="">			
			<fieldset>
				<p><input name="name" placeholder="'.$lang['NAME'].'" type="text" x-autocompletetype="given-name" autofocus="autofocus" value="'.(isset($_POST['name']) ? htmlspecialchars($_POST['name']) : "").'" tabindex="2" /></p>
				<p><input type="email" x-autocompletetype="email" name="e-mail" value="'.(isset($_POST['e-mail']) ? htmlspecialchars($_POST['e-mail']) : (isset($_GET['e']) ? htmlspecialchars($_GET['e']) : "")).'" tabindex="3" placeholder="'.$lang['EMAILADRES'].'"  ></p>
				<p><input type="password" name="pass" tabindex="4" placeholder="'.$lang['PASSWORD'].'" ></p>
				<p><input type="password" name="pass2" tabindex="5" placeholder="'.$lang['REPEAT_PASSWORD'].'" ></p>
				<p><input name="accept-terms" type="checkbox" tabindex="6"  value="accept"> <label class="choice">'.$lang['CHECK_BOX_TO_ACCEPT'].' <a href="'.$settings['site_url'].$langCode.'/terms-and-conditions/" target="_blank">'.$lang['TERMS_AND_CONDITIONS'].'</a> .</label></p>
				<p><button type="submit" tabindex="7" class="btn btn-red btn-lg">'.$lang['REGISTER'].'</button></p>
				<p><a href="'.$settings['site_url'].'" class="btn btn-grey btn-lg">Terug</a></p>
				<input type="hidden" name="form" value="' . time() . '" />
			</fieldset>

            </form>';
        }
        echo '</div>';

    } else {
        // redirect user to index if already logged in
        header('Location: ' . $settings['site_url']);
    }
}
?>		

	</div>
</div>
	<?php
$_no_footer = true;
include_once("inc/footer.inc.php");
?>