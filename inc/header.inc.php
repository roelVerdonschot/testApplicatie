<!doctype html>
<html lang="<?php echo $lang['_LANG_CODE']; ?>">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title><?php if(isset($_page_title)) { echo $_page_title.' | '; } echo $settings['site_name']; ?></title>
    <?php
    if(!isset($_error_page)) $_error_page = false;
    if(($settings['site_state'] == SITE_STATE_RELEASE && !Authentication_Controller::IsAuthenticated()) && ($_error_page !== true)) {
        $request = parse_url($_SERVER['REQUEST_URI']);
        $link = rtrim(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $request["path"]), '/');
        if(substr($link, -1) != '/') {
            $link .= '/';
        }
        if(substr($link, 1, 2) != null && array_key_exists(substr($link, 1, 2),$settings['languages']))
        {
            $linkWithoutLang = substr($link, 3);
        }
        else
        {
            $linkWithoutLang = $link;
            $link = $lang['_LANG_CODE'].$link; //substr($result, 3);
        }
        echo '
        <meta name="description" content="'.(isset($_page_description) ? $_page_description : $lang['DEFAULT_DESCRIPTION'] ).'" />
        <meta name="keywords" content="'.(isset($_page_keywords) ? $_page_keywords : $lang['DEFAULT_KEYWORDS'] ).'" />

        <link rel="canonical" href="'.$settings['site_url'].''.ltrim($link, '/').'" />
        <meta http-equiv="content-language" content="'.$lang['_LANG_CODE'].'" />
        <meta http-equiv="content-script-type" content="text/javascript" />
        <meta http-equiv="content-style-type" content="text/css" />
        <meta http-equiv="window-target" content="_top" />
        <meta name="twitter:card" content="summary">
        <meta name="twitter:site" content="@Monipalcom">
        <meta name="twitter:creator" content="@Monipalcom">
        <meta property="og:type" content="website" />
        <meta property="og:title" content="'.$lang['OG_TITLE'].'" />
        <meta property="og:image" content="" />
        <meta property="og:description" content="'.$lang['OG_DESCRIPTION'].'" />
        <meta property="og:url" content="'.$settings['site_url'].''.$lang['_LANG_CODE'].'/" />
        <meta property="og:site_name" content="'.$settings['site_name'].'" />
        <meta name="country" content="'.$lang['_LANG_CODE'].'" />


        <link rel="alternate" type="text/html" hreflang="nl" href="'.$settings['site_url'].'nl'.$linkWithoutLang.'" title="'.$settings['languages']['nl'].'" />';

		if(isset($_no_index) && $_no_index == true)
		{			
			echo '<meta name="robots" content="noindex, nofollow" />';
		}
		else
		{			
			echo '<meta name="robots" CONTENT="index, follow" />';
		}
    } else { //        <link rel="alternate" type="text/html" hreflang="en" href="'.$settings['site_url'].'en'.$linkWithoutLang.'" title="English" />
        echo '<meta name="robots" content="noindex, nofollow" />';
    }
    ?>
	
    <link rel="icon" href="<?php echo $settings['site_url']; ?>favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo $settings['site_url']; ?>favicon.ico" type="image/x-icon">
	
    <?php
    if(basename($_SERVER['PHP_SELF']) == "index.php" && !Authentication_Controller::IsAuthenticated())
    {
        echo '<link rel="stylesheet" href="'.$settings['site_url'].'css/styles-home.css?v=1.0" />';
    }
    elseif((basename($_SERVER['PHP_SELF']) == "register.php" || basename($_SERVER['PHP_SELF']) == "reset-password.php") && !Authentication_Controller::IsAuthenticated() || $_error_page === true)
    {
        //echo '<link rel="stylesheet" href="'.$settings['site_url'].'css/styles-register.css?v=1.0" />';
    }
    elseif(basename($_SERVER['PHP_SELF']) == "login.php" && !Authentication_Controller::IsAuthenticated())
    {
        //echo '<link rel="stylesheet" href="'.$settings['site_url'].'css/styles-login.css?v=1.0" />';
    }
    ?>
	<link rel="stylesheet" href="<?php echo $settings['site_url']; ?>css/pure.css">
	
    <!--[if lte IE 8]>
        <link rel="stylesheet" href="<?php echo $settings['site_url']; ?>css/main-grid-old-ie.css">
    <!--[if gt IE 8]><!-->
        <link rel="stylesheet" href="<?php echo $settings['site_url']; ?>css/main-grid.css" />
    <!--<![endif]-->
  
    <!--[if lte IE 8]>
        <link rel="stylesheet" href="<?php echo $settings['site_url']; ?>css/styles-old-ie.css">
    <![endif]-->
    <!--[if gt IE 8]><!-->
        <link rel="stylesheet" href="<?php echo $settings['site_url']; ?>css/styles.css?v=1.2" />
    <!--<![endif]-->
	
    <link rel="stylesheet" href="<?php echo $settings['site_url']; ?>icon/fontello.css?v=1.1">
    <link rel="stylesheet" href="<?php echo $settings['site_url']; ?>icon/animation.css">
    <!--[if IE 7]>
    <link rel="stylesheet" href="icon/fontello-ie7.css">
    <![endif]-->
	
    <link rel="stylesheet" href="<?php echo $settings['site_url']; ?>css/forms.css?v=1.0" />
    <link rel="stylesheet" href="<?php echo $settings['site_url']; ?>css/tables.css?v=1.0" />
    <link rel="stylesheet" href="<?php echo $settings['site_url']; ?>css/print.css?v=1.0" type="text/css" media="print" />

	<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script src="<?php echo $settings['site_url']; ?>js/responsiveslides.js"></script>
	<script src="<?php echo $settings['site_url']; ?>js/scrollhide.js"></script>
	<?php
	if(!isset($_no_header))
	{
	?>
	
</head>
<body>

    <div id="header" class="pure-menu-fixed header">
		<div class="top-menu">
			<div class="top-menu-max pure-menu pure-menu-open pure-menu-horizontal">
				<span class="slogan pure-menu-heading">Easier shared life</span>
				<?php
				if(!Authentication_Controller::IsAuthenticated())
				{
				?>
				<ul>
					<li><a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']."/reset-password/"; ?>"><?php echo $lang['LOGINBOX_FORGOT_PASSWORD']; ?></a></li>					
					<li><a class="icon-facebook" href="https://www.facebook.com/monipalcom" target="_blank"></a></li>
					<li><a class="icon-twitter" href="https://twitter.com/intent/follow?region=follow_link&amp;screen_name=Monipalcom" target="_blank"></a></li>
					<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
					<li><a class="icon-gplus" href="https://plus.google.com/112657715546313124825" rel="publisher" target="_blank"></a></li>
				</ul>
				<?php
				}
				 else
				{
					$user = Authentication_Controller::GetAuthenticatedUser();
					$invites = $DBObject->CheckInvites($user->email);
				?>
				<ul>
					<li><a class="icon-user" href="<?php echo $settings['site_url']; ?>my-account/"><span class="headerUserName"><?php echo $user->getFullName().($invites > 0 ? ' ('.$invites.')' : '');  ?></span></a></li>
					<li><a class="icon-cog-1" href="<?php echo $settings['site_url']; ?>settings/"></a></li>
					<li><a class="icon-logout" href="<?php echo $settings['site_url']; ?>logout/" title="<?php echo $lang['LOGINBOX_LOGOUT']; ?>"></a></li>
				</ul>				
				<?php
				}
				?>
			</div>
		</div>
        
		<div class="home-menu">
			<div class="home-menu-max pure-menu pure-menu-open pure-menu-horizontal">
				<a class="logo pure-menu-heading" href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/"></a>
				<ul>
				<?php
                if(!Authentication_Controller::IsAuthenticated())
                {
					if(isset($_demo))
					{
						echo '<li><a href="'.$settings["site_url"].$lang['_LANG_CODE'].'/demo-cost-management/">'.$lang['MENU_COSTMANAGEMENT'].'</a></li> <!--class="pure-menu-selected"-->
								<li><a href="'.$settings["site_url"].$lang['_LANG_CODE'].'/demo-dinner-list/">'.$lang['MENU_DINNER_LIST'].'</a></li>
								<li><a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-task-list/">'.$lang['MENU_TASK_LIST'].'</a></li>';
					}
					else
					{
						?>
						<!--<li><a href="<?php echo $settings["site_url"].$lang['_LANG_CODE']; ?>/demo-cost-management/">Demo</a></li> -->
						<li><a href="<?php echo $settings["site_url"].$lang['_LANG_CODE']; ?>/#info">Info</a></li>
						<li><a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']."/login/"; ?>"><?php echo $lang['LOGINBOX_LOGIN']; ?></a></li>
						<li><a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']."/register/"; ?>"><?php echo $lang['LOGINBOX_REGISTER']; ?></a></li>
						<?php
					}
                } else {
                    if(isset($_GET['gid']) && !empty($_GET['gid']))
                    {
                    	$group = $DBObject->GetGroupById($_GET['gid']);
                        if($group->CheckCost()){
                            echo '<li><a class="costmanagementimg" href="'.$settings['site_url'].'cost/'.(isset($_GET['gid']) ? $_GET['gid'].'/' : '').'">'.$lang['MENU_COSTMANAGEMENT'].'</a></li>';
                        }
                        if($group->CheckDinner()){
                            echo '<li><a class="dinnerlistimg" href="'.$settings['site_url'].'dinner/'.(isset($_GET['gid']) ? $_GET['gid'].'/' : '').'">'.$lang['MENU_DINNER_LIST'].'</a></li>';
                        }
                        if($group->CheckTask()){
                            echo '<li><a class="tasklistimg" href="'.$settings['site_url'].'tasks/'.(isset($_GET['gid']) ? $_GET['gid'].'/' : '').'">'.$lang['MENU_TASK_LIST'].'</a></li>';
                        }
                        //echo '<li class="settingsli"><a class="settingsimg" href="'.$settings['site_url'].'settings-group/'.$_GET['gid'].'/"></a></li>';
                    }
                    else
                    {
                        echo '<li><a href="'.$settings['site_url'].'new-group/">'.$lang['NEW_GROUP'].'</a></li>';
                    }
                }
                ?>					
				</ul>
			</div>
		</div>
    </div>
<?php
}


?>