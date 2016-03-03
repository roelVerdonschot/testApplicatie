<?php
date_default_timezone_set('Europe/Amsterdam');
if(false) //true == debug
{
    ini_set('error_reporting', E_ALL);
    error_reporting(E_ALL);
    ini_set('log_errors','1');
    ini_set('html_errors','0');
    ini_set('error_log', dirname(__FILE__) . '/../admin/log/'.date("Ymd").'-error_log.txt');
    ini_set('display_errors','1');
}
else
{
    ini_set('error_reporting', E_ALL);
    error_reporting(E_ALL);
    ini_set('log_errors','1');
    ini_set('html_errors','0');
    ini_set('error_log', dirname(__FILE__) . '/../admin/log/'.date("Ymd").'-error_log.txt');
    ini_set('display_errors','0');
}
//do not allow direct access
if ( strpos(strtolower($_SERVER['SCRIPT_NAME']),strtolower(basename(__FILE__))) ) {
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');
}

define('SITE_STATE_RELEASE',"1");
define('SITE_STATE_BETA',"2");
define('SITE_STATE_TEST',"3");

define('DINNER_NOTHING_SET',-1);
define('DINNER_NOT_EATING',0);
define('DINNER_JOIN_DINNER',1);
define('DINNER_IS_COOK',2);

if(isset($_SERVER) && isset($_SERVER["HTTP_HOST"])) //  voor cronjob (http_host bestaat daar niet)
{
	$host = $_SERVER["HTTP_HOST"];
}
else
{
	$host = "";
}
switch($host)
{
    // Voor de development server
    case 'localhost:8080':
        $db['username']     = 'root';
        $db['password']     = 'usbw';
        $db['host']        	= 'localhost'; //localhost
        $db['database']    	= 'monipal_beta';
        $db['port']			= 3307;
        $settings['site_url'] 	= "http://localhost:8080/"; // Url van de website
        $settings['site_state'] = SITE_STATE_TEST;
        break;
	// Test / development server
	case 'test.monipal.com':
        $db['username']     = 'monipal_webtest';
        $db['password']     = 'c1dtjWrE';
        $db['host']        	= 'localhost'; //localhost
        $db['database']    	= 'monipal_test';
        $db['port']			= 3307;
        $settings['site_url'] 	= "http://test.monipal.com/"; // Url van de website
        $settings['site_state'] = SITE_STATE_TEST;
        break;
    // Voor de beta website
    case 'beta.monipal.com':
    case 'api.monipal.com':
        $db['username']     = 'monipal_webbeta';
        $db['password']     = 'Sh1VTKOi';
        $db['host']        	= 'localhost'; //localhost
        $db['database']    	= 'monipal_beta';
        $db['port']			= 3306;
        $settings['site_url'] 	= "http://beta.monipal.com/"; // Url van de website
        $settings['site_state'] = SITE_STATE_BETA;
        break;
    default:
        $db['username']     = 'monipal_webfinal';
        $db['password']     = 'lyeb4cbU';
        $db['host']        	= 'localhost'; //localhost
        $db['database']    	= 'monipal_final';
        $db['port']			= 3306;
        $settings['site_url'] 	= "http://www.monipal.com/"; // Url van de website
        $settings['site_state'] = SITE_STATE_RELEASE;
}
$settings['languages'] = array('nl' => 'Nederlands',);//'en' => 'English','de'=>'Deutsch','fr'=>'Français','es'=>'Español','ru'=>'Русский');

$settings['DEFAULT_DEMO_USER'] = 71;
$settings['DEFAULT_DEMO_GROUP']  = 64;

$settings['site_name'] 	= "Monipal"; // Website Titel
$settings['admin_url'] 	= "http://www.monipal.com/admin"; // Url van de website

$settings['feedback_emailaddress']      = 'support@monipal.com';

//--------------------Setting--------------------
$settings['db_user_table'] 				= 'user';
$settings['db_session_table'] 			= 'session';
$settings['db_login_attempt_table'] 	= 'login_attempt';
$settings['db_group_table']             = '`group`';
$settings['db_user_group_table']        = 'user_group';
$settings['db_check_table']             = 'check';
$settings['db_cost_table']              = 'cost';
$settings['db_dinnerplanner_table']     = 'dinnerplanner';
$settings['db_invite_table']            = 'invite';
$settings['db_logbook_table']           = 'logbook';
$settings['db_setting_table']           = 'setting';
$settings['db_stickynote_table']        = 'stickynote';
$settings['db_task_table']              = 'task';
$settings['db_user_task_table']         = 'user_task';
$settings['db_user_cost_table']         = 'user_cost';
$settings['db_device_table']            = 'device';
$settings['db_user_group_device_table'] = 'user_group_device';
$settings['db_dinner_table'] 		    = 'dinner';
$settings['db_changesets_table']        = 'changesets';
$settings['db_faq_table']               = 'faq';
$settings['db_user_task_ical_table'] 	= 'user_task_ical';
$settings['db_check_table']             = 'group_check';
$settings['db_checkout_table']          = 'checkout';
$settings['db_cost_subgroup_table']             = 'cost_subgroup';
$settings['db_cost_subgroup_user_table']          = 'cost_subgroup_user';

$settings['bit_group_modules']          = array( 'cost' => '1', 'dinner' => '2', 'task' => '4', 'datepicker' => '8');

$settings['max_login_attempts'] 		= '3';
$settings['show_loading_time']          = false;
$settings['maintenance_message']        = false;
$settings['maintenance_message_text']        = "Momenteel is er een probleem met de automatische mails vanuit Monipal. Hierdoor kunt u zich tijdelijk niet registreren of mensen uitnodigen voor uw groep.<br />We doen er alles aan dit probleem zo snel mogelijk te verhelpen. Excuses voor het ongemak en prettige Kerstdagen toegewenst.";



$settings['country_array']              = array('AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' =>
'Algeria', 'AS' => 'American Samoa', 'AD' => 'Andorra', 'AO' => 'Angola', 'AI' =>
'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua and Barbuda', 'AR' =>
'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' =>
'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' =>
'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' =>
'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' =>
'Bolivia', 'BA' => 'Bosnia and Herzegowina', 'BW' => 'Botswana', 'BV' =>
'Bouvet Island', 'BR' => 'Brazil', 'IO' => 'British Indian Ocean Territory',
    'BN' => 'Brunei Darussalam', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' =>
    'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' =>
    'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' =>
    'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island', 'CC' =>
    'Cocos (Keeling) Islands', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CG' =>
    'Congo', 'CD' => 'Congo, the Democratic Republic of the', 'CK' => 'Cook Islands',
    'CR' => 'Costa Rica', 'CI' => 'Cote d&#39Ivoire', 'HR' => 'Croatia (Hrvatska)',
    'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark',
    'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'TP' =>
    'East Timor', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' =>
    'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia',
    'FK' => 'Falkland Islands (Malvinas)', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji',
    'FI' => 'Finland', 'FR' => 'France', 'FX' => 'France, Metropolitan', 'GF' =>
    'French Guiana', 'PF' => 'French Polynesia', 'TF' =>
    'French Southern Territories', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' =>
    'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' =>
    'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' =>
    'Guam', 'GT' => 'Guatemala', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' =>
    'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard and Mc Donald Islands', 'VA' =>
    'Holy See (Vatican City State)', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' =>
    'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' =>
    'Iran (Islamic Republic of)', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IL' =>
    'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JO' => 'Jordan',
    'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KP' =>
    'Korea, Democratic People&#39s Republic of', 'KR' => 'Korea, Republic of', 'KW' =>
    'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Lao People&#39s Democratic Republic',
    'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' =>
    'Libyan Arab Jamahiriya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' =>
    'Luxembourg', 'MO' => 'Macau', 'MK' =>
    'Macedonia, The Former Yugoslav Republic of', 'MG' => 'Madagascar', 'MW' =>
    'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' =>
    'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania',
    'MU' => 'Mauritius', 'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' =>
    'Micronesia, Federated States of', 'MD' => 'Moldova, Republic of', 'MC' =>
    'Monaco', 'MN' => 'Mongolia', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' =>
    'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' =>
    'Nepal', 'NL' => 'Netherlands', 'AN' => 'Netherlands Antilles', 'NC' =>
    'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger',
    'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island', 'MP' =>
    'Northern Mariana Islands', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan',
    'PW' => 'Palau', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' =>
    'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn', 'PL' =>
    'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RE' =>
    'Reunion', 'RO' => 'Romania', 'RU' => 'Russian Federation', 'RW' => 'Rwanda',
    'KN' => 'Saint Kitts and Nevis', 'LC' => 'Saint LUCIA', 'VC' =>
    'Saint Vincent and the Grenadines', 'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' =>
    'Sao Tome and Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'SC' =>
    'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' =>
    'Slovakia (Slovak Republic)', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands',
    'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' =>
    'South Georgia and the South Sandwich Islands', 'ES' => 'Spain', 'LK' =>
    'Sri Lanka', 'SH' => 'St. Helena', 'PM' => 'St. Pierre and Miquelon', 'SD' =>
    'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard and Jan Mayen Islands', 'SZ' =>
    'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' =>
    'Syrian Arab Republic', 'TW' => 'Taiwan, Province of China', 'TJ' =>
    'Tajikistan', 'TZ' => 'Tanzania, United Republic of', 'TH' => 'Thailand', 'TG' =>
    'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad and Tobago', 'TN' =>
    'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TC' =>
    'Turks and Caicos Islands', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' =>
    'Ukraine', 'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' =>
    'United States', 'UM' => 'United States Minor Outlying Islands', 'UY' =>
    'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VE' => 'Venezuela', 'VN' =>
    'Viet Nam', 'VG' => 'Virgin Islands (British)', 'VI' => 'Virgin Islands (U.S.)',
    'WF' => 'Wallis and Futuna Islands', 'EH' => 'Western Sahara', 'YE' => 'Yemen',
    'YU' => 'Yugoslavia', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe');

?>