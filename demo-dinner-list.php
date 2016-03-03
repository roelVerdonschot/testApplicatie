<?php
/**
 * User: roel
 * Date: 16-10-13
 * Time: 13:26
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_DINNER'];
$_page_description = $lang['DESCRIPTION_DEMO_DINNER'];
$_page_keywords = $lang['KEYWORDS_DEMO_DINNER'];
$_demo = true;

$group = new Group();
$group = $group->GetGroupById($settings['DEFAULT_DEMO_GROUP']);
// gets the users from the selected group
$users = $group->getUsers();

$userData = $settings['DEFAULT_DEMO_USER'];
// if the user is not entering the data for hims/herself it'll get the user out of the url, else it'll be the current user
if(isset($_GET['uid'])){
    if(!$group->checkUser($_GET['uid'])){
        $userData = $settings['DEFAULT_DEMO_USER'];
    }
    elseif(!$group->AuthenticationGroupId($_GET['uid'], $settings['DEFAULT_DEMO_GROUP'])){
        $userData = $settings['DEFAULT_DEMO_USER'];
    }
    elseif($_GET['uid'] == $settings['DEFAULT_DEMO_USER']){
        $userData = $settings['DEFAULT_DEMO_USER'];
    }
    else
    {
        $userData = $_GET['uid'];
    }
}
else{
    $userData = $settings['DEFAULT_DEMO_USER'];
}
if(isset($_POST['uid'])){
    if(!$group->checkUser($_POST['uid'])){
        $userData = $settings['DEFAULT_DEMO_USER'];
    }
    elseif(!$group->AuthenticationGroupId($_POST['uid'], $settings['DEFAULT_DEMO_GROUP'])){
        $userData = $settings['DEFAULT_DEMO_USER'];
    }
    elseif($_POST['uid'] == $settings['DEFAULT_DEMO_USER']){
        $userData = $settings['DEFAULT_DEMO_USER'];
    }
    else
    {
        $userData = $_POST['uid'];
    }
}

// if there's a date in the url that'll be the date, otherwise it'll pick today's date
$dateBool = true;
if(isset($_POST['dinnerDate'])){
    list($yyyy,$mm,$dd) = explode('-',$_POST['dinnerDate']);
    if (checkdate($mm,$dd,$yyyy)) {
        if( strtotime($_POST['dinnerDate']) >= strtotime(date('Y-m-d')) &&  strtotime($_POST['dinnerDate']) < strtotime(date('Y-m-d') . ' + 7 day')){
            $dateValue = $_POST['dinnerDate'];
        }
        else{
            $dateBool = false;
        }
    }
    else{
        $dateBool = false;
    }
}
elseif (isset($_GET['date'])) {
    list($yyyy,$mm,$dd) = explode('-',$_GET['date']);
    if (checkdate($mm,$dd,$yyyy)) {
        if( strtotime($_GET['date']) >= strtotime(date('Y-m-d')) &&  strtotime($_GET['date']) < strtotime(date('Y-m-d') . ' + 7 day')){
            $dateValue = $_GET['date'];
        }
        else{
            $dateBool = false;
        }
    }
    else{
        $dateBool = false;
    }
} else {
    $dateValue = date('Y-m-d');
}

if(isset($_GET['tb'])){
    $set = $group->GetSetting('tbd',$settings['DEFAULT_DEMO_USER']);
    if($set == null){
        $set = New Setting();
        $set->setKey('tbd');
        $set->setIdGroup($settings['DEFAULT_DEMO_GROUP']);
        $set->setIdUser($settings['DEFAULT_DEMO_USER']);
    }
    if($_GET['tb'] == 'top'){
        $set->setValue('top');
        $group->UpdateUserSetting($set);
        header('Location: ' . $settings['site_url'] .$lang['_LANG_CODE']. '/demo-dinner-list');
    }
    elseif($_GET['tb'] == 'left'){
        $set->setValue('left');
        $group->UpdateUserSetting($set);
        header('Location: ' . $settings['site_url'] .$lang['_LANG_CODE']. '/demo-dinner-list');
    }
}

if(isset($dateValue)){
    // checks if there's a chef for the current day in the group
    $chef = $DBObject->CheckChef($dateValue, $group->id);
}

if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {

    // if the button opslaan has been pressed
    if (isset($_POST['opslaan'])) {
        if (isset($_POST['chefSwitch'])) {
            $role = 2;
        } else if (isset($_POST['dinnerSwitch'])) {
            $role = 1;
        } else if (!isset($_POST['dinnerSwitch'])) {
            $role = 0;
        }
        if(!$group->ClosingTimeExceeded($dateValue))
        {
            if(is_numeric($_POST['nrOfPersons'])){
                $nrOfPersons = $_POST['nrOfPersons'];
            }
            else{
                $nrOfPersons = 1;
            }

            $description=$_POST['Description'];
            if($dateBool == true){
                // updates the selected dinnerdata for the selected user on teh selected date
                $DBObject->UpdateAdvancedDinner($group->id, $userData, $dateValue, $role, $nrOfPersons,$description);

                if($role == DINNER_NOT_EATING){
                    $lbKey= 'ENW';
                    $lbValue = $DBObject->GetUserNameById($userData).'|'.$dateValue;
                }
                elseif($role == DINNER_JOIN_DINNER){
                    $lbKey= 'EW';
                    $lbValue = $DBObject->GetUserNameById($userData).'|'.$nrOfPersons.'|'.$dateValue;
                }
                elseif($role == DINNER_IS_COOK){
                    $lbKey= 'EC';
                    $lbValue = $DBObject->GetUserNameById($userData).'|'.$nrOfPersons.'|'.$dateValue;
                }
                elseif($role == DINNER_NOTHING_SET){
                    $lbKey= 'ENS';
                    $lbValue = $DBObject->GetUserNameById($userData).'|'.$dateValue;
                }
                // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                $lb = new Logbook(null, $group->id, $settings['DEFAULT_DEMO_USER'], null, $userData, $dateValue, null,$lbKey , $lbValue);
                $DBObject->AddDinnerLogbookItem($lb);
            }
        }
    }
}

// checks if role is in the date, this happens after the post check, so if the button opslaan has been pressed
// there's no role in the url
// if the table has been clicked, there is
if (isset($_GET['role'])) {
    $role = $_GET['role'];
    if(isset($dateValue)){
        if(!$group->ClosingTimeExceeded($dateValue))
        {
            if($dateBool == true){
                if($role >= -1 && $role <= 2){
                    $DBObject->UpdateDinner($group->id, $userData, $dateValue, $role);
                    if($role == DINNER_NOT_EATING){
                        $lbKey= 'ENW';
                        $lbValue = $DBObject->GetUserNameById($userData).'|'.$dateValue;
                    }
                    elseif($role == DINNER_JOIN_DINNER){
                        $lbKey= 'EW';
                        $lbValue = $DBObject->GetUserNameById($userData).'|0|'.$dateValue;
                    }
                    elseif($role == DINNER_IS_COOK){
                        $lbKey= 'EC';
                        $lbValue = $DBObject->GetUserNameById($userData).'|0|'.$dateValue;
                    }
                    elseif($role == DINNER_NOTHING_SET){
                        $lbKey= 'ENS';
                        $lbValue = $DBObject->GetUserNameById($userData).'|'.$dateValue;
                    }
                    // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                    $lb = new Logbook(null, $group->id, $settings['DEFAULT_DEMO_USER'], null, $userData, $dateValue, null,$lbKey , $lbValue);
                    $DBObject->AddDinnerLogbookItem($lb);

                    header ('Location: '.$settings['site_url'].$lang['_LANG_CODE'].'/demo-dinner-list');
                }
            }
        }
    }
}

if(isset($dateValue)){
    // gets the dinner with the current date and current user in the current group for display in the box above the table
    $userDinner = $DBObject->GetDinnerByUserDate($dateValue, $group->id, $userData);
}

require_once("inc/header.inc.php");

$dinner = new Dinner(null,null,null,null,null,null);
$dinnerChef = isset($userDinner) && $userDinner->idRole == DINNER_IS_COOK;

echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">';
if(isset($_POST['stickynote-title']) || isset($_POST['stickynote-message'])){
    echo '<div class="notification-bar">'.$lang['DEMO_STICKYNOTES'].'</div>';
}
if($group->ClosingTimeExceeded(date('Y-m-d')))
{
    $var = $group->GetDinnerClosingTime();
    echo '<div class="error-bar">'.$lang['CLOSING_TIME_EXCEEDED'].' ('.(isset($var)?$var->value:'').')</div>';
}
if($dateBool == false){
    echo '<div class="error-bar">'.$lang['INCORRECT_DATE'].'</div>';
}
      echo' <h1>'.$lang['DEMO'].' '.$lang['MENU_DINNER_LIST'].'</h1>';
    echo '<form method="post" action="">';

    if (isset($userData) && isset($dateValue)) {
        $dateV = explode("-",$dateValue);
        $dateV = $dateV[2].'-'.$dateV[1].'-'.$dateV[0];
        echo '<h2 class="clearnone"><span id="dinnerName">' . $DBObject->GetUserNameById($userData) . '</span> ' . dateArray('dinnerDate', $dateValue,1) . '</h2>';
    } else {
        echo '<h2 class="clearnone"><span id="dinnerName">' . $DBObject->GetUserNameById($settings['DEFAULT_DEMO_USER']) . '</span> ' . dateArray('dinnerDate', 0,1) . '</h2>';
    }

    $bool = isset($userDinner) && ($userDinner->idRole == 1 || $userDinner->idRole == DINNER_IS_COOK);
    $numberOfPersons = (isset($userDinner)) ? ($userDinner->NumberOfPersons) : 1;

    echo '
            <div class="dinner-info">';
    if(isset($chef) && $chef == 1){
        $whoCooksHtml = $group->showWhoCooksByDate($dateValue);
        if($whoCooksHtml != "")
        {
            echo '<span id="howIsChef">' . $whoCooksHtml . '</span>';
        }
    }
                echo '<span class="clear">
                    <label>'.$lang['EAT_WITH'].'</label>
                    <div class="onoffswitch">
                        <input type="checkbox" name="dinnerSwitch" class="onoffswitch-checkbox" id="dinnerSwitch" onclick="if (!this.checked) document.getElementById(\'chefSwitch\').checked = false; toggleDinner(\'dinnerSwitch\');" '.($bool ? 'checked ' : '' ). '/>
                        <label class="onoffswitch-label" for="dinnerSwitch">
                            <div class="onoffswitch-inner"></div>
                            <div class="onoffswitch-switch"></div>
                        </label>
                    </div>
                </span>
                <span>
                    <label>'.$lang['NUMBER_OF_PPL'].'</label>' . numberArray('nrOfPersons', ($numberOfPersons)) . '
                </span>
                <span class="clear">';

    if (isset($chef) && $chef == 1) {
        echo '<label>'.$lang['ARE_YOU_CHEF'].'</label>';
    } else {
        echo '<label>'.$lang['NO_CHEF_YET'].'</label>';
    }
    echo '
                    <div class="onoffswitch">
                    <input type="checkbox" name="chefSwitch" class="onoffswitch-checkbox" id="chefSwitch" onclick="toggleDinner(this)"'.($dinnerChef ? ' checked ' : '').'/>
                    <label class="onoffswitch-label" for="chefSwitch">
                        <div class="onoffswitch-inner"></div>
                        <div class="onoffswitch-switch"></div>
                    </label>
                    </div>
                </span>
                <span class="csDescription" style="'.(!$dinnerChef ? "display:none" : "").'">
                    <label name="lbDescription">'.$lang['WHATS_FOR_DINNER'].'</label>
                    <input name="Description" type="text" id="inputDescription" value="'.(isset($userDinner) && $userDinner->description != null ? $userDinner->description : '').'" />
                </span>
                <input type="hidden" name="uid" value="" id="uid" />
                <span class="right-bottom"><input type="submit" name="opslaan" class="submit-btn" value="'.$lang['SAVE'].'" /></span>
            </div>
            </form>';

    echo '<div class="double-scroll">';

    $set = $group->GetSetting('tbd',$settings['DEFAULT_DEMO_USER']);

if($set->value == 'top'){
    echo '<table><tr><th><a href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/left"><img src="' . $settings['site_url'] . 'images/arrow-2-ways.png" alt="switch" /></a></th><th></th>';

    foreach ($users as $value) {
        echo '<th>' . $value->firstName . '</th>';
    }
    echo '</tr>';


    for ($d = 0; $d < 7; $d++) {
        if ($d == 0) {
            echo ' <tr><th class="onepx">'.$lang['TODAY'].'</th>';
        }
        if ($d == 1) {
            echo '<tr><th class="onepx">'.$lang['TOMORROW'].'</th>';
        }
        if ($d > 1) {
            echo '<tr><th class="onepx">' . ucfirst(strftime('%a %d %b', strtotime(date('d-m-Y') . ' + ' . $d . ' day'))) . '</th>';
        }

        $date = date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day'));
        $time = $DBObject->GetGroupDinnerTime($date, $settings['DEFAULT_DEMO_GROUP']);
        echo '<td class="dinnertime" ><span name="'.$settings['DEFAULT_DEMO_GROUP'].'" id="itm'.$d.'" >'.(isset($time) ? $time : '18:00').'</span><input name="'.$date.'" id="itm'.$d.'b" class="replace" type="text" value=""></td>';

        // get an array with dinners, each dinner contains a user, his/her role, and his/her number of guests
        $dinners = $DBObject->GetDinnerByDate($group->id, date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day')));

        // loops trough each user in the group
        foreach ($users as $value) {
            // if the user hasnt made a decision yet, requiresInput is 1
            $requiresInput = 1;
            if ($dinners != null) {
                // loops trough each dinner
                foreach ($dinners as $key => $dinval) {
                    // if the user is found in dinner set requiresInput on 0,
                    //  if the user is not found requiresInput = 1 and said user does not attend
                    if ($value->id == $key) {
                        $requiresInput = 0;

                        switch ($dinval->idRole) {
                            case DINNER_NOTHING_SET:
                                echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day')) . '&quot;, &quot;role&quot;:&quot;'.DINNER_JOIN_DINNER.'&quot;}" class="nothingimg dinnerClick" href="' . $settings['site_url'] .$lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day')) . '/'.DINNER_JOIN_DINNER.'">&nbsp;</a></td>';
                                break;
                            case DINNER_NOT_EATING:
                                echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;'.DINNER_NOTHING_SET.'&quot;}" class="noeatimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/'.DINNER_NOTHING_SET.'">&nbsp;</a></td>';
                                break;
                            case DINNER_JOIN_DINNER:
                                echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;'.DINNER_IS_COOK.'&quot;, &quot;nopersons&quot;:&quot;'.$dinval->NumberOfPersons.'&quot;}" class="eatimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/'.DINNER_IS_COOK.'">'.($dinval->NumberOfPersons >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').'</a></td>';
                                break;
                            case DINNER_IS_COOK:
                                echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;'.DINNER_NOT_EATING.'&quot;}" class="chefimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/'.DINNER_NOT_EATING.'">'.($dinval->NumberOfPersons >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').'</a></td>';
                                break;
                        }
                    }
                }
                if ($requiresInput == 1) {
                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;1&quot;}" class="nothingimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/'.DINNER_JOIN_DINNER.'">&nbsp;</a></td>';
                }

            } else {
                echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day')) . '&quot;, &quot;role&quot;:&quot;1&quot;}" class="nothingimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day')) . '/'.DINNER_JOIN_DINNER.'">&nbsp;</a></td>';
            }
        }
        echo '</tr>';

    }

    // STATISTICS
    $dinnerStats = $DBObject->GetDinnerStatistics($group->id);
    $dinnerStats2 = $DBObject->GetGroupUserAvgCookingCost($group->id);
    echo '<tr>
            <td colspan="2">'.$lang['COOKED'].'</td>';
    foreach ($users as $u) {
        if(isset($dinnerStats[$u->id][DINNER_IS_COOK])) // 2 staat voor dinner role
        {
            echo '<td>'.$dinnerStats[$u->id][DINNER_IS_COOK]->getCount().'</td>';
        }
        else
        {
            echo '<td>0</td>';
        }
    }

    echo '</tr>
        <tr>
        <td colspan="2">'.$lang['DINNERS_JOINED'].'</td>';
    foreach ($users as $u) {
        if(isset($dinnerStats[$u->id][DINNER_JOIN_DINNER])) // 1 staat voor dinner role
        {
            echo '<td>'.$dinnerStats[$u->id][DINNER_JOIN_DINNER]->getCount().'</td>';
        }
        else
        {
            echo '<td>0</td>';
        }
    }

    echo '</tr>
        <tr>
        <td colspan="2">'.$lang['RATIO'].'</td>';
    foreach ($users as $u) {
        if(isset($dinnerStats[$u->id][DINNER_JOIN_DINNER]) && isset($dinnerStats[$u->id][DINNER_IS_COOK])) // 1 staat voor dinner role
        {
            echo '<td>'.number_format((float)round(($dinnerStats[$u->id][DINNER_IS_COOK]->getCount() / $dinnerStats[$u->id][DINNER_JOIN_DINNER]->getCount()),2), 2, '.', '').'</td>';
        }
        else
        {
            echo '<td>0</td>';
        }
    }

    echo '</tr>
        <tr>
        <td colspan="2">'.$lang['AVG_DINNERCOST'].'</td>';
    foreach ($users as $u) {
        echo '<td>'.$group->currency.' '.(isset($dinnerStats2[$u->id]) ? $dinnerStats2[$u->id][DINNER_JOIN_DINNER] : 0.00).'</td>';
    }
    echo '</tr>

        <tr>
        <td colspan="2">'.$lang['AVG_PPL_JOINING'].'</td>';
    foreach ($users as $u) {
        $avgMeeEters = $DBObject->GetDinnerrStaticsAvgMeeEters($group->id,$u->id);
        echo '<td>'.$avgMeeEters.'</td>';
    }
    echo '</tr>

        <tr>
        <td colspan="2">'.$lang['COOKING_POINTS'].'</td>';
    foreach ($users as $u) {
        echo '<td>'.(isset($dinnerStats2[$u->id]) ? $dinnerStats2[$u->id][DINNER_IS_COOK] : 0).'</td>';
    }

    echo '</tr></table>';
}
else{
    echo '<table><tr><th><a href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/top"><img src="' . $settings['site_url'] . 'images/arrow-2-ways.png" alt="switch" /></a></th>';

    //foreach ($users as $value) {
    // echo '<th>' . $value->firstName . '</th>';
    //}
    ///echo '</tr>';
    echo '<th>'.$lang['TODAY'].'</th><th>'.$lang['TOMORROW'].'</th>';

    for ($d = 2; $d < 7; $d++) {
        echo '<th>' . ucfirst(strftime('%a %d %b', strtotime(date('d-m-Y') . ' + ' . $d . ' day'))) . '</th>';

    }
    echo '</tr>';
    echo '<tr><th></th>';
    for ($d = 0; $d < 7; $d++) {
        $date = date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day'));
        $time = $DBObject->GetGroupDinnerTime($date, $settings['DEFAULT_DEMO_GROUP']);
        echo '<td class="dinnertime" ><span name="'.$settings['DEFAULT_DEMO_GROUP'].'" id="itm'.$d.'" ">'.(isset($time) ? $time : '18:00').'</span><input name="'.$date.'" id="itm'.$d.'b" class="replace" type="text" value=""></td>';
    }
    echo '</tr>';
    foreach ($users as $value) {
        echo '<th>' . $value->firstName . '</th>';
        for ($d = 0; $d < 7; $d++) {
            // get an array with dinners, each dinner contains a user, his/her role, and his/her number of guests
            $dinners = $DBObject->GetDinnerByDate($group->id, date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day')));
            // if the user hasnt made a decision yet, requiresInput is 1
            $requiresInput = 1;
            if ($dinners != null) {
                // loops trough each dinner
                foreach ($dinners as $key => $dinval) {
                    // if the user is found in dinner set requiresInput on 0,
                    //  if the user is not found requiresInput = 1 and said user does not attend
                    if ($value->id == $key) {
                        $requiresInput = 0;

                        switch ($dinval->idRole) {
                            case DINNER_NOTHING_SET:
                                echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day')) . '&quot;, &quot;role&quot;:&quot;'.DINNER_JOIN_DINNER.'&quot;}" class="nothingimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day')) . '/'.DINNER_JOIN_DINNER.'">&nbsp;</a></td>';
                                break;
                            case DINNER_NOT_EATING:
                                echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;'.DINNER_NOTHING_SET.'&quot;}" class="noeatimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/'.DINNER_NOTHING_SET.'">&nbsp;</a></td>';
                                break;
                            case DINNER_JOIN_DINNER:
                                echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;'.DINNER_IS_COOK.'&quot;, &quot;nopersons&quot;:&quot;'.$dinval->NumberOfPersons.'&quot;}" class="eatimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/'.DINNER_IS_COOK.'">'.($dinval->NumberOfPersons >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').'</a></td>';
                                break;
                            case DINNER_IS_COOK:
                                echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;'.DINNER_NOT_EATING.'&quot;}" class="chefimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/'.DINNER_NOT_EATING.'">'.($dinval->NumberOfPersons >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').'</a></td>';
                                break;
                        }
                    }
                }
                if ($requiresInput == 1) {
                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;'.DINNER_JOIN_DINNER.'&quot;}" class="nothingimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/'.DINNER_JOIN_DINNER.'">&nbsp;</a></td>';
                }

            } else {
                echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id . '&quot;, &quot;date&quot;:&quot;' . date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day')) . '&quot;, &quot;role&quot;:&quot;'.DINNER_JOIN_DINNER.'&quot;}" class="nothingimg dinnerClick" href="' . $settings['site_url'] . $lang['_LANG_CODE']. '/demo-dinner-list/' . $group->id . '/' . $value->id . '/' . date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day')) . '/'.DINNER_JOIN_DINNER.'">&nbsp;</a></td>';
            }
        }
        echo '</tr>';
    }
    echo '    </table>';
}

    echo '</div>';


echo '</div>
        <aside class="l-box pure-u-1-4">
       ';
$group->ShowSideBarDemoStickyNotes();
echo '<div class="box">
                <p>'.$lang['MAKE_NEW_ACCOUNT'].'</P>
                <a href="'.$settings['site_url'].$lang['_LANG_CODE'].'/register" class="btn btn-white">'.$lang['REGISTER_NOW'].'</a>
            </div>';
    $group->ShowSideBarLegend();

echo '		</aside>';
echo '	</div>';
echo '</div>';
include_once("inc/footer.inc.php");
?>