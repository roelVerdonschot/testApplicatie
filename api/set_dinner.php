<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == group ID | [2] == Dinner object
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group;
    if($group->AuthenticationGroup($result[0], $result[1])){
        //var_dump($result[2]['_NumberOfPersons']);
        if(isset($result[2]['Description'])){
            $desc = $result[2]['Description'];
        }
        else{
            $desc = null;
        }
        $DBObject->UpdateAdvancedDinner($result[2]['IdGroup'], $result[2]['IdUsers'], $result[2]['Date'], $result[2]['IdRole'], ($result[2]['NumberOfPersons'] == 0 ? 1 : $result[2]['NumberOfPersons']),$desc);
		
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

        if($result[2]['IdRole'] == 0){
            $lbKey= 'ENW';
            $lbValue = $result[2]['UserName'].'|'.$result[2]['Date'];
        }
        elseif($result[2]['IdRole'] == 1){
            $lbKey= 'EW';
            $lbValue = $result[2]['UserName'].'|'.($result[2]['NumberOfPersons'] == 0 ? 1 : $result[2]['NumberOfPersons']).'|'.$result[2]['Date'];
        }
        elseif($result[2]['IdRole'] == 2){
            $lbKey= 'EC';
            $lbValue = $result[2]['UserName'].'|'.($result[2]['NumberOfPersons'] == 0 ? 1 : $result[2]['NumberOfPersons']).'|'.$result[2]['Date'];
        }
        elseif($result[2]['IdRole'] == -1){
            $lbKey= 'ENS';
            $lbValue = $result[2]['UserName'].'|'.$result[2]['Date'];
        }
        // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
        $lb = new Logbook(null, $result[2]['IdGroup'], $result[0]->id, null,$result[2]['IdUsers'], $result[2]['Date'], null,$lbKey , $lbValue);
        $DBObject->AddDinnerLogbookItem($lb);
    }
    else{
        echo json_encode(false);
    }
}
?>