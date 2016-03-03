<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == custom object (array)
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    //vraag groups op
    $group = new Group();
    $myGroups = $group->GetMyGroup($result[0]->id);

    $output = array();
    if(isset($myGroups)){
        foreach($myGroups as $group)
        {
			//Current user
            $currentUser = new UserData($result[0]->id,$result[0]->firstName,$DBObject->GetUserSaldo($group->id,$result[0]->id),$result[0]->preferredLanguage);

            //All Users
            $usersOutput = array();
            $users = $group->getUsers();
            if(isset($users))
            {
                foreach( $users as $u ) {
                    $usersOutput[] = new UserData($u->id, $u->firstName, $DBObject->GetUserSaldo($group->id,$u->id), $u->preferredLanguage);
                }
            }
			
			$currGroup = new GroupData($group->id,$group->name,$usersOutput,$group->currency,$currentUser,$group->modules);
			
			// Haal costen history op
			$costhis = $DBObject->GetLastGroupCost($group->id);
			$cost = array();
			if($costhis != null){
				foreach($costhis as $o){
					//var_dump($o);
					$cost[] = new CostData($o->id,$o->amount,$o->isPaid,$o->description,$o->idUser,$o->nameUser,$o->idGroup,$o->isDinner,$o->date,$o->users,COUNT($o->users),$o->deleted);
					//break;
				}
			}
			$currGroup->Costs = $cost;
		
			
			// haalt dinners van komende 7 dagen op.
			$AllDinners = array();
			for($i = 0; $i <7 ; $i++){
				$dinners = $DBObject->GetDinnerByDate($group->id, date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' day')));
				if($dinners != null){
					foreach($users as $u){
						if(isset($dinners[$u->id])){
							$dinnerData = new DinnerData($dinners[$u->id]->idGroup,$dinners[$u->id]->idUsers,$dinners[$u->id]->idRole,$dinners[$u->id]->date,$dinners[$u->id]->description,$dinners[$u->id]->NumberOfPersons,$u->firstName);
						}
						else{
							$dinnerData = new DinnerData($group->id,$u->id,0,date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' day')),"",0,$u->firstName);
						}
						$AllDinners[] = $dinnerData;
					}
				}
				else{
					foreach($users as $u){
						$dinnerData = new DinnerData($group->id,$u->id,0,date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' day')),"",0,$u->firstName);
						$AllDinners[] = $dinnerData;
					}

				}
			}
			$currGroup->Dinners = $AllDinners;
			
			// haal dinnerstats op
			$dinnerStats = $DBObject->GetDinnerStatistics($group->id);
			$dinnerStats2 = $DBObject->GetGroupUserAvgCookingCost($group->id);
			$users2 = $DBObject->GetUsersIdsFromGroup($group->id);
			$diStats = array();
			foreach($users2 as $u){

				$name = $DBObject->GetUserNameById($u->id);
				$cooked = (isset($dinnerStats[$u->id][2]) ? $dinnerStats[$u->id][2]->getCount() : 0); //cooked
				$dinnersJoined = (isset($dinnerStats[$u->id][1]) ? $dinnerStats[$u->id][1]->getCount() : 0); //dinners joined
				$ratio = (isset($dinnerStats[$u->id][1]) && isset($dinnerStats[$u->id][2]) ? number_format((float)round(($dinnerStats[$u->id][2]->getCount() / $dinnerStats[$u->id][1]->getCount()),2), 2, '.', '') : 0);
				$avg = (isset($dinnerStats2[$u->id][1]) ? $dinnerStats2[$u->id][1] : 0); // avg dinner cost pp
				$points = (isset($dinnerStats2[$u->id]) ? $dinnerStats2[$u->id][2] : 0); // cooking points
				$AVGEaters = $DBObject->GetDinnerrStaticsAvgMeeEters($group->id,$u->id);

				$diStats[] = new DinnerStats($name,$cooked,$dinnersJoined,$ratio,$avg,$points,$AVGEaters);
			}			
			$currGroup->DinnerStatistics = $diStats;
			
			// haal niet betaalde dinners op
			$unpayedDinners = array();
			$userDate = $DBObject->CheckCostDinner($currentUser->Id,$group->id);
			if($userDate != null){
				foreach($userDate as $ud){
					$idusers = $DBObject->GetAllGuestsFromDinner($group->id,$ud[0]);
					$userName = $DBObject->GetUserNameById($currentUser->Id);
					$cooked = $DBObject->GetDinner($ud[0], $group->id);
					$desc = "";
					if(isset($cooked)){
						if($cooked[0] == $currentUser->Id){
							$desc = $cooked[1];
						}
					}
					$unpayedDinners[] = new CostData(0,0,0,$desc,$currentUser->Id,$userName,$group->id,1,$ud[0],$idusers,COUNT($idusers),0);

				}
			}
			
			$currGroup->UnpayedDinners = $unpayedDinners;
			
			//TAKEN
			$tasksl = array();
			$tasks = $group->getAllTasks();
            $currGroup->Tasks = $tasks;
			
			$verschil = (count($users) - count($tasks));
			$tasksForOverview = $tasks;
			if($verschil > 0)
			{
				$tasksForOverview= array_merge($tasks,array_fill(0,$verschil,null));
			}
			for ($w = 0; $w < 5; $w++) {
				$weeklist = new WeekListData();
				$usertasks = null;
				$weeknumber = date('W', strtotime('last Monday + ' . ($w-1) . ' week'));
				$mondaydate = date('Y-m-d', strtotime('last Monday + ' . ($w-1) . ' week'));

				if ($w == 0) {
					$weeklist->setNameOfWeek($lang['LAST_WEEK']);
					$weeknumber = date('W', strtotime('last Monday - 1 week'));
					$mondaydate = date('Y-m-d', strtotime('last Monday -1 week'));
					$usertasks = $group->getUserTasksByWeek($mondaydate);
				}
				if ($w == 1) {
					$weeklist->setNameOfWeek($lang['THIS_WEEK']);
					$usertasks = $group->getUserTasksByWeek($mondaydate);
				}
				if ($w >= 2) {
					$weeklist->setNameOfWeek($lang['WEEK'] . date('W, d-m', strtotime('last Monday + ' . ($w-1) . ' week')));
				}

				if ($w == 2)
				{
					$usertasks = $group->getUserTasksByWeek($mondaydate);
				}
				$weeklist->setDateOfWeek($mondaydate);
				$i = 0;
				$tasklist = array();
				foreach($users as $u){

					$task = new TaskData();
					$taskNumber = ($weeknumber+$i) % count($tasksForOverview); // THIS IS NOT THE TASKID!

					$task->setUserId($u->id);
					$task->setUserName($u->firstName);
					if(isset($usertasks[$u->id]))
					{
						//task uit database en done

						$task->setTaskId($usertasks[$u->id]->id);
						$task->setTaskName($usertasks[$u->id]->name);
						$task->setIsComplete(true);
					}
					else
					{
						if( isset($tasksForOverview[$taskNumber]) && $tasksForOverview[$taskNumber] != null) // controleer of er een taak is voor de persoon / week
						{
							//$tasksForOverview[$taskNumber]->id
							$task->setTaskId($tasksForOverview[$taskNumber]->id);//$usertasks[$u->id]->getId);
							$task->setTaskName($tasksForOverview[$taskNumber]->name); //$usertasks[$u->id]->name);
							$task->setIsComplete(false);
						}
						else
						{
							// geen taak voor de gebruiker deze week
							$task->setTaskId(null);
							$task->setTaskName(null);
						}
					}
					$i++;
					$tasklist[] = $task;
				}
				$weeklist->setTaskList($tasklist);
				$tasksl[] = $weeklist;
			}
				//END TAKEN
				$currGroup->UserTasks = $tasksl;

            //DINNER CLOSING TIME
            $var = $group->GetDinnerClosingTime();
            $currGroup->DinnerClosingTime = (isset($var)?$var->value:'');


            //$currGroup->StickyNotes = $group->getAllStickyNotes()
		
            $output[] = $currGroup;
        }
    }
    //error_log("outut:".$output);
    //error_log("outut Json:".json_encode($output));
    echo json_encode($output);
    $DBObject->MobileLoginToday($result[0]->id);
}
?>