<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_PAGE_NOT_FOUND'];
$_error_page = true;
require_once("inc/header.inc.php");
?>
<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-1">
			<h1><?php echo $lang['PAGE_NOT_FOUND']; ?></h1>
			<p><?php echo $lang['PAGE_NOT_FOUND_DESCRIPTION']; ?></p>
			<p>
				<a href="javascript:history.back()"><?php echo $lang['PAGE_NOT_FOUND_GO_BACK']; ?></a> <br />
				<a href="<?php echo $settings['site_url']?>"><?php echo $lang['PAGE_NOT_FOUND_GO_TO_HOME']; ?></a>
			</p>
		</div>
	</div>
</div>
<?php
include_once("inc/footer.inc.php");
?>