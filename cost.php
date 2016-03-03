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
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login/');
    $showform = false;
}
?><link rel="stylesheet" type="text/css" href="<?php echo $settings['site_url']; ?>calc/jquery.calculator.css">   
<?php
if(isset($_GET['gid'])){	
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group;
    $errorData = array();
    if($group->AuthenticationGroup($user, $_GET['gid'])){
        $group = $group->GetGroupById($_GET['gid']);
        $uid = $user->id;
        if(isset($_GET['uid'])){
            if($group->checkUser($_GET['uid'])){
                if($_GET['uid'] != $user->id){
                    $uid = $_GET['uid'];
                }
                else{
                    if(!isset($_get['date'])){
                        header ('Location: '.$settings['site_url'].'cost/'.$group->id);
                    }
                    else{
                        $uid = $user->id;
                    }
                }
            }
            else{
                header ('Location: '.$settings['site_url'].'cost/'.$group->id);
            }
        }
        else{
            $uid = $user->id;
        }
        $showform = true;
        $instant = new Cost();
        if(isset($_GET['date'])){
            if (preg_match("/[0-9]{2}-[0-9]{2}-[0-9]{4}/", $_GET['date']))
            {
                list($dd,$mm,$yyyy) = explode('-',$_GET['date']);
                if (checkdate($mm,$dd,$yyyy)) {
                    $date = $yyyy.'-'.$mm.'-'.$dd;
                    $idcost = $instant->AddCost(0, $lang['NO_DINNER_COST_DESC'], $user->id,$group,1,$date);
                    $DBObject->AddDinnerCost($group->id,$date,$idcost);
                    $lb = new Logbook(null, $group->id, $user->id, null, $idcost, null, null, 'CA', null);
                    $DBObject->AddLogbookItem($lb);
                }
                header ('Location: '.$settings['site_url'].'cost/'.$group->id.'/'.$uid);
            }
        }
        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            $description = $_POST['description']; //isset($_POST['description']) ? $_POST['description'] : '';
            $amount = $_POST['amount']; // isset($_POST['amount']) ? $_POST['amount'] : '';
            $date = isset($_POST['date']) ? $_POST['date'] : '';
            $userId = $_POST['uid']; // array met user id's
            $isnumric = true;
            $count = -1;
            foreach($amount as $a){
                $count++;
                $a = str_replace(',', '.', $a);
                if(!is_numeric($a)){
                    $isnumric = false;
                    $errorData[$date[$count]] = $date[$count];
                }
                elseif($a <= 0){
                    if($error == null)
                    $error[] = $lang['AMOUNT_NEEDS_TO_BE_POSITIVE'];
                    $errorData[$date[$count]] = $date[$count];
                }
            }
            if($isnumric == false){
                $error[] = $lang['AMOUNT_IS_NOT_VALID'];
            }

            if(empty($_POST['description'])) {
                $error[] = $lang['DISCRIPTION_MISSING'];
            }

            if($instant->AddCosts($date, $amount, $description, $userId,$group,$user->id)){
                $DBObject->UpdateGroupUserAvgCooked($group);
                $DBObject->UpdateGroupUserSaldo($group->id);
            }
            else{
                $error[] = $lang['SOMETHING_WENT_WRONG_TRY_AGAIN'];
            }
        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}


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
    <a href="'.$settings['site_url'].'cost-group.php?gid='.$group->id.'" class="full-width buttonExtra">Kosten groep toevoegen</a>'.
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