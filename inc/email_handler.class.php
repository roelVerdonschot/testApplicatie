<?php
class Email_Handler {

    private static function sendMail($to,$subject,$message)
    {
        // To send HTML mail, the Content-type header must be set
        $headers =  'From: Monipal <noreply@monipal.com>' . "\r\n";
        $headers .= 'Return-Path: Monipal support <support@monipal.com>'. "\r\n";
        $headers .= 'Reply-To: Monipal support <support@monipal.com>'. "\r\n" ;
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Organization: Monipal.com'. "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion(). "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        // Mail it
        return mail($to, $subject, $message, $headers);
    }
	
	private static function getMailFooter()
	{
        global $lang;
		return '<strong>Mobiele apps</strong><br />		
		Wist je dat we ook een app hebben? Voor <strong>iPhone</strong>, <strong>Android</strong> en <strong>Windows Phone</strong> staat er een app in de store die je gratis kan downloaden.<br />
		Voeg nu gemakkelijk nieuwe kosten toe, geef aan of je mee-eet of gaat koken vanavond en zet je taak voor deze week op "gedaan" direct vanuit de app!<br />
		<a href="http://www.monipal.com/apps/" target="_blank">Download hier de Monipal app voor je mobiel!</a><br /><br />		
		
		<strong>Feedback</strong><br />
		We zijn momenteel nog in ontwikkeling waardoor het kan zijn dat je nog een foutje tegenkomt. Daarom hebben we een feedback optie toegevoegd, zowel op de website als in de apps. Wanneer je iets ontdekt horen we dit graag!

		<br /><br />
		Vergeet ook niet om monipal te liken op <a href="https://www.facebook.com/monipalcom" target="_blank">Facebook</a> en/of te volgen op <a href="https://twitter.com/Monipalcom" target="_blank">Twitter</a>.
		Zo blijf je altijd up-to-date bij alle veranderingen op <a href="http://www.monipal.com" target="_blank">www.monipal.com</a>.
		
        <br /><br />
        '.$lang['YOURS_FAITHFULLY'].'<br /><br />

        de Monipal crew';
	}

    public static function activationConfirmation($to,$subject,$name)
    {
        global $lang;
        // message
        $message = self::GenerateEmail($lang['WELCOME'].' '.$name.',',
		'<strong>Je account is succesvol geactiveerd!</strong> Vanaf nu kan je inloggen op de website en app en gratis gebruikmaken van alle functionaliteiten van Monipal.com!<br />
        '.$lang['THANKS_FOR_REGISTERING'].'<br /><br />
		'.self::getMailFooter());

        self::sendMail($to,$subject,$message);
    }

    public static function mailActivationRequest($to,$subject,$name,$activation)
    {
        global $settings,$lang;
        // message
        $message = self::GenerateEmail($lang['WELCOME'].' '.$name.',',
        '<strong>'.$lang['THANKS_FOR_REGISTERING'].'</strong><br />

        '.sprintf($lang['CLICK_HERE_ACTIVATE'],'<a href="'.$settings['site_url'].'activation/'.$activation.'">','</a>').'<br /><br />

        '.self::getMailFooter());

        self::sendMail($to,$subject,$message);
    }
	
	public static function mailActivationReminder($to,$name,$activation)
    {
        global $settings,$lang;
        // message
        $message = self::GenerateEmail($lang['HELLO'].' '.$name.',','

		Onlangs heeft u zich geregistreed bij Monipal.com.<br />
		Het is ons opgevallen dat je je account nog niet hebt geactiveerd.<br /><br />

		Activeer nu je account door op onderstaande link te klikken.<br /><br />
		<a href="'.$settings['site_url'].'activation/'.$activation.'">'.$settings['site_url'].'activation/'.$activation.'</a><br /><br />

        '.self::getMailFooter());

        return self::sendMail($to,"Monipal account activatie herinnering",$message);
    }

    public static function ActivationAndPassword($to,$subject,$name,$activation,$password,$friend)
    {
        global $settings,$lang;
        // message
        $message = self::GenerateEmail($lang['WELCOME'].' '.$name.',',
        $friend.' heeft jullie groep geïmporteerd bij Monipal.<br /><br />

	   <strong>Je login gegevens</strong><br />
	   Om het je makkelijk te maken hebben we alvast een account voor je aangemaakt.<br />
	   Je wachtwoord is: '.$password.'.<br /> 
	   We adviseren je om deze te wijzigen na het inloggen.<br />'.
	   sprintf($lang['CLICK_HERE_ACTIVATE'],'<a href="'.$settings['site_url'].'activation/'.$activation.'">','</a>').'<br /><br />'.self::getMailFooter());

        self::sendMail($to,$subject,$message);
    }

    public static function mailBodyInviteToGroup($to,$subject,$name,$group,$groupId)
    {
        global $settings,$lang;
        // message
        $message =  self::GenerateEmail($lang['HELLO'].' '.$to.',',
            '<strong>'.$group.'</strong><br />'.
        $name.' '.$lang['WANT_INVITE'].' '.$group.'<br />
        '.sprintf($lang['CLICK_HERE_ACCEPT_INVATE'],'<a href="'.$settings['site_url'].'accept-invite/'.$to.'/'.$groupId.'/">','</a>').'<br /><br />

        '.self::getMailFooter());

        self::sendMail($to,$subject,$message);
    }
	
	public static function mailInviteToGroupReminder($to,$name,$group,$groupId)
    {
        global $settings,$lang;
        // message
        $message =  self::GenerateEmail($lang['HELLO'].' '.$to.',',
		'Onlangs ben je uitgenodigd door '.$name.' voor de groep '.$group.' op monipal.com.<br />
		Het is ons opgevallen dat deze uitnodiging nog niet hebt geaccepteerd.<br /><br />

		Accepteer de uitnodiging van '.$name.' door op onderstaande link te klikken.<br /><br />

        <a href="'.$settings['site_url'].'accept-invite/'.$to.'/'.$groupId.'/">'.$settings['site_url'].'accept-invite/'.$to.'/'.$groupId.'/</a><br /><br />

		Wil deze uitnodiging weigeren of verwijderen klik dan op onderstaande link.<br /><br />
		
		<a href="'.$settings['site_url'].'refuse-invite/'.$to.'/'.$groupId.'/">'.$settings['site_url'].'refuse-invite/'.$to.'/'.$groupId.'/</a><br /><br />
		
        '.self::getMailFooter());

        self::sendMail($to,"Reminder",$message);
    }
	
	
    public static function mailBodyResetPass($to, $subject, $name, $code)
    {
        global $settings,$lang;
        // message
        $message = self::GenerateEmail($lang['HELLO'].' '.$name.',',
        $lang['RECEIVING_EMAIL_FOR_PASS_CHANGE'].'<br />
        '.sprintf($lang['CLICK_FOR_RESET_PASS'],'<a href="'.$settings['site_url'].'reset-password/'.$code.'">','</a>').'<br /><br />

        '.$lang['YOURS_FAITHFULLY'].'<br /><br />

            '.$lang['SITE_NAME'].'');

        self::sendMail($to,$subject,$message);

    }

    public static function mailEmailChanged($to, $name)
    {
        global $lang;
        // message
        $message = self::GenerateEmail($lang['HELLO'].' '.$name.',', '
        <p>'.$lang['EMAIL_CHANGED_TO'].' '.$to.'</p>'.self::getMailFooter());

        self::sendMail($to,$lang['SUBJECT_EMAILCHANGE'],$message);
    }

    public static function mailTaskReminder($to, $name, $taskName)
    {
        global $settings,$lang;
        // message
        $message = self::GenerateEmail($lang['HELLO'].' '.$name.',','
        <p>'.$lang['TASK_THIS_WEEK'].' '.$taskName.'. <br /><a href="'.$settings['site_url'].'">'.$lang['GO_TO_MONIPAL'].'</a></p>'.self::getMailFooter());;

        self::sendMail($to,'Monipal '.$lang['TASK_REMINDER'].' '.$taskName,$message);
    }

    public static function mailFeedback($from, $feedback, $path)
    {
        global $settings;
        // message
        $message = self::GenerateEmail("Feedback",'
        <p><strong>Email:</strong> '.htmlspecialchars($from).'<br />
        <strong>Locatie:</strong> '.$path.'<br />
        <strong>Feedback:</strong><br />
        '.htmlspecialchars($feedback).'</p>');
        return self::sendMail($settings['feedback_emailaddress'],'Monipal Feedback '.time(),$message);
    }

    public static function mailPayOff($pay,$get,$group,$uid,$gid){
        global $lang;
        global $settings;
        $DBObject = DBHandler::GetInstance(null);
        $message = self::GenerateEmail($lang['HELLO'].' '.$DBObject->GetUserNameById($uid).',','
        <p>'.$lang['YOUR_GROUP'].' '.$group.' '.$lang['HAS_CHECKOUT'].'</p>
        <p>'.$lang['YOUR_BALANCE'].'</p>
        <p>'.$pay.'</p>
        <p>'.$get.'</p>
        <p><b>'.$lang['OVERVIEW'].'</b><br />
        '.$lang['COMPLETECHECKOUTOVERVIEW'].'<br />'.$lang['OR'].' <a href="'.$settings['site_url'].'cost-checkout/'.$gid.'/">'.$lang['CLICKHERE'].'</a></p>
        '.$lang['YOURS_FAITHFULLY'].'<br /><br />

            '.$lang['SITE_NAME'].'');
        //var_dump($message);
        return self::sendMail($DBObject->GetEmailByuserID($uid),$lang['SUB_CHECKOUT'],$message);

    }

    public static function mailFeedbackThanks($to)
    {
        global $lang;
        // message
        $message = self::GenerateEmail($lang['THANKS_FOR_FEEDBACK'],
            $lang['FEEDBACK_RECEIVED'].'<br /><br />

            '.$lang['YOURS_FAITHFULLY'].'<br /><br />

            '.$lang['SITE_NAME'].'');

        self::sendMail($to,'Feedback Monipal',$message);
    }

    public static function mailContactThanks($to)
    {
        global $lang;
        // message
        $message = self::GenerateEmail($lang['CONTACT_MONIPAL'],
          $lang['CONTACT_RECIEVED_2'].'<br /><br />
         '.$lang['YOURS_FAITHFULLY'].'<br /><br />

            '.$lang['SITE_NAME'].'');

        self::sendMail($to,$lang['CONTACT_MONIPAL'],$message);
    }
	
	public static function mailChristmasWish2014()
    {
        global $lang;
		
		return self::GenerateEmail("Beste wensen van Monipal!", 'Beste Monipaller,<br /><br />

Via deze weg willen we je een mooie Kerst een een gelukkig nieuwjaar wensen!<br />
We hopen dat we je in 2015 nog beter kunnen helpen met het organiseren van het avondeten, het eerlijk verdelen van kosten en de schoonmaak van je studentenhuis.<br /><br />

Afgelopen jaar stond voor ons in het teken van verbetering van de website en de lancering van de apps voor iOS, Android en Windows Phone. In 2015 gaan we op volle kracht vooruit om Monipal nog beter te maken!<br /><br />

Met vriendelijke groet,<br />
de Monipal crew<br />
<a href="http://www.monipal.com">www.monipal.com</a><br /><br />

Gebruik jij de Monipal app al? Download hem nu via <a href="http://www.monipal.com/apps/">http://www.monipal.com/apps/</a><br /><br />
<img src="http://www.monipal.com/assets/images/mail/christmas2014.png" alt="Merry Christmas" />');
    }
	

    private static function GenerateEmail($title,$body)
    {
        global $settings;
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Monipal.com</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="background-color:#efefef; margin: 0; padding: 0;">
	<table bgcolor="#efefef" border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td style="padding: 0px 0 0px 0;">
				<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: none;">
					<tr>
						<td align="center" bgcolor="#e14b41" style="padding: 0; color: #ffffff; font-size: 28px; font-weight: bold; font-family: \'Helvetica Neue\', Helvetica, Arial, Geneva, sans-serif;">
							<img src="'.$settings['site_url'].'images/email/header_mail.png" alt="Monipal" width="600" height="153" style="display: block;" /> <!--'.$settings['site_url'].'-->
						</td>
					</tr>
					<tr>
						<td bgcolor="#ffffff" style="padding: 30px;">
							<table border="0" cellpadding="0" cellspacing="0" width="100%">
								<tr>
									<td style="color: #e14b41; font-family: \'Helvetica Neue\', Helvetica, Arial, Geneva, sans-serif;; font-size: 18px;">
										<b>'.$title.'</b>
									</td>
								</tr>
								<tr>
									<td style="padding: 20px 0 30px 0; color: #153643; font-family: \'Helvetica Neue\', Helvetica, Arial, Geneva, sans-serif;; font-size: 13px; line-height: 20px;">
									    '.$body.'
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td bgcolor="#e14b41" style="padding: 30px 30px 30px 30px;">
							<table border="0" cellpadding="0" cellspacing="0" width="100%">
								<tr>
									<td style="color: #ffffff; font-family: \'Helvetica Neue\', Helvetica, Arial, Geneva, sans-serif; font-size: 14px;" width="75%">
										<a href="'.$settings['site_url'].'" style="color: #ffffff;"><font color="#ffffff">Monipal.com</font></a> &copy; 2014<br/>
									</td>
									<td align="right" width="25%">
										<table border="0" cellpadding="0" cellspacing="0">
											<tr>
												<td style="font-family: \'Helvetica Neue\', Helvetica, Arial, Geneva, sans-serif; font-size: 12px; font-weight: bold;">
													<a href="https://twitter.com/Monipalcom" style="color: #ffffff;">
														<img src="'.$settings['site_url'].'images/email/tw.gif" alt="Twitter" width="38" height="38" style="display: block;" border="0" />
													</a>
												</td>
												<td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>
												<td style="font-family: \'Helvetica Neue\', Helvetica, Arial, Geneva, sans-serif; font-size: 12px; font-weight: bold;">
													<a href="http://www.facebook.com/Monipalcom" style="color: #ffffff;">
														<img src="'.$settings['site_url'].'images/email/fb.gif" alt="Facebook" width="38" height="38" style="display: block;" border="0" />
													</a>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>';
    }

}