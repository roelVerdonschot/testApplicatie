<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = "Apps voor Monipal";
$_page_description = "Monipal app  downloaden voor op je mobiel? Kijk hier of hij al beschikbaar is!";
$_page_keywords = "monipal, android, iphone, windows phone, mobiel, app, store";

require_once("inc/header.inc.php");
?>	
<div class="normal-content" style="margin-top: 109px;">
	<div class="pure-g">
		<div class="l-box pure-u-1">
			<h2 class="content-head is-center">Monipal voor op je telefoon</h2>
			<!--<p class="is-center">Het team van Monipal is hard bezig om de apps in de app-stores te krijgen.<br />We zijn momenteel in de testfase van de apps en de verwachting is dat ze in september beschikbaar worden om te downloaden.</p>-->
		</div>

        <div class="l-box pure-u-1-3 boxbg" onclick="window.open('http://www.windowsphone.com/nl-nl/store/app/monipal/62a689e6-edcf-4ff3-9fce-d043e4d85532','_black')">

			<h3 class="content-subhead">
				<!--<i class="and"></i>-->
				Windows Phone
			</h3>
			<p>
				<img src="<?php echo $settings['site_url']; ?>images/nokia-splash.png" class="center" alt="Windows Phone app Monipal" />
				<!--<br><br><a href="#" class="ml">> Kostenbeheer demo</a>-->
			</p>
		</div>
        <div class="l-box pure-u-1-3 boxbg1" onclick="window.open('https://play.google.com/store/apps/details?id=com.monipal','_blank')">
			<h3 class="content-subhead">
				<!--<i class="apple"></i>-->
				Android
			</h3>
			<p>
				<img src="<?php echo $settings['site_url']; ?>images/samsung-splash.png" class="center" alt="Android app Monipal" />
				<!--<br><br><a href="#" class="ml">> Eetlijst demo</a>-->
			</p>
		</div>
		<div class="l-box pure-u-1-3 boxbg"  onclick="window.open('https://itunes.apple.com/us/app/monipal/id897882425','_blank')">
			<h3 class="content-subhead">
				<!--<i class="win"></i>-->
				iPhone
			</h3>
			<p>
				<img src="<?php echo $settings['site_url']; ?>images/iphone-home.png" class="center" alt="iPhone app Monipal" />
				<!--<br><br><a href="#" class="ml">> Takenlijst demo</a>	-->				
			</p>
		</div>
    </div>
</div>
<?php

require_once("inc/footer.inc.php");
?>