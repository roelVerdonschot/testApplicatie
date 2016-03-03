<?php
/**
 * User: Pascal Worek
 * Date: 11-7-13
 * Time: 13:32
 */
ob_start();
require_once("../inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);

$from = $to = "arwin@monipal.com";
$name = "Testnaam";
$subject = "Test Subject";

Email_Handler::activationConfirmation($to,$lang['ACTIVATION'].' Monipal',"Arwin");
Email_Handler::mailActivationRequest($to,$subject,$name,"testcode");
Email_Handler::ActivationAndPassword($to,$subject,$name,"testcode","testW8Woord","testVriend");
Email_Handler::mailBodyInviteToGroup($to,$subject,$name,"HuizeTest",$groupId);
Email_Handler::mailBodyResetPass($to, $subject,$name, "testCode");
Email_Handler::mailEmailChanged($to, $name);
Email_Handler::mailTaskReminder($to, $name, "testTask");
Email_Handler::mailFeedback($from, "feedbackTest", "testPath");
Email_Handler::mailPayOff($pay,$get,"HuizeTest",$uid);
Email_Handler::mailFeedbackThanks($to);
Email_Handler::mailContactThanks($to);

?>
