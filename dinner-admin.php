<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_DINNER'];

$showform = false;
if($_GET['gid']){
    $user = $DBObject->GetUserById($_GET['uid']);
        $group = new Group();
        $group = $group->GetGroupById($_GET['gid']);
        // gets the users from the selected group
        //$users = $group->getUsers();

    // update every cookingpoint for this group
    $dinners = $DBObject->GetDinnerDates($_GET['gid']);
        if(isset($dinners)){
            //error_log("dinnsers: ".COUNT($dinners));
            foreach($dinners as $date){
                $DBObject->InsertOrUpdateTotalChefpoints($group->id,$date);
                // foreach id get all userId's, role, guests from today where role = 1 or 2
                $users = $DBObject->GetGroupDinners($date, $group->id);

                // check how many chefs
                if(isset($users)){
                    foreach($users as $u){
                        $DBObject->UpdateCookingPoints3($u[0],$group->id);
                        //error_log("UpdateCookingPoints3 UID: ".$u[0]);
                    }
                }
            }
        }
    $users = $group->getUsers();

        // if the user is not entering the data for hims/herself it'll get the user out of the url, else it'll be the current user
        if (isset($_GET['uid'])) {
            $tempUid = $_GET['uid'];
        }
        if(isset($_POST['uid'])){
            $tempUid = $_POST['uid'];
        }
        if (isset($tempUid)) {
            if($group->checkUser($tempUid)){
                $userData = $tempUid;
            }
            else{
                $userData = $user->id;
            }
        } else {
            $userData = $user->id;
        }

        // if there's a date in the url that'll be the date, otherwise it'll pick today's date
        $dateBool = true;
        if(isset($_POST['dinnerDate'])){
            $tempDate = $_POST['dinnerDate'];
        }
        elseif (isset($_GET['date'])) {
            $tempDate = $_GET['date'];
        }
        if (isset($tempDate)) {
            list($yyyy,$mm,$dd) = explode('-',$tempDate);
            if (checkdate($mm,$dd,$yyyy)) {
                //if( strtotime($tempDate) >= strtotime(date('Y-m-d')) &&  strtotime($tempDate) < strtotime(date('Y-m-d') . ' + 7 day')){
                    $dateValue = $tempDate;
                //}
                //else{
                //    $dateBool = false;
               // }
            }
            else{
                $dateBool = false;
            }
        } else {
            $dateValue = date('Y-m-d');
        }

        if(isset($_GET['tb'])){
            $set = $group->GetSetting('tbd',$user->id);
            if($set == null){
                $set = New Setting();
                $set->setKey('tbd');
                $set->setIdGroup($group->id);
                $set->setIdUser($user->id);
            }
            if($_GET['tb'] == 'top'){
                $set->setValue('top');
                $group->UpdateUserSetting($set);
                header('Location: ' . $settings['site_url'] . 'dinner/' . $group->id);
            }
            elseif($_GET['tb'] == 'left'){
                $set->setValue('left');
                $group->UpdateUserSetting($set);
                header('Location: ' . $settings['site_url'] . 'dinner/' . $group->id);
            }
        }

        // checks if there's a chef for the current day in the group
        $chef = $DBObject->CheckChef($dateValue, $group->id);

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
                        $DBObject->InsertLogbookItem($role,$userData,$dateValue,$nrOfPersons,$dateValue,$group,$user);
                    }
                }
            }
        }

        // checks if role is in the date, this happens after the post check, so if the button opslaan has been pressed
        // there's no role in the url
        // if the table has been clicked, there is
        if (isset($_GET['role'])) {
            $role = $_GET['role'];
            if(!$group->ClosingTimeExceeded($dateValue))
            {
                if($dateBool == true){
                    if($role == 1 || $role == 0 || $role == 2 || $role = -1){
                        $DBObject->UpdateDinner($group->id, $userData, $dateValue, $role);
                        $DBObject->InsertLogbookItem($role,$userData,$dateValue,0,$dateValue,$group,$user);
                        header ('Location: '.$settings['site_url'].'dinner/'.$group->id);
                    }
                }
            }
        }

        if (isset($users)) {
            $showform = true;
        } else {
            $showform = false;
        }

        // gets the dinner with the current date and current user in the current group for display in the box above the table
        $userDinner = $DBObject->GetDinnerByUserDate($dateValue, $group->id, $userData);
}

require_once("inc/header.inc.php");
$showform = true;
if ($showform) {
    if ($group->CheckDinner()) {
        echo '<div class="normal-content">
	<div class="pure-g">
		<div class="l-box pure-u-3-4">';
        $dinner = new Dinner(null,null,null,null,null,null);
		$weeks = (isset($_GET['p']) ? $_GET['p'] : 1);
        //$weeks = ($weeks <= 0 ? 0 : $weeks);
        $dinnerChef = isset($userDinner) && $userDinner->idRole == 2;
        if($group->ClosingTimeExceeded(date('Y-m-d')))
        {
            $var = $group->GetDinnerClosingTime();
            echo '<div class="error-bar">'.$lang['CLOSING_TIME_EXCEEDED'].' ('.(isset($var)?$var->value:'').')</div>';
        }
        if($dateBool == false){
            echo '<div class="error-bar">'.$lang['INCORRECT_DATE'].'</div>';
        }
        echo '<h1>'.$lang['MENU_DINNER_LIST'].'</h1>
        <p><a href="'.$settings['site_url'].'group/'.$group->id.'/">&larr; '.$lang['BACK_TO_GROUPPAGE'].'</a></p>';
        if(isset($_GET['tb']) && $_GET['tb'] == 'stats'){
            //echo '<a class="buttonLink" href="' . $settings['site_url'] . 'dinner/' . $group->id . '"><img src="' . $settings['site_url'] . 'images/overview.jpg" alt="'.$lang['OVERVIEW'].'" /></a>';
            echo '<div class="double-scroll">';
            $dinner->ShowStatTableLeft($users,$group);
            echo '</div>';
        }
        else
        {
            echo '
            <form method="post" action="">';

            //echo '<a class="buttonLink" href="' . $settings['site_url'] . 'dinner/' . $group->id . '/stats"><img src="' . $settings['site_url'] . 'images/statistics.jpg" alt="'.$lang['STATS_TABLE'].'" /></a>';
            echo '<h2 class="clearnone"><select name="dinnerName" id="dinnerName" class="dinnerName" style="margin-right: 5px;">';
            foreach($group->getUsers() as $u){

                if (isset($userData) && $userData == $u->id) {
                    $thisExtra = " SELECTED";
                } else {
                    $thisExtra = "";
                }
                echo '<option value="'.$u->id.'"' . $thisExtra . '>' . $u->getFullName() .'</option>';
            }
            echo '</select>' . dateArray('dinnerDate', (isset($dateValue) ? $dateValue : 0), $weeks). '</h2>';
           /* if (isset($userData) && isset($dateValue)) {
                $dateV = explode("-",$dateValue);
                $dateV = $dateV[2].'-'.$dateV[1].'-'.$dateV[0];
                echo '<h2 class="clearnone"><span id="dinnerName">' . $DBObject->GetUserNameById($userData) . '</span> ' . dateArray('dinnerDate', $dateValue) . '</h2>';
            } else {
                echo '<h2 class="clearnone"><span id="dinnerName">' . $user->getFullName() . '</span> ' . dateArray('dinnerDate', 0) . '</h2>';
            }*/

            $bool = isset($userDinner) && ($userDinner->idRole == 1 || $userDinner->idRole == 2);
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
                    <label>'.$lang['NUMBER_OF_PPL'].'</label>' . numberArray('nrOfPersons', ($numberOfPersons), 1) . '
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
                    <input name="Description" id="inputDescription" type="text" value="'.(isset($userDinner) && $userDinner->description != null ? $userDinner->description : '').'" />
                </span>
                <input type="hidden" name="uid" value="'.$userData.'" id="uid" />
                <span class="right-bottom"><input type="submit" name="opslaan" class="submit-btn" value="'.$lang['SAVE'].'" /></span>
            </div>
            </form>';
			// ($weeks == 0 ? '<br /><br />' : )
			echo '<input type="button" class="alt_btn" value="Vorige week" onclick="window.location.href=\''.$settings['site_url'].'dinner-admin.php?uid='.$_GET['uid'].'&gid='.$group->id.'&p='.($weeks -1).'/\'"/> ';
			echo '<input type="button" class="alt_btn" value="Volgende week" onclick="window.location.href=\''.$settings['site_url'].'dinner-admin.php?uid='.$_GET['uid'].'&gid='.$group->id.'&p='.($weeks +1).'/\'"/><br /><br />';
            echo '<div class="double-scroll">';

            $set = $group->GetSetting('tbd',$user->id);


            if($set != null){
                if($set->value == 'top'){
					if($weeks <= 0){
						$dinner->ShowDinnerTableHistoryTop($users,$group,$weeks);
					}
					else{
						$dinner->ShowDinnerTableTop($users,$group,$weeks);
					}
                }
                else{
					if($weeks <= 0){
						$dinner->ShowDinnerTableHistoryLeft($users,$group,$weeks);
					}
					else{
						$dinner->ShowDinnerTableLeft($users,$group,$weeks);
					}
                }
            }
            else {
                if(count($users) > 8)
                {
					if($weeks <= 0){
						$dinner->ShowDinnerTableHistoryLeft($users,$group,$weeks);
					}
					else{
						$dinner->ShowDinnerTableLeft($users,$group,$weeks);
					}
                }
                else
                {
					if($weeks <= 0){
						$dinner->ShowDinnerTableHistoryTop($users,$group,$weeks);
					}
					else{
						$dinner->ShowDinnerTableTop($users,$group,$weeks);
					}
                }
            }
            echo '</div>';
        }
        echo '</div>
        <aside class="l-box pure-u-1-4">';
		if(isset($_GET['tb']) && $_GET['tb'] == 'stats'){
			echo '<a href="'.$settings['site_url'].'dinner/'.$group->id.'/" class="full-width buttonExtra">'.$lang['BACK'].'</a>';
		}
		else
		{
			echo '<a href="'.$settings['site_url'].'dinner/'.$group->id.'/stats/" class="full-width buttonExtra">'.$lang['STATS_TABLE'].'</a>';
		}
		echo '<div class="clear"></div>';
        $group->ShowSideBarStickyNotes();
        $group->ShowSideBarSettings();
        $group->ShowSideBarLegend();
        echo '		</aside>';
		echo '	</div>';
		echo '</div>';
    } else {
        echo '<div class="notification-bar">'.$lang['ERROR_DINNER_NOTACTIVE'].'<a href="'.$settings['site_url'].'settings-group/' . $group->id . '/">'.$lang['CLICKHERE'].'</a>'.$lang['EDIT_GROUP_MODULES'].'<br></div>';
    }
} else {
    header('Location: ' . $settings['site_url']);
}

include_once("inc/footer.inc.php");
?>