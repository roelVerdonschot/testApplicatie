<?php
if($settings['maintenance_message'] && basename($_SERVER['PHP_SELF']) != "index.php"){
    echo '<div class="notification-bar"><strong>LET OP:</strong> '.$settings['maintenance_message_text'].'</div>'; //$lang['MAINTENANCE_MESSAGE']
}
if(!isset($_no_footer))
{
?>
<div class="footer l-box is-center">
	<?php
	if(Authentication_Controller::IsAuthenticated()) {
	?><p id="footer-nav">
		Monipal is momenteel nog in ontwikkeling, daardoor kan het zijn dat sommige onderdelen niet helemaal correct werken.<br /> Vind je iets wat niet goed werkt? Vertel het ons via de feedback knop aan de zijkant! 
	</p>
	<?php
	}
	?>
	<p id="footer-nav">
		<?php /*echo languageSelectFooter("select_language", $lang['_LANG_CODE']);*/ ?>			
			<span>@ 2014 <?php echo $lang['SITE_NAME']; ?></span>
		<a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/privacy/"><?php echo $lang['PRIVACY_CAP']; ?> &amp; <?php echo $lang['COOKIES_CAP']; ?></a>
			<span> | </span>
		<a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/terms-and-conditions/"><?php echo strtoupper($lang['TERMS_AND_CONDITIONS']); ?></a>
			<span> | </span>
		<a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/changesets/"><?php echo 'Changesets'; ?></a>
			<!--<span> | </span>
		<a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/faq/"><?php echo 'FAQ'; ?></a> -->
			<span> | </span>
		<a href="<?php echo $settings['site_url'].$lang['_LANG_CODE']; ?>/contact/"><?php echo $lang['CONTACT']; ?></a>
			<span> | </span>
		<a href="https://www.facebook.com/monipalcom" target="_blank"><i class="icon-facebook"></i></a>
			<span> | </span>
		<a href="https://twitter.com/intent/follow?region=follow_link&amp;screen_name=Monipalcom" target="_blank"><i class="icon-twitter"></i></a>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
			<span> | </span>
		<a href="https://plus.google.com/112657715546313124825" rel="publisher" target="_blank"><i class="icon-gplus"></i></a>	
	</p>
</div>
<?php
}
?>
<div id="slidebox">
    <div id="feedback">
        <a href="#"><?php echo $lang['FEEDBACK']; ?></a>
    </div>
    <div id="feedback-form">
        <h2><?php echo $lang['GIVE_FEEDBACK']; ?></h2>
        <form>
            <span>
                <label><?php echo $lang['E-MAILADRES']; ?></label>
                <input name="e-mail" type="email" id="feedback-email" tabindex="100" value="<?php if(Authentication_Controller::GetAuthenticatedUser() != null) { echo Authentication_Controller::GetAuthenticatedUser()->email; } else { echo ''; } ; ?>" />
            </span>

            <span class="clear">
                <label><?php echo $lang['FEEDBACK']; ?></label>
                <textarea id="feedback-message" rows="4" cols="45" tabindex="101"></textarea>
            </span>

            <span class="clear">
				<input type="submit" id="send-feedback" tabindex="102" class="btn btn-red btn-lg" value="<?php echo $lang['SEND']; ?>" /><input type="button" id="cancel-feedback" tabindex="103" class="btn btn-grey btn-lg" value="<?php echo $lang['CANCEL']; ?>" />
            </span>
        </form>
        <div class="clear"></div>
    </div>
</div>
<script type="text/javascript" src="<?php echo $settings['site_url']; ?>js/scripts.php?lang=<?php if(isset($langCode)) {echo $langCode; }else{ echo 'en';}?>"></script>
<script type="text/javascript" src="<?php echo $settings['site_url']; ?>js/jquery.doubleScroll.js"></script>
<?php 
if($settings['site_state'] == SITE_STATE_RELEASE)
{
	if(!Authentication_Controller::IsAuthenticated()) {
		echo "<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-44918419-1', 'monipal.com');
		ga('send', 'pageview');
		</script>";
	}
	else
	{
		echo "<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', 'UA-44918419-3', 'auto');
		  ga('send', 'pageview');

		</script>";
	}
}
elseif($settings['site_state'] == SITE_STATE_BETA)
{
    echo "<script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-44918419-2', 'monipal.com');
    ga('send', 'pageview');
    </script>";
}
if($settings['show_loading_time'] == true)
{
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    echo 'Page generated in '.$total_time.' seconds.';
}
?>
<script>
  $(function() {
    $(".rslides").responsiveSlides();
	
	$('a[href*=#]:not([href=#])').click(function() {
		if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
		  var target = $(this.hash);
		  target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
		  if (target.length) {
			$('html,body').animate({
			  scrollTop: target.offset().top
			}, 1000);
			return false;
		  }
		}
	});
  });
</script>
</body>
</html>