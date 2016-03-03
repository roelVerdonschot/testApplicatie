<?php
/**
 * User: Roel Verdonschot
 * Date: 12-7-13
 * Time: 12:09
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_IMPORT'];
require_once("inc/header.inc.php");
require_once('inc/recaptchalib.php');
$publickey = "6Lf_3ekSAAAAAMp1DPqvgzQWU9c3fmjGiRq-5B6G"; // you got this from the signup page
$privatekey = "6Lf_3ekSAAAAAIhi-mgRWSEYpPNjMidUdS-WxHws";

$loginform = true;
?>
   <script type="text/javascript">
        var RecaptchaOptions = {
            theme : 'clean',
            tabindex : 6
        };
    </script>
	<?php
echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">';
if (Authentication_Controller::IsAuthenticated()) {
    $user = Authentication_Controller::GetAuthenticatedUser();
    if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
        if(isset($_POST['name']) && isset($_POST['saldo']) && isset($_POST['email']) && isset($_POST['group_name'])){
            $register = true;

            if($_POST['group_name'] == null || $_POST['group_name'] == ''){
                $error[] = 'Vul een groepsnaam in.';
                $register = false;
            }

            $userEmail = false;
            $tot = COUNT($_POST['email']);
            $tot = ($tot - 1);
            for ($i = 0; $i <= $tot; $i++) {
                $som = 0;
                foreach($_POST['email'] as $e){
                    if($_POST['email'][$i] == $e){
                        $som++;
                    }
                }
                if($som > 2){
                    $error[] = 'Dubbelen email adressen zijn niet toegestaan.';
                    $register = false;
                    break;
                }
            }

            foreach($_POST['email'] as $e){
                if($e == $user->email){
                    $userEmail = true;
                }
                if(!filter_var(strtolower($e), FILTER_VALIDATE_EMAIL)){
                    $error[] = 'Er zijn ongeldige email adressen ingevult.';
                    $register = false;
                }
                if($e == null || $e == ''){
                    $error[] = 'Niet ieder email adres is ingevult.';
                    $register = false;
                }
            }

            if(!$userEmail){
                $error[] = 'Je kunt geen groep aanmaken zonder jezelf, een van de email adressen moet overeenkomen met je email adres die bij monipal bekent is.';
                $register = false;
            }

            $som = 0;
            foreach($_POST['saldo'] as $s){
                $s = str_replace(',', '.', $s);
                $som = $som + $s;
            }

            if($som < -0.02 || $som > 0.02){
                $error[] = 'De ingevulde saldo\'s liggen opgetelt te ver van 0,00 af helaas kunnen we deze saldo\'s niet overnemen. De saldo\'s opgetelt zijn samen '.$som.' dit mag minimaal -0,02 en maximaal 0,02 zijn. Dit is nodig om ons systeem goed te laten werken en om jullie een zo nauwkeurig mogelijke saldo tussenstand te bieden.';
                $register = false;
            }

            if($register){
                $groupId = $DBObject->AddGroupNoUsers($_POST['group_name']);
                $group = new Group;
                $group = $group->GetGroupById($groupId);
                $count = 0;
                $cost = new Cost();
                foreach($_POST['email'] as $e){
                    // checks if email exists in db
                    if ($user->CheckEmail($e)) {
                        // add user to group
                        $userId = $DBObject->GetUserIdByEmail($e);

                        $DBObject->AddUserToGroup($userId, $groupId);

                        $amountUser = str_replace(',', '.', $_POST['saldo'][$count]);
                        $date = date("Y-m-d");
                        $costId = $cost->AddCost($amountUser,'Import',$userId,$group,2,$date);
                    }
                    else{
                        $randomPass = $user->randomPassword();
                        // sha1 email=activationcode
                        $userId = $DBObject->AddUser($_POST['name'][$count], strtolower($e), Bcrypt::hash($randomPass), sha1($e), 'nl',false);

                        $DBObject->AddUserToGroup($userId, $groupId);

                        $amountUser = str_replace(',', '.', $_POST['saldo'][$count]);
                        $date = date("Y-m-d");
                        $costId = $cost->AddCost($amountUser,'Import',$userId,$group,2,$date);

                        // sent activatie mail met wachtwoord
                        Email_Handler::ActivationAndPassword($e,$lang['SUBJECT_REG'],$_POST['name'][$count],sha1($e),$randomPass,$user->firstName);
                    }
                    $count++;
                }
                $DBObject->UpdateGroupUserSaldo($group->id);
                header ('Location: '.$settings['site_url'].'group/'.$groupId);
            }
        }
        if(isset($_POST['username']) && isset($_POST['password'])){
            $captcha = false;
            if(isset($_POST['name']) && isset($_POST['saldo']) && isset($_POST['email']) && isset($_POST['group_name'])){
                $captcha = true;
            }
            else{
                $resp = recaptcha_check_answer ($privatekey,
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["recaptcha_challenge_field"],
                    $_POST["recaptcha_response_field"]);

                if (!$resp->is_valid) {
                    // What happens when the CAPTCHA was entered incorrectly
                    $loginform = true;
                    $error[] = $lang['INCORRECT_CAPTCHA'];
                }
                else{
                    $captcha = true;
                }
            }

            if(true){//$captcha
                $import = new Import();
                $import->getSaldosWieBetaaltWat($_POST['username'],$_POST['password']);
                if($import->WBWbankList != null){
                    $loginform = false;
                }
                else{
                    $error[] = 'Inlognaam of wachtwoord incorrect.';
                }
            }
        }
    }
    if ($loginform) {
        if(isset($error)){
            if($error != null)
            {
                echo '<div class="error-bar">';
                foreach($error as $string)
                    echo ' - '.$string.'<br />';
                echo '</div>';
            }
        }

        echo '<h1>Importeer WieBetaaltWat.nl</h1>
		<form method="post" action="">
		    <span>
                <label>Login naam</label>
                <input name="username" type="text" value="'.(isset($_POST['username']) ? $_POST['username'] : '').'" tabindex="1" />
            </span>

            <span class="clear">
                <label>'.$lang['PASSWORD'].'</label>
                <input name="password" type="password" value="'.(isset($_POST['password']) ? $_POST['password'] : '').'" tabindex="2" />
            </span>

            <span class="clear">'.
            recaptcha_get_html($publickey)
            .'</span>

			<span class="clear">
                <input type="submit" class="submit_btn" value="'.$lang['LOGIN'].'" tabindex="3">
            </span>
			<input type="hidden" name="form" value="' . time() . '" />
		</form>';
    }
    else{
        if(isset($error)){
            if($error != null)
            {
                echo '<div class="error-bar">';
                foreach($error as $string)
                    echo ' - '.$string.'<br />';
                echo '</div>';
            }
        }
        $bankList = $import->WBWbankList;
        echo '
             <h1>Importeer WieBetaaltWat.nl</h1>
             <span>
		    <label>Kies een group</label>
		        <select id="wbwGroupsSelector" >';
        $i = 1;
        foreach($bankList as $b){
            echo '<option value="wbw'.$i.'">'.$b->ListName.'</option>';
            $i++;
        }
        echo '</select>
		    </span>';
        $i =1;
        foreach($bankList as $b){
            echo '<div class="wbwGroups" id="wbw'.$i.'"><form method="post" action="">
		<input name="username" type="hidden" value="'.(isset($_POST['username']) ? $_POST['username'] : '').'" />
		<input name="password" type="hidden" value="'.(isset($_POST['password']) ? $_POST['password'] : '').'" />
            <span class="clear">
			    <label>'.$lang['GROUPOFNAME'].'</label>
			    <input type="text" name="group_name" value="'.$b->ListName.'" tabindex="1">
		    </span>
		    <span class="clear">
		        <label>
		        Naam &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		        Saldo &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		        Email-adres
		        </label>
		    </span>';
            $count = 0;
            foreach($b->UserNames as $u){
                if($u != null){
                    echo '<span class="clear">
                <input name="name[]" type="text" value="'.trim($u).'" />
                <input name="saldo[]" type="text" value="'.trim($b->UserAmounts[$count]).'" />
                <input name="email[]" type="text" value="'.trim($b->UserEmails[$count]).'" tabindex="'.($count + 2).'" />
            </span>';
                }
                $count++;
            }
            echo '<span class="clear">
                <input type="submit" class="submit_btn" value="Importeer" tabindex="'.($count + 3).'">
            </span>
			<input type="hidden" name="form" value="' . time() . '" />
		</form></div>';
            $i++;
        }
    }
} else {
    header('Location: ' . $settings['site_url'] . '');
}
?>
		</div>
	</div>
</div>
 <script type="text/javascript">
        var RecaptchaOptions = {
            theme : 'clean',
            tabindex : 6
        };
    $(document).ready(function(){
        $('.wbwGroups').hide();
        $('#wbw1').show();
    });
    $(function() {
        $('#wbwGroupsSelector').change(function(){
            $('.wbwGroups').hide();
            $('#' + $(this).val()).show();
        });
    });
</script><?php
require_once("inc/footer.inc.php");
?>
