<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_ACTIVATION'];

$activationcode = $_GET['ac'];
$user = User::getUserByAC($activationcode);

if($user == null)
{
	require_once("inc/header.inc.php");		
	echo '
	<div class="normal-content">
			<div class="pure-g">
				<div class="l-box pure-u-1">
					<div class="error-bar">Account niet gevonden of al geactiveerd.</div>
					<p><a href=" '.$settings['site_url'].'login">'.$lang['CLICKHERE'].'</a> '.$lang['TO_LOGIN'].'</p>
				</div>
			</div>
		</div>';
	require_once("inc/footer.inc.php");
}
elseif($user->terms_accepted == null && !isset($_POST["accept"]))
{
	require_once("inc/header.inc.php");		
	echo '
	<div class="normal-content">
		<div class="pure-g">
			<div class="l-box pure-u-1">
			
			<form method="post" action="">
				<p><label class="choice">Door hieronder op "accepteren &amp; activeren" te drukken ga je akkoord met de <a href="'.$settings['site_url'].$langCode.'/terms-and-conditions/" target="_blank">'.$lang['TERMS_AND_CONDITIONS'].'</a> .</label></p>
				<p><button type="submit" tabindex="7" class="btn btn-red btn-lg">Accepteren &amp; activeren</button></p>
				<input type="hidden" name="form" value="' . time() . '" />
				<input type="hidden" name="accept" value="yes" />
			</form>				
			</div>
		</div>
	</div>';
	require_once("inc/footer.inc.php");
}
else
{
	if(isset($_POST["accept"]))
	{
		$terms_accepted = $DBObject->UpdateTermsAcceptedByUserId($user->id);
	}
	User::activateUser($activationcode);

	Email_Handler::activationConfirmation($user->email,$lang['ACTIVATION'].' Monipal', $user->getFullName());
	header("refresh:4; url=".$settings['site_url'].$lang['_LANG_CODE'].'/login/', true);

	require_once("inc/header.inc.php");
	echo '        
	<div class="normal-content">
		<div class="pure-g">
			<div class="l-box pure-u-1">
				<div class="notification-bar">'.$lang['ACCOUNT_ACTIVE'].'</div>
				<p><a href=" '.$settings['site_url'].'login">'.$lang['CLICKHERE'].'</a> '.$lang['TO_LOGIN'].'</p>
			</div>
		</div>
	</div>';
	require_once("inc/footer.inc.php");
}
?>
