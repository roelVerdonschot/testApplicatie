<?php
if ( strpos(strtolower($_SERVER['SCRIPT_NAME']),strtolower(basename(__FILE__))) ) {
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');
}
require_once("../inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);

mb_internal_encoding( 'UTF-8' );
//header('Content-Type: text/html; charset=utf-8');
header('Content-type: application/json; charset=utf-8');

define("LOGIN_FAILED", 100);
define("WRONG_TIMESTAMP", 101);
define("USER_NOT_ACTIVATED", 102);
require __DIR__.'/RNCryptor/autoload.php'; //ios decription
require_once('api_controller.class.php');
require_once('login.class.php');
require_once('error_data.class.php');
require_once('group_data.class.php');
require_once('user_data.class.php');
require_once('weeklist_data.class.php');
require_once('task_data.class.php');
require_once('dinner_data.class.php');
require_once('mcrypt.class.php');
require_once('cost_data.class.php');
require_once('dinner_stats.class.php');
require_once('checkout_data.class.php');
require_once('group_invite.class.php');
?>
