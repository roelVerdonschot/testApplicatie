<?php
ob_start();
require_once("inc/config.inc.php");
require_once("inc/header.inc.php");

$faq = $DBObject->GetFAQ($lang['_LANG_CODE']);
echo '
<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-1">
<h1>Frequently Asked Questions</h1>';
if(isset($faq)){
    foreach($faq as $c){
        echo '<span class="clear"><strong>'.$c[0].'</strong><br /><p>'.$c[1].'</p></span>';
    }
    $link = '<a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/contact/">'.$lang['CONTACT_SMALL'].'</a>';
    echo '<span class="clear"><p>'.sprintf($lang['CONTACT_FAQ'], $link).'</p></span>';
}
else{
    echo $lang['FAQ_NOT_AV'];
}

echo '</div>
</div>
</div>';

include_once("inc/footer.inc.php");
?>