<?php
// Language set
if(class_exists('Authentication_Controller'))
{
    if(Authentication_Controller::IsAuthenticated()) {
        $user = Authentication_Controller::GetAuthenticatedUser();
        $langCode = $user->preferredLanguage;
        if(isSet($_GET['editLang']) && array_key_exists($_GET['editLang'], $settings['languages']))
        {
            $user->editPreferredLanguage($_GET['editLang']);
            $langCode = $_GET['editLang'];
        }
    }
}
if(!isSet($langCode)) {
    if(isSet($_GET['lang']))
    {
        // register the session and set the cookie
        $_SESSION['lang'] = $langCode = $_GET['lang'];
        setcookie('lang', $langCode, time() + (3600 * 24 * 30));
    }
    else if(isSet($_SESSION['lang']))
    {
        $langCode = $_SESSION['lang'];
    }
    else if(isSet($_COOKIE['lang']))
    {
        $langCode = $_COOKIE['lang'];
    }
    else
    {
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            $langCode = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        }
        else
        {
            $langCode = "nl";
        }
    }
}
if(!array_key_exists($langCode, $settings['languages'])){	
	$langCode = "nl";
}
switch ($langCode) {
    case 'en':
        $lang_file = 'lang.en.php';
		break;
    case 'nl':
        $lang_file = 'lang.nl.php';
        break;
    default:
        $lang_file = 'lang.nl.php';
}
require_once 'languages/'.$lang_file;
setlocale(LC_TIME, $lang['_LANG_CODE_LINUX'], $lang['_LANG_CODE_WINDOWS']);
?>