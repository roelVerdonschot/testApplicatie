<?php
/**
 * User: Roel Verdonschot
 * Date: 7-8-13
 * Time: 20:19
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_COST'];
require_once("inc/header.inc.php");

$error = null;
$showform = false;
?><link rel="stylesheet" type="text/css" href="<?php echo $settings['site_url']; ?>calc/jquery.calculator.css">
<?php

$group = new Group;
$group = $group->GetGroupById(480);
$uid = $user->id;
$showform = true;

if($showform){
    echo '<div class="normal-content">
	<div class="pure-g">
	<div class="pure-u-3-4">';
    if($group->CheckCost()){
        /*if($error != null)
        {
            echo '<div class="error-bar">';
            foreach($error as $string)
                echo ' - '.$string.'<br />';
            echo '</div>';
        }*/
        if(isset($_GET['cost'])){
            echo '<div class="success-bar">'.$lang['COST_SUCCESFULLY_ADDED'].'</div>';
        }

        $instant = new cost();


        echo '<div class="l-box-top pure-u-1"><h1>'.$lang['MENU_COSTMANAGEMENT'].'</h1>
        <p><a href="'.$settings['site_url'].'group/'.$group->id.'/">&larr; '.$lang['BACK_TO_GROUPPAGE'].'</a></p></div>';

        echo '<div class="l-box left-sidebar pure-u-1-4">';
        $instant->ShowUserSaldos($group,$uid);
        echo '</div>
		<div class="l-box middle-box pure-u-17-24">';
        $instant->ShowUnpayedDinners($group,$uid,$errorData);
        $instant->ShowLastCostTable($group);
        echo '<a href="'.$settings['site_url'].'cost-history-all/'.$group->id.'/" style="float: right;">meer..</a>';
        echo '</div>';
    }
    else{
        echo '<div class="notification-bar">'.$lang['ERROR_COST_NOTACTIVE'].'<a href="'.$settings['site_url'].'settings-group/'.$group->id.'/">'.$lang['CLICKHERE'].'</a>'.$lang['EDIT_GROUP_MODULES'].'<br></div>';
    }
    echo '</div>';
    echo '<aside class="l-box pure-u-1-4">
    <a href="'.$settings['site_url'].'add-cost/'.$group->id.'" class="full-width buttonExtra">'.$lang['ADDCOST'].'</a>
    <a href="'.$settings['site_url'].'checkout/'.$group->id.'" class="buttonExtra">'.$lang['PAYOFF'].'</a>
    <a href="'.$settings['site_url'].'cost-history-all/'.$group->id.'" class="buttonExtra">'.$lang['HISTORY'].'</a>
    <a href="'.$settings['site_url'].'cost-group.php?gid='.$group->id.'" class="full-width buttonExtra">Kosten subgroep toevoegen</a>'.
        (COUNT($DBObject->GetCheckOutIds($group->id)) == 0 ? '' : '<a href="'.$settings['site_url'].'cost-checkout/'.$group->id.'" class="buttonExtra">'.$lang['CHECKOUTHISTORY'].'</a>').'
    '.($DBObject->CheckImportCost($group->id) ? '' : '<a href="'.$settings['site_url'].'import/'.$group->id.'" class="buttonExtra">'.$lang['IMPORT'].'</a>').'<div class="clear"></div>';
    if(!$DBObject->CheckImportCost($group->id)){
        echo '<h2>'.$lang['IMPORT_COSTS'].'</h2>
        <div class="box">
        <p>'.$lang['IMPORT_PROMOTION'].'</p>
        <a href="'.$settings['site_url'].'import/'.$group->id.'" class="btn btn-white">'.$lang['IMPORT_COSTS'].'</a></div>';
    }
    $group->ShowSideBarStickyNotes();
    $group->ShowSideBarSettings();
    echo '</aside>
	</div>
</div>';
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $settings['site_url']; ?>calc/jquery.calculator.css">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="<?php echo $settings['site_url']; ?>calc/jquery.plugin.js"></script>
    <script src="<?php echo $settings['site_url']; ?>calc/jquery.calculator.js"></script>
    <script> var jQuery_1_11_0 = $.noConflict(true);</script>
    <script type="text/javascript">
        jQuery_1_11_0(function () {
            var count = 0;
            jQuery_1_11_0('.dinner-cost').each(function() {
                // Replace comma with point
                jQuery_1_11_0('#basicCalculator'+count).keypress(function(e) {
                    var code = e.which ? e.which : e.keyCode;
                    if (code === 46){
                        e.preventDefault();
                        var input = $(this).val();
                        if(input.toLowerCase().indexOf(",") < 0)
                        {
                            var e2 = jQuery_1_11_0.Event("keypress");
                            e2.which = 44; // # Some key code value
                            e2.keyCode = 44;
                            jQuery_1_11_0('#basicCalculator'+count).trigger(e2);
                            input += ',';
                            $(this).val(input);
                        }

                    }
                });

                jQuery_1_11_0('#basicCalculator'+count).calculator({
                    showOn: 'button', buttonImageOnly: true, buttonImage: '<?php echo $settings['site_url']; ?>calc/calculator.png'
                });
                count++;
            });

        });
    </script>
    <?php
    echo '</div>';
}
else{
    header ('Location: '.$settings['site_url']);
}

include_once("inc/footer.inc.php");
?>