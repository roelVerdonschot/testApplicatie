<?php
/**
 * User: roel
 * Date: 16-10-13
 * Time: 13:25
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_COST'];
$_page_description = $lang['DESCRIPTION_DEMO_COST'];
$_page_keywords = $lang['KEYWORDS_DEMO_COST'];
$_demo = true;
require_once("inc/header.inc.php");

$error = null;
$group = new Group;
$group = $group->GetGroupById($settings['DEFAULT_DEMO_GROUP']);
$uidBool = false;
if(isset($_GET['uid'])){
    $uidBool = true;
    if(!$group->checkUser($_GET['uid'])){
        $uidBool = false;
    }
    if(!$group->AuthenticationGroupId($_GET['uid'], $settings['DEFAULT_DEMO_GROUP'])){
        $uidBool = false;
    }
    if($_GET['uid'] == $settings['DEFAULT_DEMO_USER']){
        $uidBool = false;
    }
}
if($uidBool == false){
    $uid = $settings['DEFAULT_DEMO_USER'];
}
else{
    $uid = $_GET['uid'];
}
$showform = true;
$instant = new Cost();
$errorData = array();
if(isset($_GET['date'])){
    if (preg_match("/[0-9]{2}-[0-9]{2}-[0-9]{4}/", $_GET['date']))
    {
        list($dd,$mm,$yyyy) = explode('-',$_GET['date']);
        if (checkdate($mm,$dd,$yyyy)) {
            $date = $yyyy.'-'.$mm.'-'.$dd;
            $idcost = $instant->AddCost(0, $lang['NO_DINNER_COST_DESC'], $uid,$group,1,$date);
            $DBObject->AddDinnerCost($group->id,$date,$idcost);
        }
        header ('Location: '.$settings['site_url'].$lang['_LANG_CODE'].'/demo-cost-management/'.$uid);
    }
}
if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    if(!isset($_POST['stickynote-title']) || !isset($_POST['stickynote-message'])){
        $description = $_POST['description']; //isset($_POST['description']) ? $_POST['description'] : '';
        $amount = $_POST['amount']; // isset($_POST['amount']) ? $_POST['amount'] : '';
        $date = isset($_POST['date']) ? $_POST['date'] : '';

        $userId = $_POST['uid'];
        $isnumric = true;
        $count = -1;
        foreach($amount as $a){
            $count++;
            $a = str_replace(',', '.', $a);
            if(!is_numeric($a)){
                $isnumric = false;
                $errorData[$date[$count]] = $date[$count];
            }
            if($a <= 0){
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

        if($instant->AddCosts($date, $amount, $description, $userId,$group,$settings['DEFAULT_DEMO_USER'])){
           // header ('Location: '.$settings['site_url'].'demo-cost-management/'.$uid);
            $DBObject->UpdateGroupUserAvgCooked($group);
            $DBObject->UpdateGroupUserSaldo($group->id);
        }
        else{
            $error[] = $lang['SOMETHING_WENT_WRONG_TRY_AGAIN'];
        }
    }
}
?>
    <link rel="stylesheet" type="text/css" href="<?php echo $settings['site_url']; ?>calc/jquery.calculator.css">
<?php
echo '<div class="normal-content">
	<div class="pure-g">
		<div class="pure-u-3-4">';
if(isset($_GET['cost']) || isset($_POST['stickynote-title']) || isset($_POST['stickynote-message'])){
    echo '<div class="notification-bar">'.$lang['DEMO_STICKYNOTES'].'</div>';
}
/*
if($error != null)
{
    echo '<div class="error-bar">';
    foreach($error as $string)
        echo ' - '.$string.'<br />';
    echo '</div>';
}
*/
$userSaldo = $DBObject->GetGroupUserSaldo($group->id);
$userDate = null;
$users = $group->getUsers();

echo '<div class="l-box-top pure-u-1">
	<h1>'.$lang['DEMO'].' '.$lang['MENU_COSTMANAGEMENT'].'</h1>
</div>';

echo ' <div class="l-box left-sidebar pure-u-1-4">
               <ul>';
if(isset($userSaldo)){
    foreach($users as $u){
        $date = $DBObject->CheckCostDinner($u->id,$group->id);
        $name = $DBObject->GetUserNameById($u->id);
        echo '<li'.($uid == $u->id ? ' class="selectedUser"' : '').'><a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-cost-management/'.$u->id.'/">'.(strlen($name) > 14 ? substr($name,0,12).'...' : $name).' '.((isset($date)) ? '('.COUNT($date).')' : ' ') ;
        if($userSaldo[$u->id][1] > 0 || $userSaldo[$u->id][1] == 0){
            echo '<span class="txtPositive">'.$group->currency.' '.$userSaldo[$u->id][1].'</span>';
        }
        else{
            echo '<span class="txtNegative">'.$group->currency.' '.$userSaldo[$u->id][1].'</span>';
        }
        echo '</a></li>';
    }
} else {
    foreach($users as $u){
        $date = $DBObject->CheckCostDinner($u->id,$group->id);
        echo '<li'.($uid == $u->id ? ' class="selectedUser"' : '').'><a href="'.$settings['site_url'].'demo-cost-management/'.$u->id.'/">'.$DBObject->GetUserNameById($u->id) .' '.((isset($date)) ? '('.COUNT($date).') ' : ' ') ;
        echo '<span class="txtPositive">'. $group->currency .' 0</span></a></li>';
    }
}
echo '</ul>
          </div> 
		  <div class="l-box middle-box pure-u-17-24">';
$userDate = $DBObject->CheckCostDinner($uid,$group->id);
$users = $group->getUsers();
if($userDate != null){
    echo '<h2 class="unpayed-dinners">'.$lang['DINNER_COST_STILL_NEEDED'].'</h2>
        <label>'.$lang['NO_DINNER_COST'].'</label>
        <form method="post" action="">';
    $calculatorCount = 0;
    foreach ($userDate as $ud){
        $explodeDate = explode("-",$ud[0]);
        $idusers = $DBObject->GetAllGuestsFromDinner($group->id,$ud[0]);
        $userstring = '';
        $count = 0;
        foreach ($idusers as $user){
            $userstring = $userstring.(COUNT($idusers) == 1 ? '' : ($count > 0 && $count < COUNT($idusers) -1 ? ', ' : '').($count == COUNT($idusers) -1 ? ' '.$lang['AND'].' ' : '')) . $user->nameUser.($user->numberOfPersons == 1 ? '' : ' x'.$user->numberOfPersons);
            $count++;
        }
        echo '<div class="dinner-cost '.(isset($errorData[$ud[0]])? ' dinner-cost-error': '').'">
                    <div>
						'.$explodeDate[2]."-".$explodeDate[1]."-".$explodeDate[0].' '.$lang['COOKED_FOR'].' '.$userstring.' <a href="'.$settings['site_url'].'demo-cost-management/demo">['.strtolower($lang['EDIT']).']</a> 
						<a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-cost-management/'.$uid.'/'.$explodeDate[2]."-".$explodeDate[1]."-".$explodeDate[0].'/" class="close-button">x</a>
					</div>


                    <span>
                        <label>'.$lang['DESCRIPTION'].': </label>
                        <input type="text" name="description[]" class="description-dinner-cost" value="'.($ud[1] != null ? $ud[1] : '' ).'">
                    </span>
                    <span>
                        <label>'.$lang['AMOUNT'].': </label>
                        <span>'.$group->currency.'</span> <input type="text" name="amount[]" id="basicCalculator'.$calculatorCount.'" class="amount" value="">
                    </span>
                    <span>
                        <label>'.$lang['PAID_BY'].': </label>
                        <select name="uid[]">';
        foreach ($users as $u){
            echo '<option value="'.$u->id.'" '. ($u->id == $uid ? "SELECTED" : '') .'>'.$DBObject->GetUserNameById($u->id) .'</option>';
        }
        echo '</select></span><input type="hidden" name="date[]" value="'.$ud[0].'" /></div>';
        $calculatorCount++;
    }
    echo '<span class="clear"><input type="submit" value="'.$lang['SAVE'].'"></span></form>';
}
echo '<h2 class="last-updates clear">'.$lang['LAST_UPDATES'].'</h2>';
$costs = $DBObject->GetLastCost($group->id);
if(isset($costs)){
    echo '<table border="1">
        <tr><th>'.$lang['PAID_BY'].'</th><th>'.$lang['DESCRIPTION'].'</th><th>'.$lang['AMOUNT'].'</th><th>'.$lang['DATE'].'</th><th>'.$lang['WHO_SHARE_COST'].'</th></th>';
    $w =0;
    foreach( $costs as $c ){
        ++$w;
        $explode = explode("-",$c->date);
        $CDate = $explode[2]."-".$explode[1]."-".$explode[0];
        echo '<tr '.($w % 2 ? '' : 'class="alt"').'><td>'.($c->deleted == '1' ? '<del>'.$DBObject->GetUserNameById($c->idUser).'</del>' : $DBObject->GetUserNameById($c->idUser)).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$c->description.'</del>' : $c->description).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$group->currency.' '.$c->amount.'</del>' : $group->currency.' '.$c->amount).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$CDate.'</del>' : $CDate).'</td>';
        $ids = $c->users;
        if(isset($ids)){
            echo '<td>'.($c->deleted == '1' ? '<del>': '');
            $count = 0;
            foreach($ids as $u){
                echo (COUNT($ids) == 1 ? '' : ($count > 0 && $count < COUNT($ids) -1 ? ', ' : '').($count == COUNT($ids) -1 ? ' '.$lang['AND'].' ' : '')).$DBObject->GetUserNameById($u->idUser).($u->numberOfPersons > 1 ? ' '.$u->numberOfPersons.'x' : '');
                ++$count;
            }
            echo ($c->deleted == '1' ? '</del></td></tr>': '</td></tr>');
        }
        else{
            echo '<td></td></tr>';
        }
    }
    echo "</table>";
}
else{
    echo $lang['NO_OPEN_COST'];
}

echo '</div>';

echo '</div>';
echo '<aside class="l-box pure-u-1-4">
    <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-cost-management/demo" class="buttonExtra">'.$lang['ADDCOST'].'</a>
    <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-cost-management/demo" class="buttonExtra">'.$lang['PAYOFF'].'</a>
    <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-cost-management/demo" class="buttonExtra">'.$lang['HISTORY'].'</a>
    <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-cost-management/demo" class="buttonExtra">'.$lang['IMPORT'].'</a><div class="clear"></div>';
$group->ShowSideBarDemoStickyNotes();
echo '<h2>'.$lang['IMPORT_COSTS'].'</h2>
        <div class="box">
        <p>'.$lang['IMPORT_PROMOTION'].'</p>
        <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/demo-cost-management/demo" class="btn btn-white">'.$lang['IMPORT_COSTS'].'</a></div><br />
        <div class="box">
                <p>'.$lang['MAKE_NEW_ACCOUNT'].'</P>
                <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/register" class="btn btn-white">'.$lang['REGISTER_NOW'].'</a>
            </div>
      </aside>';
echo '	</div>';
echo '</div>';
$user = null;
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
include_once("inc/footer.inc.php");
?>