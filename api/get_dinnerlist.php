<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // // [0] ==  USER object of ERROR | [1] == group ID
if ($result[0] instanceof ErrorData) {
   echo json_encode($result[0]);
}
else
{
    $group = new Group;
    $AllDinners = array();

    // result[0] is hele user object result[1] is group id
    if($group->AuthenticationGroup($result[0], $result[1])){
        $group = $group->GetGroupById($result[1]);
        $users = $group->getUsers();
        // haalt dinners van komende 7 dagen op.
        for($i = 0; $i <7 ; $i++){
            $dinners = $DBObject->GetDinnerByDate($result[1], date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' day')));
            if($dinners != null){
                foreach($users as $u){
                    if(isset($dinners[$u->id])){
                        $dinnerData = new DinnerData($dinners[$u->id]->idGroup,$dinners[$u->id]->idUsers,$dinners[$u->id]->idRole,$dinners[$u->id]->date,$dinners[$u->id]->description,$dinners[$u->id]->NumberOfPersons,$u->firstName);
                    }
                    else{
                        $dinnerData = new DinnerData($result[1],$u->id,0,date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' day')),"",0,$u->firstName);
                    }
                    $AllDinners[] = $dinnerData;
                }
            }
            else{
                foreach($users as $u){
                    $dinnerData = new DinnerData($result[1],$u->id,0,date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $i . ' day')),"",0,$u->firstName);
                    $AllDinners[] = $dinnerData;
                }

            }
        }
        echo json_encode($AllDinners);
    }
    else{
        echo json_encode(false);
    }
}
?>