<?php
ob_start();
require_once("inc/config.inc.php");
$_page_title = $lang['PAGENAME_LOGOUT'];
if(Authentication_Controller::IsAuthenticated())
{
    Authentication_Controller::LogOut();
    header("refresh:2; url=".$settings['site_url'].$langCode.'/', true);
}
else
{
    header ('Location: '.$settings['site_url']);
}

require_once("inc/header.inc.php");
echo '
<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-1">
			<div class="success-bar">'.$lang['YOU_ARE_LOGEDOUT'].'</div>
		</div>
	</div>
</div>';

include_once("inc/footer.inc.php");
?>