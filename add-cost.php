<?php
/**
 * User: Roel Verdonschot
 * Date: 7-8-13
 * Time: 20:50
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_ADDCOST'];
require_once("inc/header.inc.php");
$showform = false;
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
    $showform = false;
}
if(isset($_POST['date'])){
    if (preg_match("/[0-9]{2}-[0-9]{2}-[0-9]{4}/", $_POST['date']))
    {
        $date = $_POST['date'];
    }
    else{
        $date = '';
    }

}
else{
    $date = '';
}
?>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
   <script> var jQuery_1_9_1 = $.noConflict(true);</script>
  <script>
      jQuery_1_9_1(function() {
          jQuery_1_9_1( "#datepicker" ).datepicker();
          jQuery_1_9_1( "#datepicker" ).datepicker("option", "dateFormat", "dd-mm-yy");
          jQuery_1_9_1( "#datepicker" ).datepicker( "setDate", "<?php echo $date ?>" );
    });
  </script>
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
			
			jQuery_1_11_0('.userSelect').click(function(){ 
				$(".who-pays :input").each(function(){
					var context = $(this);
					context.val('0');
				});
				var button = $(this);
				button.blur();
				var userIds = button.attr("name");
				var users = JSON.parse(userIds);
				users.forEach(function(entry) {
					console.log(entry);
					// TODO test if uid does not exists.
					var element = document.getElementById('number['+entry+']');
					element.value = 1;
				});
			});

            jQuery_1_11_0('.everyone').click(function () {
                updatePrices();
            });

            jQuery_1_11_0('#uid').change(function () {
                updatePrices();
            });

            jQuery_1_11_0('#basicCalculator').change(function () {

                updatePrices();
            });

            jQuery_1_11_0('.numbers').change(function () {
                updatePrices();
            });

            // tt
            function updatePrices(){
                var optionSelected = jQuery_1_11_0('#uid').find("option:selected");
                var payer  = optionSelected.val();
                var amount = jQuery_1_11_0('#basicCalculator').val();
                amount = amount.replace(",", ".");
                $('#payedByString').text("Betaald door:");
                if(amount < 0){
                    $('#payedByString').text("Boete voor:");
                    amount = Math.abs(amount);
                }
                var userIds = JSON.parse($("#userIds").val());
                var totalPayers = 0;
                userIds.forEach(function(entry) {
                    var element = document.getElementById('number['+entry+']');
                    if(element.value > 0){
                        totalPayers = parseFloat(totalPayers) + parseFloat(element.value);
                    }
                });

                var pricePerPayer = amount / totalPayers;
                console.log(amount);
                console.log(totalPayers);
                console.log(pricePerPayer);
                userIds.forEach(function(entry) {
                    var element = document.getElementById('number['+entry+']');
                    var e = '#price'+entry;
                    if(element.value > 0){
                        var p = pricePerPayer * element.value;
                        if(parseInt(entry) == parseInt(payer)){
                            p = p - amount;
                        }
                        var toAdd = '';
                        if(p > 0){
                            toAdd = '+';
                            $( e ).removeClass( "txtPositive txtNegative" ).addClass( "txtPositive" );
                        }
                        else{
                            $( e ).removeClass( "txtPositive txtNegative" ).addClass( "txtNegative" );
                        }
                        $(e).text(toAdd+p.toFixed(2).toString().replace(".",","));
                    }
                    else{
                        $(e).text('');
                    }
                });
            }
			
        });
    </script>
<?php
if(isset($_GET['gid'])){
    $user = Authentication_Controller::GetAuthenticatedUser();
	$group = new Group;
    if($group->AuthenticationGroup($user, $_GET['gid'])){
        $group = $group->GetGroupById($_GET['gid']);
        $users = $group->getUsers();
        $showform = true;
        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            $showform = false;
            $error = '';
            $amount = $_POST["amount"];
            $description = $_POST["description"];
            if($group->checkUser($_POST['uid'])){
                $uid = $_POST["uid"];
            }
            else{
                $uid = $user->id;
            }

            if (preg_match("/[0-9]{2}-[0-9]{2}-[0-9]{4}/", $_POST['date']))
            {
                list($dd,$mm,$yyyy) = explode('-',$_POST['date']);
                if (checkdate($mm,$dd,$yyyy)) {
                    $date = $yyyy.'-'.$mm.'-'.$dd;
                }
            }
            if(!isset($date)){
                $showform = true;
                $error.= ' - '.$lang['INPUT_DATE_NOT_CORRECT'].'<br />';
            }

            if($amount == null){
                $showform = true;
                $error.= ' - '.$lang['AMOUNT_MISSING'].'<br />';
            }

            $instant = new Cost();
            $amount = str_replace(',', '.', $amount);
            if(!is_numeric($amount)){
                $showform = true;
                $error.= ' - '.$lang['AMOUNT_IS_NOT_VALID'].'<br />';
            }

            if($description == null){
                $showform = true;
                $error.= ' - '.$lang['DISCRIPTION_MISSING'].'<br />';
            }

            /*if($amount < 0){
                ?>
                <script language="JavaScript" type="text/javascript">
                    var answer = confirm("A negative amount will count as a fine. is the amount correct?");
                    if (!answer){
                        <?php
                        $showform = true;
                        echo '<div class="error-bar">'.$lang['AMOUNT_NEEDS_TO_BE_POSITIVE'].'<br></div>';
                        ?>
                    }

                </script>
                <?php
            }*/

            $count = 0;
            foreach( $users as $u ){
                $guests = $_POST["number"][$u->id];
                if($guests == '0' || $guests == 0){
                    $count++;
                }
            }

            if(COUNT($users) == $count){
                $showform = true;
                $error.= ' - '.$lang['SELECT_ATLEAST_ONE_GROEPMEMBER'].'<br />';
            }

            if($showform === false){
                $costId = $instant->AddCost($amount,$description,$uid,$group,0,$date);
                // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                $lb = new Logbook(null, $group->id, $user->id, null, $costId, null, null, 'CA', null);
                $DBObject->AddLogbookItem($lb);
                foreach( $users as $u ){
                    $guests = $_POST["number"][$u->id];
                    if($guests != 0){
                        $instant->AddUserCost($u,$costId,$guests);
                    }
                }
                $DBObject->UpdateGroupUserSaldo($group->id);
                header ('Location: '.$settings['site_url'].'cost/'.$_GET['gid'].'/added');
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
		<div class="l-box pure-u-3-4">';
    if(!empty($error))
    {
        echo '<div class="error-bar">'.$error.'</div>';
    }
    $allIds = array();
    foreach( $users as $u ){
        $allIds[] =$u->id;
    }
    $ppl = array();
    echo '<h1>'.$lang['GROUP'].': '.$group->name.' '.$lang['MENU_COSTMANAGEMENT'].'</h1>
        <p>'.$lang['ADD_COSTS_HERE'].'</p>
        <input type="hidden" id="userIds" value="'.json_encode($allIds).'" />
	    <form method="post" action="">

        <span class="clear">
	    <label>'.$lang['DESCRIPTION'].'</label> <input type="text" name="description" value="'.(isset($_POST['description']) ? $_POST['description'] : '').'" tabindex="1">
	    </span>
	    <span>
            <label>'.$lang['AMOUNT'].': </label>
            <span>'.$group->currency.'</span> <input type="text" name="amount" class="amount" id="basicCalculator" value="'.(isset($_POST['amount']) ? $_POST['amount'] : '').'" tabindex="2" />
        </span>
        <span>
            <label id="payedByString">'.$lang['PAID_BY'].': </label>
            <select name="uid" tabindex="3" id="uid">';
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
                echo '<li>' .numberArray('number['.$u->id.']', 0).'<span>'.$u->getFullName().'</span><span id="price'.$u->id.'" style="font-size:13px;"></span></li> ';
            }
            echo '
            </ul>
		</span>
		<span class="clear">
		    <input type="button" id="btn-everyone-1" class="secundaire-btn everyone" value="'.$lang['EVERYONE_ONE'].'" onclick=""/></span> <span><input type="button" id="btn-everyone-0" class="secundaire-btn everyone" value="'.$lang['EVERYONE_ZERO'].'" onclick=""/></span>';
			$costSubGroups = $DBObject->GetCostSubGroups($group->id);
			if(isset($costSubGroups) && COUNT($costSubGroups) > 0){
				foreach($costSubGroups as $csg){
					echo '<span class=""><input type="button" class="secundaire-btn userSelect everyone" name="'.json_encode($csg->userIds).'" value="'.$csg->name.'" onclick="" /></span>';
				}
			}
		echo '
        <span class="clear">
            <input type="submit" class="alt_btn" value="'.$lang['SAVE'].'" tabindex="5" />
            <input type="button" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'cost/'.$_GET['gid'].'\'" tabindex="6"/>
		</span></form>';
    echo '</div>
    <aside class="l-box pure-u-1-4">';
    $group->ShowSideBarStickyNotes();
    $group->ShowSideBarSettings();
	echo '		</aside>';
echo '	</div>';
echo '</div>';
}
else{
     header ('Location: '.$settings['site_url'].'cost/'.$_GET['gid'].'/added');
}

include_once("inc/footer.inc.php");
?>