<?php
class Group
{
    function __construct()
    {
    }

    // property declaration
    public $id;
    public $name;
    public $location;
    public $picture;
    public $users;
    public $datecreated;
    public $currency;
    public $modules;

    public static function withGroup($id, $name, $location, $picture, $datecreated, $currency, $users, $modules)
    {
        $instance = new Group();
        if ($currency == 'EU') {
            $currency = '€';
        }
        $instance->loadGroup($id, $name, $location, $picture, $datecreated, $currency, $users, $modules);
        return $instance;
    }

    public static function withGroupNoUsers($id, $name, $location, $picture, $datecreated, $currency, $modules)
    {
        $instance = new Group();
        if ($currency == 'EU') {
            $currency = '€';
        }
        $instance->loadGroupNoUsers($id, $name, $location, $picture, $datecreated, $currency, $modules);
        return $instance;
    }

    public static function withNameIdMod($id, $name, $currency, $modules)
    {
        $instance = new Group();
        if ($currency == 'EU') {
            $currency = '€';
        }
        $instance->loadNameIdMod($id, $name, $currency, $modules);
        return $instance;
    }

    public static function withNameId($id, $name, $currency)
    {
        $instance = new Group();
        $instance->loadNameId($id, $name, $currency);
        return $instance;
    }

    protected function loadGroup($id, $name, $location, $picture, $datecreated, $currency, $users, $modules)
    {
        $this->id = $id;
        $this->name = $name;
        $this->location = $location;
        $this->picture = $picture;
        $this->datecreated = $datecreated;
        $this->currency = $currency;
        $this->users = $users;
        $this->modules = $modules;
    }

    protected function loadGroupNoUsers($id, $name, $location, $picture, $datecreated, $currency, $modules)
    {
        $this->id = $id;
        $this->name = $name;
        $this->location = $location;
        $this->picture = $picture;
        $this->datecreated = $datecreated;
        $this->currency = $currency;
        $this->modules = $modules;
    }

    protected function loadNameId($id, $name, $currency)
    {
        $this->id = $id;
        $this->name = $name;
        $this->currency = $currency;
    }

    protected function loadNameIdMod($id, $name, $currency, $modules)
    {
        $this->id = $id;
        $this->name = $name;
        $this->modules = $modules;
        $this->currency = $currency;
    }
	
	public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
	
	public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
	
	public function setLocation($location)
    {
        $this->location = $location;
    }

    public function getLocation()
    {
        return $this->location;
    }
	
	public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    public function getPicture()
    {
        return $this->picture;
    }
	
	public function setUsers($users)
    {
        $this->users = $users;
    }

    public function getUsers()
    {
        global $DBObject;
        if (count($this->users) == 0) {
            $this->users = $DBObject->GetUsersFromGroup($this->id);
        }
        return $this->users;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setModules($modules)
    {
        $this->modules = $modules;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function getAllTasks()
    {
        global $DBObject;
        return $DBObject->GetTasksByGroupId($this->id);
    }

    public function checkUser($userid)
    {
        foreach ($this->getUsers() as $user) {
            if ($userid == $user->id) {
                return true;
            }
        }
        return false;
    }

    public function getUserTasksByWeek($date)
    {
        global $DBObject;
        return $DBObject->GetUserTasks($date, $this->id);
    }

    public function getStickyNotesForSidebar()
    {
        global $DBObject;
        return $DBObject->GetSidebarStickyNotesByGroup($this->id);
    }
    public function getAllStickyNotes()
    {
        global $DBObject;
        return $DBObject->GetStickyNotesByGroup($this->id);
    }

    public function AuthenticationGroup($user, $gid)
    {
        global $DBObject;
        if ($DBObject->AuthenticationGroup($user->id, $gid)) {
            return true;
        } else {
            return false;
        }
    }

    public function AuthenticationGroupId($uid, $gid)
    {
        global $DBObject;
        if ($DBObject->AuthenticationGroup($uid, $gid)) {
            return true;
        } else {
            return false;
        }
    }

    public function GetGroupById($gid)
    {
        global $DBObject;
        return $DBObject->GetGroupById($gid);
    }

    public function ShowSideBarStickyNotes()
    {
        global $settings, $DBObject, $lang;
        echo '<h2>'.$lang['PRICKBORD'].' <a id="toggle-stickynote" href="#">+</a> <span><a href="' . $settings['site_url'] . 'sticky-notes/' . $this->id . '/">'.$lang['MORE'].
            '</a></span></h2>
		<div class="box stickynote" id="add-stickynote">
		    <form method="post" action="' . $settings['site_url'] . 'add-sticky-note/' . $this->id . '/">
                <span>
                    <input type="text" name="stickynote-title" placeholder="'.$lang['TITLE_OPTIONAL'].'" tabindex="10" value="" />
                </span>
                <span class="clear">
                    <textarea name="stickynote-message" placeholder="'.$lang['MESSAGE'].'" tabindex="11" rows="4"></textarea>
                </span>
                <span class="clear">
                    <input type="hidden" name="current-page" value="' . implode("/",array_slice(explode("/", $_SERVER['REQUEST_URI']), 1)) . '/" />
                    <input type="submit" class="btn" id="send-stickynote" tabindex="12" value="'.$lang['SEND'].'">
                </span>
                <div class="clear"></div>
            </form>
        </div>';
        if (count($this->getStickyNotesForSidebar()) > 0) {
            foreach ($this->getStickyNotesForSidebar() as $stickyNote) {
                $string = strip_tags($stickyNote->message);
                if (strlen($string) > 300) {
                    $stringCut = substr($string, 0, 300);
                    $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . '... <a href="' . $settings['site_url'] . 'sticky-notes/' . $this->id . '#' . $stickyNote->id . '">'
                        .$lang['READ_MORE'].'</a>';
                }
                //$user = ;
                echo '
            <div class="box stickynote">
                ' . ($stickyNote->title ? '<h3>' . $stickyNote->title . '</h3>' : '') . '
                <p>' . $string . '</p>
                <span class="info">' . $stickyNote->getDate() . ' - ' . $DBObject->GetUserNameById($stickyNote->idUser) . '</span>
            </div>';
            }
        } else {
            echo '
            <div class="box stickynote">
               <p>'.$lang['ERROR_NO_STICKNOTES'].'</p>
            </div>';
        }
    }

    public function ShowSideBarDemoStickyNotes()
    {
        global $settings, $DBObject, $lang;
		$pageArray = explode("/", $_SERVER['REQUEST_URI']); // util functie voor get current page
        echo '<h2>'.$lang['PRICKBORD'].' <a id="toggle-stickynote" href="#">+</a></h2>
		<div class="box stickynote" id="add-stickynote">
		    <form method="post" >
                <span>
                    <input type="text" name="stickynote-title" placeholder="'.$lang['TITLE_OPTIONAL'].'" tabindex="10" value="" />
                </span>
                <span class="clear">
                    <textarea name="stickynote-message" placeholder="'.$lang['MESSAGE'].'" tabindex="11" rows="4"></textarea>
                </span>
                <span class="clear">
                    <input type="hidden" name="current-page" value="' . end($pageArray) . '" />
                    <input type="submit" class="submit_btn" id="send-stickynote" tabindex="12" value="'.$lang['SEND'].'">
                </span>
                <div class="clear"></div>
            </form>
        </div>';
        if (count($this->getStickyNotesForSidebar()) > 0) {
            foreach ($this->getStickyNotesForSidebar() as $stickyNote) {
                $string = strip_tags($stickyNote->message);
                if (strlen($string) > 300) {
                    $stringCut = substr($string, 0, 300);
                    $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . '... <a href="' . $settings['site_url'] . 'sticky-notes/' . $this->id . '#' . $stickyNote->id . '">'.$lang['READ_MORE'].'</a>';
                }
                //$user = ;
                echo '
            <div class="box stickynote">
                ' . ($stickyNote->title ? '<h3>' . $stickyNote->title . '</h3>' : '') . '
                <p>' . $string . '</p>
                <span class="info">' . $stickyNote->getDate() . ' - ' . $DBObject->GetUserNameById($stickyNote->idUser) . '</span>
            </div>';
            }
        } else {
            echo '
            <div class="box stickynote">
               <p>'.$lang['ERROR_NO_STICKNOTES'].'</p>
            </div>';
        }
    }

    public function GetTaskByUserWeek($userId, $weekNumber = null)
    {
        $users = $this->getUsers();
        $tasks = $this->getAllTasks();
        if (count($tasks) == 0) {
            return "-";
        }

        $verschil = (count($users) - count($tasks));
        $tasksForOverview = $tasks;
        if ($verschil > 0) {
            $tasksForOverview = array_merge($tasks, array_fill(0, $verschil, null));
        }
        if ($weekNumber == null) {
            $weeknumber = date('W', strtotime('last Monday'));
        }
        $i = 0;
        foreach ($users as $u) {
            if ($u->id == $userId) {
                $taskNumber = ($weeknumber + $i) % count($tasksForOverview); // THIS IS NOT THE TASKID!
                if(isset($tasksForOverview[$taskNumber]))
                {
                    return $tasksForOverview[$taskNumber]->name;
                }
                return '-';
            }
            $i++;
        }
    }

    public function GetMyGroup($uid)
    {
        global $DBObject;
        return $DBObject->GetMyGroups($uid);
    }

    public function UpdateSetting($setting)
    {
        global $DBObject;
        $DBObject->UpdateSetting($setting);
    }

    public function UpdateUserSetting($setting)
    {
        global $DBObject;
        $DBObject->UpdateUserSetting($setting);
    }

    public function GetDinnerClosingTime()
    {
        global $DBObject;
        $dinnerClosingTimeSetting = $DBObject->GetDinnerClosingTimeSetting($this->id, "dcts");

        if ($dinnerClosingTimeSetting != null)
        {
            return $dinnerClosingTimeSetting;
        }
        return null;
    }

    public function GetSetting($key,$uid){
        global $DBObject;
        $Setting = $DBObject->GetUserSetting($this->id, $key,$uid);
        return $Setting;
    }

    public function GetBalanceByUser($user)
    {
        $instant = new cost();
        $userIdAmount = $instant->CalculateUserSaldo($this->id, $user->id);
        if (isset($userIdAmount)) {
            return $userIdAmount;
        }
        return '0,-';

    }

    public function AddGroup($name, $user, $email)
    {
        global $DBObject;
        $DBObject->AddGroup($name, $user->firstName, $user->id, $email);
    }

    public function EditGroupData($group)
    {
        global $DBObject;
        $DBObject->EditGroupData($group);
    }

    public function DeleteUserFromGroup($uid, $group)
    {
        global $DBObject;
        return $DBObject->DeleteUserFromGroup($uid, $group->id);
    }

    public function GetNumberOfUsersInGroup($gid)
    {
        global $DBObject;
        return $DBObject->GetNumberOfUsersInGroup($gid);
    }

    public function GetNumberOfUsersInvited($gid)
    {
        global $DBObject;
        return $DBObject->GetNumberOfUsersInvited($gid);
    }

    public function InviteUserToGroup($email, $gid, $uid)
    {
        global $DBObject;
        $DBObject->InviteUserToGroup($email, $gid, $uid);
    }

    public function CheckCost()
    {
        global $settings;
        if ($this->modules & $settings['bit_group_modules']['cost']) {
            return true;
        } else {
            return false;
        }
    }

    public function CheckDinner()
    {
        global $settings;
        if ($this->modules & $settings['bit_group_modules']['dinner']) {
            return true;
        } else {
            return false;
        }
    }

    public function CheckTask()
    {
        global $settings;
        if ($this->modules & $settings['bit_group_modules']['task']) {
            return true;
        } else {
            return false;
        }
    }

    public function CheckDatePicker()
    {
        global $settings;
        if ($this->modules & $settings['bit_group_modules']['datepicker']) {
            return true;
        } else {
            return false;
        }
    }

    public function ShowDinnerByUserDate($group,$user){
        global $DBObject, $settings;
        $dinner =  $DBObject->GetDinnerByUserDate(Date('y-m-d'), $this->id, $user->id);
        if(isset($dinner)){
            //echo $dinner->idRole.' '.$dinner->NumberOfPersons;
            $dinnerRole = $dinner->idRole;
            $dinnerGuests = $dinner->NumberOfPersons;
            if(isset($dinnerRole)){
                if($dinnerRole == DINNER_JOIN_DINNER){
                    echo '<a class="eatimg" href="'.$settings['site_url'].'dinner/'.$group->id.'/">'.($dinnerGuests >= 2 ? ' x'.$dinnerGuests : '&nbsp;').'</a>';
                }
                elseif($dinnerRole == DINNER_IS_COOK){
                    echo '<a class="chefimg" href="'.$settings['site_url'].'dinner/'.$group->id.'/">'.($dinnerGuests >= 2 ? ' x'.$dinnerGuests : '&nbsp;').'</a>';
                }
                elseif($dinnerRole == DINNER_NOT_EATING){
                    echo '<a class="noeatimg" href="'.$settings['site_url'].'dinner/'.$group->id.'/">&nbsp;</a>';
                }
                elseif($dinnerRole == DINNER_NOTHING_SET){
                    echo '<a class="nothingimg" href="'.$settings['site_url'].'dinner/'.$group->id.'/">&nbsp;</a>';
                }
            }
        }
        else{
            echo '<a class="nothingimg" href="'.$settings['site_url'].'dinner/'.$group->id.'">&nbsp;</a>';
        }
    }

    public function ShowSideBarSettings()
    {
        global $lang,$settings;
        echo '<h2>'.$lang['SETTINGS_SIDEBAR'].'</h2>
		<div class="box">
			<ul class="small">
				<li><a href="'.$settings['site_url'].'add-user/'.$this->id.'/">'.$lang['ADD_USERS_BTN2'].'</a></li>
				<li><a href="'.$settings['site_url'].'settings-group/'.$this->id.'/">'.$lang['CHANGE_GROUP'].'</a></li>
				<li><a href="'.$settings['site_url'].'logbook/'.$this->id.'/all/1">'.$lang['GO_TO_LOGBOOK'].'</a></li>';
				$user = Authentication_Controller::GetAuthenticatedUser();
                if(isset($user)){
                    $myGroups = $this->GetMyGroup($user->id);
                    if(count($myGroups) == 1)
                    {
                        echo '<li><a href="'.$settings['site_url'].'new-group/">'.$lang['NEW_GROUP'].'</a></li>';
                    }
                }
				echo '
			</ul>
		</div>';
    }

    public function ClosingTimeExceeded($date)
    {
        if(date('Y-m-d') != date("Y-m-d", strtotime($date)))
        {
            return false;
        }
        $varGr = $this->GetDinnerClosingTime();
        if ($varGr != null)
        {
            $dctsTime = $varGr->value;
            if(date('H:i') >= date('H:i', strtotime($dctsTime)))
            {
                return true;
            }
        }
        return false;

    }

    public function ShowSideBarLegend()
    {
        global $lang;
        echo '<h2>'.$lang['LEGEND'].'</h2>
        <table class="noborder w100p">
        <tr><td class="eatimg">&nbsp;</td><td>'.$lang['JOINING_FOR_DINNER'].'</td></tr>
        <tr><td class="chefimg">&nbsp;</td><td>'.$lang['THE_CHEF'].'</td></tr>
         <tr><td class="noeatimg">&nbsp;</td><td>'.$lang['NOT_JOING_FOR_DINNER'].'</td></tr>
         </table>';
    }

    public function getCurrencyDropDown(){
        $currency = '';
        if($this->currency == '€'){
            $currency = '€ euro';
        }
        elseif($this->currency == '$'){
            $currency = '$ dollar';
        }
        else{
            $currency = '£ pound';
        }
        return $currency;
    }

    public function showWhoCooksByDate($date)
    {
        global $lang,$DBObject;
        $cooked = $DBObject->GetDinner($date, $this->id);
        $count = 0;
        $userString = '';
        $cookingString = '';
        if(isset($cooked[0])){
            foreach($cooked[0] as $u){
                $userString = $userString . (COUNT($cooked[0]) == 1 ? '' : ($count > 0 && $count < COUNT($cooked[0]) -1 ? ', ' : '').($count == COUNT($cooked[0]) -1 ? ' '.$lang['AND'].' '
                            : '')).$DBObject->GetUserNameById($u);
                ++$count;
            }
        }
        $count = 0;
        $arrayCount = 0;
        if(isset($cooked[1])){
            foreach($cooked[1] as $u){
                if($u != null){
                    $arrayCount++;
                }
            }
            foreach($cooked[1] as $u){
                if($u != null){
                    $cookingString = $cookingString . ($arrayCount == 1 ? '' : ($count > 0 && $count < $arrayCount -1 ? ', ' : '').($count == $arrayCount -1 ? ' '.$lang['AND'].' ' : '')).$u;
                }
                ++$count;
            }
        }
        if($cooked[0] != null && $cooked[1] != null){
            return $userString . (COUNT($cooked[0]) > 1 ? ' koken ' : ' kookt ').' deze vanavond '.$cookingString;
        }
        return "";
    }

    public function getTaskIcal($uid)
    {
        global $DBObject;
        return $DBObject->getTaskIcal($this->id,$uid);
    }

    public function setTaskIcal($uid,$dayOfWeek,$uniqKey)
    {
        global $DBObject;
        return $DBObject->setTaskIcal($this->id,$uid,$dayOfWeek,$uniqKey);
    }
}

?>