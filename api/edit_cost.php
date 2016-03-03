<?php
require_once("classes/config.mobile.inc.php");


$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId | [2] == Cost Object | [3] == iduser | aantal mee betalers  (array)
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group();
    if($group->AuthenticationGroup($result[0], $result[1])){
        //var_dump("test");
        $bool = false;
        foreach($result[3] as $u){
            $u = explode('|',$u);
            if($u[1] >= 1){
                $bool = true;
            }
        }
        if($bool){
            //var_dump($result[2]);
            $cost = new Cost();
            $cost = $cost->GetCostById($result[2]['id']);
            $oldAmount = $cost->amount;
            if($result[2]['amount'] > 0){
                $cost->setAmount($result[2]['amount']);
            }
            if(!empty($result[2]['date'])){
                $cost->setDate($result[2]['date']);
            }
            if(!empty($result[2]['description'])){
                $cost->setDescription($result[2]['description']);
                //var_dump($cost->description);
            }
            if(!empty($result[2]['idUser'])){
                $cost->idUser = $result[2]['idUser'];
            }
            $cost->EditCostData($cost);

            foreach($result[3] as $u ){
                $u = explode('|',$u);
                $cost->UpdateCostGuests($u[0],$u[1],$cost->id);
            }

            $group = $group->GetGroupById($result[1]);
            $DBObject->UpdateGroupUserAvgCooked($group);
            $DBObject->UpdateGroupUserSaldo($group->id);

            // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
            $lb = new Logbook(null, $result[1], $result[0]->id, null, $cost->id, null, null, 'CE', $oldAmount);
            $DBObject->AddLogbookItem($lb);

            //All Users
            $usersOutput = array();
            $users = $group->getUsers();
            if(isset($users))
            {
                foreach( $users as $u ) {
                    $usersOutput[] = new UserData($u->id, $u->firstName, $DBObject->GetUserSaldo($group->id,$u->id), $u->preferredLanguage);
                }
            }

            echo json_encode($usersOutput);
        }
        else{
            echo json_encode(false);
        }
    }
    else{
        echo json_encode(false);
    }
}
?>