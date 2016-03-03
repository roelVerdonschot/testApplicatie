<?php
require_once("config_settings.inc.php");

//do not allow direct access
if ( strpos(strtolower($_SERVER['SCRIPT_NAME']),strtolower(basename(__FILE__))) ) {
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');
}

if($settings['show_loading_time'] == true)
{
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
}

$session_name = 'hpSess'; // Set a custom session name
$secure = false; // Set to true if using https.
$httponly = true; // This stops javascript being able to access the session id.

ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies.
$cookieParams = session_get_cookie_params(); // Gets current cookies params.
session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
session_name($session_name); // Sets the session name to the one set above.
session_start(); // Start the php session
session_regenerate_id(); // regenerated the session, delete the old one.
header('Cache-control: private'); // IE 6 FIX

include_once('dbhandler.class.php');
include_once('authentication_controller.class.php');
unset($db);
include_once('functies.inc.php');
include_once('bcrypt.class.php');
include_once('cost.class.php');
include_once('dinner.class.php');
include_once('dinner_statistic_data.class.php');
include_once('email_handler.class.php');
include_once('group.class.php');
include_once('logbook.class.php');
include_once('setting.class.php');
include_once('sticky_note.class.php');
include_once('task.class.php');
include_once('push_device.class.php');
include_once('user.class.php');
include_once('user_saldo.class.php');
include_once('user_task.class.php');
include_once('user_cost.class.php');
include_once('language_controller.php');
include_once('import.class.php');
include_once('checkout.class.php');
include_once('cost_subgroup.class.php');
include("dropdown_menu.function.php");

?>
