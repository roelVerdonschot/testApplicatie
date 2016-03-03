<?php
ob_start();
require_once("inc/config.inc.php");
require_once("inc/header.inc.php");
require_once('inc/recaptchalib.php');

// E-mailadres van de ontvanger
$mail_ontv = 'support@monipal.com';
$publickey = "6Lf_3ekSAAAAAMp1DPqvgzQWU9c3fmjGiRq-5B6G"; // Captcha
$showform = true;

?>
    <script type="text/javascript">
        var RecaptchaOptions = {
            theme : 'clean',
            tabindex : 6
        };
    </script>
<?php
echo '
<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-1">';
// Speciale checks voor naam en e-mailadres
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $showform = false;
    // naam controle
    if (!preg_match('/[ a-zA-Z-]$/', $_POST['naam'])){
        $showform = true;
        $error[] = $lang['FORGOT_NAME'];
    }

    // e-mail controle
    if (function_exists('filter_var') && !filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)){
        $showform = true;
        $error[] = $lang['ERROR_EMAIL_NOT_CORRECT'];
    }

    // antiflood controle
    if (!empty($_SESSION['antiflood']))
    {
        $seconde = 20; // 20 seconden voordat dezelfde persoon nog een keer een e-mail mag versturen
        $tijd = time() - $_SESSION['antiflood'];
        if($tijd < $seconde){
            $showform = true;
            $error[] = $lang['ANTI_SPAM'];
        }
    }

    if(empty($_POST['bericht'])){
        $showform = true;
        $error[] = $lang['FORGOT_MESSAGE'];
    }

    $privatekey = "6Lf_3ekSAAAAAIhi-mgRWSEYpPNjMidUdS-WxHws";
    $resp = recaptcha_check_answer ($privatekey,
        $_SERVER["REMOTE_ADDR"],
        $_POST["recaptcha_challenge_field"],
        $_POST["recaptcha_response_field"]);

    if (!$resp->is_valid) {
        // What happens when the CAPTCHA was entered incorrectly
        $showform = true;
        $error[] = $lang['INCORRECT_CAPTCHA'];
    }

    if($showform === false){
        // set datum
        $datum = date('d/m/Y H:i:s');

        $inhoud_mail = "===================================================\n";
        $inhoud_mail .= "Ingevulde contact formulier " . $_SERVER['HTTP_HOST'] . "\n";
        $inhoud_mail .= "===================================================\n\n";

        $inhoud_mail .= "Naam: " . htmlspecialchars($_POST['naam']) . "\n";
        $inhoud_mail .= "E-mail adres: " . htmlspecialchars($_POST['mail']) . "\n";
        $inhoud_mail .= "Bericht:\n";
        $inhoud_mail .= htmlspecialchars($_POST['bericht']) . "\n\n";

        $inhoud_mail .= "Verstuurd op " . $datum . " via het IP adres " . $_SERVER['REMOTE_ADDR'] . "\n\n";

        $inhoud_mail .= "===================================================\n\n";

        // --------------------
        // spambot protectie
        // ------
        // van de tutorial: http://www.phphulp.nl/php/tutorial/beveiliging/spam-vrije-contact-formulieren/340/
        // ------

        $headers = 'From: ' . htmlspecialchars($_POST['naam']) . ' <' . $_POST['mail'] . '>';

        $headers = stripslashes($headers);
        $headers = str_replace('\n', '', $headers); // Verwijder \n
        $headers = str_replace('\r', '', $headers); // Verwijder \r
        $headers = str_replace("\"", "\\\"", str_replace("\\", "\\\\", $headers)); // Slashes van quotes

        $_POST['onderwerp'] = str_replace('\n', '', $_POST['onderwerp']); // Verwijder \n
        $_POST['onderwerp'] = str_replace('\r', '', $_POST['onderwerp']); // Verwijder \r
        $_POST['onderwerp'] = str_replace("\"", "\\\"", str_replace("\\", "\\\\", $_POST['onderwerp'])); // Slashes van quotes

        if (mail($mail_ontv, $_POST['onderwerp'], $inhoud_mail, $headers))
        {
            Email_Handler::mailContactThanks($_POST['mail']);
            // zorg ervoor dat dezelfde persoon niet kan spammen
            $_SESSION['antiflood'] = time();

            echo '<h1>'.$lang['CONTACT_FORM_SEND'].'</h1>

      '.$lang['CONTACT_SEND_INFO'].'</div></div></div>';
        }
        else
        {
            $showform = true;
            $error[] =  $lang['CONTACT_FORM_NOT_SEND'].'<br />'.$lang['CONTACT_NOT_SEND_INFO'];
        }
    }
}
if($showform){

    // HTML e-mail formlier
    if(isset($error)){
        echo '<div class="error-bar">';
        foreach ($error as $r){
            echo '- '.$r.'<br />';
        }
        echo '</div>';
    }
    echo '<h1>'.$lang['CONTACT'].'</h1>
<form name="contactForm" method="post" action="' . $_SERVER['REQUEST_URI'] . '">

<span>
    <label class="desc" id="title1" for="Field1">'.$lang['NAME'].'<span class="req">*</span></label>
    <input id="naam" name="naam" type="text" class="field text fn" value="' . (isset($_POST['naam']) ? htmlspecialchars($_POST['naam']) : '') . '" size="8" tabindex="1" />
</span>
<span class="clear">
    <label class="desc" id="title3" for="Field3">'.$lang['EMAILADRES'].'<span class="req">*</span></label>
    <input id="mail" name="mail" type="email" class="s200" value="' . (isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : '') . '" tabindex="3" onblur="if(this.value==\'\'){blurV(\'email\')}else{blurD(\'email\')}" />
</span>

<span class="clear">
    <label class="desc" id="title4" for="Field4">'.$lang['SUBJECT'].'</label>
    <input id="onderwerp" name="onderwerp" type="text" class="field text medium" value="' . (isset($_POST['onderwerp']) ? htmlspecialchars($_POST['onderwerp']) : '') . '" maxlength="555" tabindex="4">
</span>

<span class="clear">
    <label class="desc" id="title5" for="Field5">'.$lang['MESSAGE'].'<span class="req">*</span></label>
    <textarea id="bericht" name="bericht" class="field textarea small" rows="10" cols="50" tabindex="5" onkeyup="">' . (isset($_POST['bericht']) ? htmlspecialchars($_POST['bericht']) : '') . '</textarea>
</span>

<span class="clear">'.
        recaptcha_get_html($publickey)
        .'</span>

<span class="clear">
    <input id="saveForm" name="saveForm" class="btn btn-red" type="submit" value="'.$lang['SEND'].'" onmousedown="doSubmitEvents();">
</span>
</form>
</div>
</div>
</div>';
}

include_once("inc/footer.inc.php");
?>