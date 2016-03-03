<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_MYACCOUNT'];
require_once("inc/header.inc.php");

if(Authentication_Controller::IsAuthenticated())
{
	$user = Authentication_Controller::GetAuthenticatedUser();
    if($user->dateOfBirth != null){
        list($yyyy,$mm,$dd) = explode('-',$user->dateOfBirth);
        $date = $dd.'-'.$mm.'-'.$yyyy;
    }
    else{
        $date = '-';
    }
echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4"><table class="index-table">
        <tr><th class="onepx">'.$lang['ACCOUNTDATA'].'</th><th></th></tr>
	    <tr><td class="balance-column"><label>'.$lang['FIRSTNAME'].'</label></td><td><label>'.$user->firstName.'</label></td></tr>
	    <tr><td class="balance-column datacelltwo"><label>'.$lang['LASTNAME'].'</label></td><td class="datacelltwo"><label>'.($user->surname != null ? $user->surname : '-').'</label></td></tr>
	    <tr><td class="balance-column"><label>'.$lang['ADDRESS'].'</label></td><td class=""><label>'.($user->address != null ? $user->address : '-').'</label></td></tr>
		<tr><td class="balance-column datacelltwo"><label>'.$lang['POSTAL'].'</label></td><td class=" datacelltwo"><label>'.($user->zipcode != null ? $user->zipcode : '-').'</label></td></tr>
		<tr><td class="balance-column"><label>'.$lang['CITY'].'</label></td><td class=""><label>'.($user->city != null ? $user->city : '-').'</label></td></tr>
		<tr><td class="balance-column datacelltwo"><label>'.$lang['LAND'].'</label></td><td class="datacelltwo"><label>'.($user->country != null ? getCountry($user->country) : '-').'</label></td></tr>
		<tr><td class="balance-column"><label>'.$lang['DATEOFBIRTH'].'</label></td><td class=""><label>'.$date.'</label><br>
		<tr><td class="balance-column datacelltwo"><label>'.$lang['EDUCATION'].'</label></td><td class="datacelltwo"><label>'.($user->school != null ? $user->school : '-').'</label></td></tr>
		<tr><td class="balance-column"><label>'.$lang['PREFERENCESTYLE'].'</label></td><td class=""><label>'.($user->preferredLanguage != null ? getLang($user->preferredLanguage) : '-').'</label></td></tr>
		</table> <br />';
    // <tr><td class="balance-column"><label>'.$lang['BANKNUMMER'].'</label></td><td class=""><label>'.($user->bankAccount != null ? $user->bankAccount : '-').'</label></td></tr>
    $invites = $DBObject->GetInvites($user->email);
    if(isset($invites)){
        echo '<table class="index-table">
        <tr><th>'.$lang['GROUP_INVITATIONS'].'</th><th></th></tr>';
        foreach($invites as $i){
            echo '<tr>
            <td>'.$DBObject->GetUserNameById($i[2]).' '.$lang['HAS_INVITED_YOU'].' '.$DBObject->GetGroupName($i[1]).'</td>
            <td class="onepx" style="white-space: nowrap;"><input type="button" class="secundaire-btn" value="'.$lang['ACCEPT'].'" onclick="window.location.href=\''.$settings['site_url'].'accept-invite.php?inv='.$i[0].'&gid='.$i[1].'\'"/> <input type="button" class="alt_btn" value="'.$lang['IGNORE'].'" onclick="window.location.href=\''.$settings['site_url'].'accept-invite.php?del='.$i[0].'\'"/></td>
            </tr>
            ';
        }
        echo '</table>';
    }
    echo '</div>
    <aside class="l-box pure-u-1-4">
    <a href="'.$settings['site_url'].'settings/" class="buttonExtra">'.$lang['EDIT_ACCOUNT'].'</a>
    <a href="'.$settings['site_url'].'settings-login/" class="buttonExtra">'.$lang['PAGENAME_EDIT_LOGIN'].'</a>
    <h2>&nbsp;</h2>
        <div class="box">
            <p>'.$lang['NEW_FREE_GROUP'].'</p>
            <a href="'.$settings['site_url'].'new-group/" class="btn btn-white">'.$lang['NEW_GROUP'].'</a>';
    if($user->surname == null || $user->address == null || $user->zipcode == null || $user->city == null){
        echo ' <a href="'.$settings['site_url'].'settings/" class="btn btn-white">'.$lang['UPDATE_ACCOUNT'].'</a>';
    }
        echo '</div>
    </aside>
	</div>
</div>';
}
else
{
    header ('Location: '.$settings['site_url'].'login');
}
include_once("inc/footer.inc.php");
?>