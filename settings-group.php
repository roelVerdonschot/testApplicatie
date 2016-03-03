<?php
/**
 * User: Roel Verdonschot
 * Date: 7-8-13
 * Time: 2:34
 */
ob_start();
require_once("inc/config.inc.php");

$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_EDITGROUP'];
require_once("inc/header.inc.php");

if (!Authentication_Controller::IsAuthenticated()) {
    header('Location: ' . $settings['site_url'] . 'login');
} else {
        echo '<div class="normal-content">
        <div class="pure-g">';
    if (isset($_GET['gid'])) {
        $user = Authentication_Controller::GetAuthenticatedUser();
        $group = new Group;
        $errorBarTime = false;
        $errorBarName = false;

        if ($group->AuthenticationGroup($user, $_GET['gid'])) {
            if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
                $showform = true;
            } else {
                $group = $group->GetGroupById($_GET['gid']);
                $showform = false;
                $user = Authentication_Controller::GetAuthenticatedUser();
                $modules = 0;
                if (isset($_POST['cost'])) {
                    $modules = $modules + 1;
                }
                if (isset($_POST['dinner'])) {
                    $modules = $modules + 2;
                    if(!empty($_POST["tbClosingTime"]))
                    {
                        if (preg_match("/0[1-9]|1[0-9]|2[0-3]:[0-5][0-9]$/", $_POST['tbClosingTime'])){
                            if (($timestamp = strtotime($_POST["tbClosingTime"]) === false)) {
                                $errorBarTime = true;
                                $showform = true;
                            } else {
                                $dctsSetting = new Setting();
                                $dctsSetting->setValue($_POST["tbClosingTime"]);
                                $dctsSetting->setKey("dcts");
                                $dctsSetting->setIdGroup($group->id);
                                $group->UpdateSetting($dctsSetting);
                            }
                        }
                        else{
                            $errorBarTime = true;
                            $showform = true;
                        }
                    }
                }
                if (isset($_POST['task'])) {
                    $modules = $modules + 4;
                }
                if (isset($_POST['datepicker'])) {
                    $modules = $modules + 8;
                }
                $oldGroupsName = $group->name;
                if(strlen(trim($_POST['name'])) <= 2) {
                    $errorBarName = true;
                    $showform = true;
                    $_POST['name'] = $oldGroupsName;
                }

                $group->setName($_POST['name']);
                $group->setLocation($_POST['location']);
                $group->setCurrency($_POST['currency']);
                $oldGroupsModules = $group->modules;
                $group->setModules($modules);
                $group->EditGroupData($group);
                if($oldGroupsName != $group->name){
                    // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                    $lb = new Logbook(null, $group->id, $user->id, null, null, null, null, 'GNE', $oldGroupsName);
                    $DBObject->AddLogbookItem($lb);
                }

                if($oldGroupsModules != $group->modules){
                    // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                    $lb = new Logbook(null, $group->id, $user->id, null, null, null, null, 'GME', null);
                    $DBObject->AddLogbookItem($lb);
                }
                if($errorBarName === false && $errorBarTime === false){
                    echo '<div class="l-box-top pure-u-1"><div class="success-bar">'.$lang['GROUPDATE_CHANGED'].'</div></div>';
                }
                //header('Location: ' . $settings['site_url'] . 'group.php?gid=' . $group->id);
                $showform = true;

            }
            $group = $group->GetGroupById($_GET['gid']);
            $varGr= $group->GetDinnerClosingTime();
            if ($varGr != null)
              $dctsTime = $varGr->value;
            else
              $dctsTime = "";

        } else {
            header('Location: ' . $settings['site_url']);
        }
    } else {
        header('Location: ' . $settings['site_url']);
    }
    if ($showform) {
echo '
	<div class="pure-u-3-4">
        <div class="l-box-top pure-u-1">
            <h1>'.$lang['SETTINGS_TITLE'].'</h1>
            <p>&nbsp;</p>
        </div>';
        $user->ShowSettingsLeftSideBar($group->id);
        echo '
        <div class="l-box middle-box pure-u-17-24">';
        if ($errorBarTime) {
            echo '<div class="error-bar">'.$lang['CLOSING_TIME_NOT_VALID'].'</div>';
        }
        if ($errorBarName) {
            echo '<div class="error-bar">'.$lang['GROUP_NAME_NOT_VALID'].'</div>';
        }
        echo '
        <h2>'.$lang['GROUP'].' ' . $group->name . '</h2>
		<a href="'.$settings['site_url'].'group/'.$group->id.'/">&larr; '.$lang['BACK_TO_GROUPPAGE'].'</a><br>
	    <p>'.$lang['CHANGE_GROUP_INFO'].'</p>
	    <form method="post" action="">
            <span class="clear">
                <label>'.$lang['NAME'].'</label>
                <input type="text" name="name" value="' . $group->name . '" tabindex="1">
            </span>
            <span class="clear">
                <label>'.$lang['LOCATION'].'</label>
                <input type="text" name="location"  value="' . $group->location . '" tabindex="2" />
            </span>
            <span class="clear">
                <label>'.$lang['VALUTA'].'</label>
                ' . currencyArray('currency', $group->getCurrencyDropDown()) . '
            </span>
            <span class="clear">
                <label>'.$lang['MENU_COSTMANAGEMENT'].'</label>
                <div class="onoffswitch">
                <input type="checkbox" name="cost" id="cost" class="onoffswitch-checkbox" ' . ($group->CheckCost() ? "checked" : '') . ' tabindex="4">
                <label class="onoffswitch-label" for="cost">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
                </label>
                </div>
            </span>
            <span>
                <label>'.$lang['MENU_DINNER_LIST'].'</label>
                <div class="onoffswitch">
                <input type="checkbox" name="dinner" class="onoffswitch-checkbox"  onclick="toggleDCTS(this)" id="dinner" ' . ($group->CheckDinner() ? "checked" : '') . ' tabindex="5">
                <label class="onoffswitch-label" for="dinner">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
                </label>
                </div>
            </span>
            <span>
                <label>'.$lang['MENU_TASK_LIST'].'</label>
                <div class="onoffswitch">
                <input type="checkbox" name="task" class="onoffswitch-checkbox" id="task" ' . ($group->CheckTask() ? "checked" : '') . ' tabindex="7">
                <label class="onoffswitch-label" for="task">
                <div class="onoffswitch-inner"></div>
                <div class="onoffswitch-switch"></div>
                </label>
                </div>
            </span>
            <span class="clear">
                <label name="lbClosingTime" class="csDCTS"  style="' . ($group->CheckDinner() ? "" : "display:none") . '">'.$lang['CLOSING_TIME'].'</label>
                <input name="tbClosingTime" class="csDCTS" style="' . ($group->CheckDinner() ? "" : "display:none") . '" type="text" value="' . $dctsTime . '" tabindex="6">
            </span>

            <span class="clear">
                <input type="submit" class="alt_btn" value="'.$lang['SAVE'].'" tabindex="9">
                <input type="button" class ="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'\'" tabindex="10"/>
            </span>

            <span class="clear">
                <a href="'.$settings['site_url'].'delete-group/'.$group->id.'/">'.$lang['DELETE_GROUP'].'</a> - <a href="'.$settings['site_url'].'delete-user/'.$group->id.'/">'.$lang['DELETE_USER'].'</a>
            </span>
        </form>
        </div>
        </div>
        <script>
            function toggleDCTS(obj) {
                var $input = $(obj);
                 if ($input.prop(\'checked\'))
                      $(\'.cDTCS\').show();
                 else $(\'.csDTCS\').hide();
                }
        </script>';
        echo '
        <aside class="l-box pure-u-1-4">';
        $group->ShowSideBarStickyNotes();
        $group->ShowSideBarSettings();
        echo '		</aside>';

        ///DATUMPRIKKER:
        /*
        <label>'.$lang['MENU_DATEPICKER'].'</label>
		<div class="onoffswitch">
		<input type="checkbox" name="datepicker" class="onoffswitch-checkbox" id="datepicker" ' . ($group->CheckDatePicker() ? "checked" : '') . ' tabindex="8">
		<label class="onoffswitch-label" for="datepicker">
        <div class="onoffswitch-inner"></div>
        <div class="onoffswitch-switch"></div>
        </label>
        </div>
         */
    }
			echo '	</div>';
		echo '</div>';

}
include_once("inc/footer.inc.php");
?>