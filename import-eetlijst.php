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
?>
    <script type="text/javascript">
        var RecaptchaOptions = {
            theme : 'clean',
            tabindex : 6
        };
    </script>
	<div class="normal-content">
	<div class="pure-g">
<?php
$loginform = true;
if (Authentication_Controller::IsAuthenticated()) {
    $user = Authentication_Controller::GetAuthenticatedUser();
    if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
        if(isset($_POST['name']) && isset($_POST['saldo']) && isset($_POST['email']) && isset($_POST['group_name'])){
            $register = true;
			$loginform = false;
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
                    $error['email'] = 'Dubbele email adressen zijn niet toegestaan.';
                    $register = false;
                    break;
                }
            }

            foreach($_POST['email'] as $e){
                if($e == $user->email){
                    $userEmail = true;
                }
                if(!filter_var(strtolower($e), FILTER_VALIDATE_EMAIL)){
                    $error['email1'] = 'Er zijn ongeldige email adressen ingevuld.';
                    $register = false;
                }
                if($e == null || $e == ''){
                    $error['email2'] = 'Niet ieder email adres is ingevuld.';
                    $register = false;
                }
            }

            if(!$userEmail){
                $error[] = 'Je kunt geen groep aanmaken zonder jezelf, een van de email adressen moet overeenkomen met jou email adres die bij Monipal bekend is.';
                $register = false;
            }

            $som = 0;
            foreach($_POST['saldo'] as $s){
                $s = str_replace(',', '.', $s);
                $som = $som + $s;
            }

			if($som < -0.02 || $som > 0.02){
				//Wanneer het verschil meer dan 2 cent is het verdelen over de groepsleden zodat het wel klopt
				$som_per_user = round($som / count($_POST['saldo']),2);

				$count = count($_POST['saldo']);
				for ($i = 0; $i < $count; $i++) {
					if(round($som,2) != 0.00){ // Zolang de centen niet opzijn ze bij elke user herberekenen
						$s = str_replace(',', '.', $_POST['saldo'][$i]);
						$_POST['saldo'][$i] = $s - $som_per_user;
						$som = $som - $som_per_user;
					}
				}
			}


            //if($som < -0.02 || $som > 0.02){               
                //$error[] = 'De ingevulde saldo\'s liggen opgetelt te ver van 0,00 af helaas kunnen we deze saldo\'s niet overnemen. De saldo\'s opgetelt zijn samen '.$som.' dit mag minimaal -0,02 en maximaal 0,02 zijn. Dit is nodig om ons systeem goed te laten werken en om jullie een zo nauwkeurig mogelijke saldo tussenstand te bieden.';
                //$register = false;
            //}

            if($register){
                $groupId = $DBObject->AddGroupNoUsers($_POST['group_name']);
                $group = new Group;
                $group = $group->GetGroupById($groupId);
                $count = 0;
                $cost = new Cost();
                foreach($_POST['email'] as $e){
                    // checks if email exists in db
                    if ($user->CheckEmail($e))
					{
                        // add user to group
                        $userId = $DBObject->GetUserIdByEmail($e);

                        $DBObject->AddUserToGroup($userId, $groupId);

                        $amountUser = str_replace(',', '.', $_POST['saldo'][$count]);
                        $date = date("Y-m-d");
                        $costId = $cost->AddCost($amountUser,'Import',$userId,$group,2,$date);
                    }
                    else
					{
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
				die();
            }
            else{
                $import = new Import();
                $import->saldos = $_POST['saldo'];
                $import->emails = $_POST['email'];
                $import->users = $_POST['name'];
            }
        }
		elseif(isset($_POST['username']) && isset($_POST['password'])){
			$resp = recaptcha_check_answer ($privatekey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]);

			if (!$resp->is_valid) {//
				// What happens when the CAPTCHA was entered incorrectly
				$loginform = true;
				$error[] = $lang['INCORRECT_CAPTCHA'];
			}
			else{
				$import = new Import();				
				if($import->getSaldosEetlijst($_POST['username'],$_POST['password']))
				{
					$loginform = false;
				}
				else
				{
					$error[] = 'Inlognaam of wachtwoord incorrect.';
				}
			}
        }
    }
    if ($loginform)
	{
        echo '
		<div class="l-box pure-u-3-4">
            <h1>Importeer Eetlijst.nl</h1>';
		if(isset($error)){
            if($error != null)
            {
                echo '<div class="error-bar">';
                foreach($error as $string)
                    echo ' - '.$string.'<br />';
                echo '</div>';
            }
        }
        echo '
			<form method="post" action="">
				<span>
					<label>Login naam</label>
					<input name="username" type="text" value="'.(isset($_POST['username']) ? $_POST['username'] : '').'" tabindex="1" />
				</span>

				<span class="clear">
					<label>'.$lang['PASSWORD'].'</label>
					<input name="password" type="password" value="'.(isset($_POST['password']) ? $_POST['password'] : '').'" tabindex="2" />
				</span>

				<span class="clear">'.recaptcha_get_html($publickey).'</span>

				<span class="clear">
					<input type="submit" class="submit_btn" value="'.$lang['LOGIN'].'" tabindex="3">
				</span>
				<input type="hidden" name="form" value="' . time() . '" />
			</form>
        </div>';
    }
    else
	{
        if(isset($error))
		{
            if($error != null)
            {
                echo '<div class="l-box pure-u-1 error-bar">';
                foreach($error as $string)
                    echo ' - '.$string.'<br />';
                echo '</div>';
            }
        }
        echo '
		<div class="l-box pure-u-3-4">
            <h1>Importeer Eetlijst.nl</h1>
			<p>Het kan zijn dat de geïmporteerde saldo\'s een paar cent verschillen omdat de berekening bij eetlijst niet altijd 100% klopt.</p>
			<form method="post" action="">
				<span>
					<label>'.$lang['GROUPOFNAME'].'</label>
					<input type="text" name="group_name" value="'.(isset($_POST['group_name']) ? $_POST['group_name'] : '').'" tabindex="1">
				</span>
				<span class="clear">
					<table>
					<tr><th>Naam</th><th>Saldo</th><th>Emailadres</th></tr>
				   ';
			$count = 0;
			foreach($import->users as $u)
			{
				echo '<tr>
					<td><input name="name[]" type="text" value="'.(isset($_POST['name'][$count]) ? $_POST['name'][$count] : trim($u)).'" /></td>
					<td><input name="saldo[]" type="text" value="'.(isset($_POST['saldo'][$count]) ? $_POST['saldo'][$count] : trim($import->saldos[$count])).'" /></td>
					<td><input name="email[]" type="text" value="'.(isset($_POST['email'][$count]) ? $_POST['email'][$count] : (trim(strtoupper($u)) == strtoupper($user->firstName) ? $user->email : '')).'" tabindex="'.($count + 2).'" /></td>
					</tr>
				';
				$count++;
			}
				echo '</table></span>				
				<span class="clear">
					<input type="submit" class="submit_btn" value="Importeer" tabindex="'.($count + 3).'">
				</span>
				<input type="hidden" name="form" value="' . time() . '" />
			</form>
        </div>';
    }
} else {
    header('Location: ' . $settings['site_url'] . '');
}
?>
</div>
</div>
<?php
require_once("inc/footer.inc.php");
?>