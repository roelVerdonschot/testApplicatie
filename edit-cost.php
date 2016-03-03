<?php
/**
 * User: Roel Verdonschot
 * Date: 13-8-13
 * Time: 13:54
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_EDITCOST'];
require_once("inc/header.inc.php");
$showform = false;
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login/');
    $showform = false;
}
?>
    <link rel="stylesheet" type="text/css" href="<?php echo $settings['site_url']; ?>calc/jquery.calculator.css">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="<?php echo $settings['site_url']; ?>calc/jquery.plugin.js"></script>
	<script src="<?php echo $settings['site_url']; ?>calc/jquery.calculator.js"></script>
    <script> var jQuery_1_11_0 = $.noConflict(true);</script>
    <script type="text/javascript">
        jQuery_1_11_0(function () {
			
			// Replace comma with point
			jQuery_1_11_0('#basicCalculator').keypress(function(e) {
				var code = e.which ? e.which : e.keyCode;
				if (code === 46){				 
					e.preventDefault();	
					var input = $(this).val();
					if(input.toLowerCase().indexOf(",") < 0)
					{
						var e2 = jQuery_1_11_0.Event("keypress");
						e2.which = 44; // # Some key code value	
						e2.keyCode = 44;
						jQuery_1_11_0('#basicCalculator').trigger(e2);		
						input += ',';
						$(this).val(input);
					}
					
				}
			});
            jQuery_1_11_0('#basicCalculator').calculator({
                showOn: 'button', buttonImageOnly: true, buttonImage: '<?php echo $settings['site_url']; ?>calc/calculator.png'
			});
			
			
        });
    </script>
<?php
if(isset($_GET['gid']) && isset($_GET['cid'])){
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group;
    if($group->AuthenticationGroup($user, $_GET['gid'])){
        $cost = new Cost();
        $cost = $cost->GetCostById($_GET['cid']);
        $costBool = false;
        if($cost->idGroup == $_GET['gid']){
            $costBool = true;
        }
        if($costBool == false){
            header ('Location: '.$settings['site_url'].'cost/'.$group->id);
        }
        if($cost->date != null){
            list($yyyy,$mm,$dd) = explode('-',$cost->date);
            $costdate = $dd.'-'.$mm.'-'.$yyyy;
        }
        else{
            $costdate = '';
        }
        ?>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
        <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
        <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <script> var jQuery_1_9_1 = $.noConflict(true);</script>
        <link rel="stylesheet" href="/resources/demos/style.css" />
        <script>
            jQuery_1_9_1(function() {
                jQuery_1_9_1( "#datepicker" ).datepicker();
                jQuery_1_9_1( "#datepicker" ).datepicker("option", "dateFormat", "dd-mm-yy");
                jQuery_1_9_1( "#datepicker" ).datepicker( "setDate", "<?php echo $costdate ?>" );
            });
        </script>
        <?php
        if($cost->isDinner == 2){
            header ('Location: '.$settings['site_url'].'cost/'.$_GET['gid']);
        }
        $group = $group->GetGroupById($_GET['gid']);
        $users = $group->getUsers();
        $showform = true;
        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
            $amount = $_POST['amount'];
            $amount = str_replace(',', '.', $amount);
            if (preg_match("/[0-9]{2}-[0-9]{2}-[0-9]{4}/", $_POST['date']))
            {
                list($dd,$mm,$yyyy) = explode('-',$_POST['date']);
                if (checkdate($mm,$dd,$yyyy)) {
                    $date = $yyyy.'-'.$mm.'-'.$dd;
                }
            }
            if(is_numeric($amount)){
                if($amount > 0){
                    $cost->description = $_POST['description'];
                    $oldAmount = $cost->amount;
                    $cost->amount = $amount;
                    if(isset($date)){
                        $cost->date = $date;
                    }
                    //$cost->setIsDinner($_POST['is_dinner']);
                    $cost->EditCostData($cost);
                    // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                    $lb = new Logbook(null, $_GET['gid'], $user->id, null, $cost->id, null, null, 'CE', $oldAmount);
                    $DBObject->AddLogbookItem($lb);

                    foreach( $users as $u ){
                        $guests = $_POST["number"][$u->id];
                        $cost->UpdateCostGuests($u->id,$guests,$cost->id);
                    }

                    $DBObject->UpdateGroupUserAvgCooked($group);
                    $DBObject->UpdateGroupUserSaldo($group->id);

                    header ('Location: '.$settings['site_url'].'cost-history-all/'.$_GET['gid'].'/');
                }
                else{
                    $showform = true;
                    $notification = '<div class="error-bar">'.$lang['AMOUNT_NEEDS_TO_BE_POSITIVE'].'<br></div>';
                }
            }
            else{
                $showform = true;
                $notification = '<div class="error-bar">'.$lang['AMOUNT_IS_NOT_VALID'].'<br></div>';
            }
        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

if($showform){
    $explode = explode('-',$cost->date);
    $date = $explode[2].'-'.$explode[1].'-'.$explode[0];
    echo '<div class="normal-content"><div class="pure-g">';
    if(isset($notification)) {
        echo $notification;
    }
    echo '<div class="l-box pure-u-3-4">
        <h1>'.$lang['EDIT_COST'].'</h1>
        <form method="post" action="">
        <span class="clear">
	        <label>'.$lang['DESCRIPTION'].'</label> <input type="text" name="description" value="'.$cost->description.'" tabindex="1">
	    </span>
	    <span>
            <label>'.$lang['AMOUNT'].': </label>
            <span>'.$group->currency.'</span> <input type="text" name="amount" class="amount" id="basicCalculator" value="'.$cost->amount.'" tabindex="2" />
        </span>
        <span>
            <label>'.$lang['PAID_BY'].': </label>
            <select name="uid" tabindex="3">';
    foreach ($users as $u){
        echo '<option value="'.$u->id.'" '. ($u->id == $user->id ? "SELECTED" : '') .' >'.$u->firstName.'</option>';
    }
    echo '</select>
        </span>
        <span class="clear">
		    <label>'.$lang['DATE'].'</label> <input type="text" name="date" id="datepicker" tabindex="4">
		</span>
        <span class="clear">
		<label>'.$lang['WHO_PAYS_WITH'].'</label>
            <ul class="who-pays">
            ';
        foreach( $users as $u ){
            $guests = $cost->GetCostGuests($cost->id,$u->id);
            echo '<li>' .numberArray('number['.$u->id.']', $guests).'<span>'.$u->getFullName().'</span></li> ';
        }
        echo '
            </ul>
		</span>
		<span class="clear">
		    <input type="button" id="btn-everyone-1" class="secundaire-btn" value="'.$lang['EVERYONE_ONE'].'" onclick=""/> <input type="button" id="btn-everyone-0" class="secundaire-btn" value="'.$lang['EVERYONE_ZERO'].'" onclick=""/>
		</span>

        <span class="clear">
            <input type="submit" class="alt_btn" value="'.$lang['SAVE'].'" tabindex="10">
            <input type="button" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'cost/'.$_GET['gid'].'\'" tabindex="11"/>
        </span>
        </form>
    </div>';
    /*
        <span class="clear">
            <label>'.$lang['IS_DINNER'].': </label>'.yesNoArray('is_dinner', $cost->isDinner).'
        </span>
     */
    echo '<aside class="l-box pure-u-1-4">';
    $group->ShowSideBarStickyNotes();
    $group->ShowSideBarSettings();
    echo '		</aside>';
	echo '	</div>';
	echo '</div>';
}
else{
    header ('Location: '.$settings['site_url'].'cost/'.$_GET['gid']);
}

include_once("inc/footer.inc.php");
?>