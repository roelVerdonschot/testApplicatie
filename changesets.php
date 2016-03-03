<?php
ob_start();
require_once("inc/config.inc.php");
require_once("inc/header.inc.php");

$changeSets =  $DBObject->GetChangeSets($lang['_LANG_CODE']);
echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-1">
<h1>'.$lang['CHANGESETS'].'</h1>
<table>
<tr><th>'.$lang['VERSION'].'</th><th>'.$lang['DESCRIPTION'].'</th><th>'.$lang['DATE'].'</th>';

foreach($changeSets as $c){
    $desc =  explode("|",$c[1]);
    $s = '';
    foreach($desc as $d){
        $s = $s.'- '.$d.'<br>';
    }
    echo '<tr><td>'.$c[0].'</td><td>'.$s.'</td><td>'.$c[2].'</td></tr>';
}

echo '</table></div>';

echo '	</div>';
echo '</div>';

include_once("inc/footer.inc.php");
?>