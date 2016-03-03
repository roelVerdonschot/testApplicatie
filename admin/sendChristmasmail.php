<?php


require_once("../inc/config.inc.php");

//set php script timeout, 0 to disable
set_time_limit(0);
echo "Time: ".time();
if(isset($_GET['sendnow']) && $_GET['sendnow'] == "yes" && isset($_GET['timestamp']) && $_GET['timestamp'] <= (time()+10))
{
	$db = new PDO('mysql:host=localhost;dbname=monipal_final;charset=utf8', 'monipal_webfinal', 'lyeb4cbU', array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	try {
		$stmt = $db->query('SELECT firstname, surname, email FROM `monipal_final`.`user` WHERE activation IS NULL');// invalid query!
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach($results as $user)
		{
			//$users[] = utf8_decode($user['firstname'] . ' ' . (!empty($user['surname']) ? $user['surname'].' ' : '').'<'.trim($user['email']).'>');
		}
	} catch(PDOException $ex) {
		echo "An Error occured!";// user friendly message
	}
	//$users[] = "Arwin van der velden <arwinvandervelden@hotmail.com>";
	$users[] = "arwin1993@gmail.com";
	$users[] = "info@ligon.nl";
	//$users[] = "jorinvandervelden@gmail.com";
	//$users[] = "roel_verdonschot@hotmail.com";

	// To send HTML mail, the Content-type header must be set
	$headers =  'From: Monipal support <support@monipal.com>' . "\r\n";
	$headers .= 'Return-Path: Monipal support <support@monipal.com>'. "\r\n";
	$headers .= 'Reply-To: Monipal support <support@monipal.com>'. "\r\n" ;
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Organization: Monipal.com'. "\r\n";
	$headers .= 'X-Mailer: PHP/' . phpversion(). "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	
	//$to = "Monipal support <support@monipal.com>";
	$subject = "Beste wensen van Monipal!";
	
	$fail = array();
	$succeed = 0;
	$count = 0;
	foreach($users as $u)
	{
		if(mail($u, $subject, Email_Handler::mailChristmasWish2014(), $headers))
		{
			$succeed++;			
		}
		else
		{
			$fail[] = $u;
		}
		$count++;
		if(($count % 50) == 0) // om de 5 mails een sleep van 6 seconden
		{
			echo $count."<br />";
			sleep(5);
		}
	
	}
	// Mail it
	echo "gelukt: ".$succeed;
	var_dump($fail);
}
else
{
	echo Email_Handler::mailChristmasWish2014();
}
//don't forget to reset to 30 seconds.
set_time_limit(30);
?>