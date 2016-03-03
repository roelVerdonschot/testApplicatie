<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == Cost object | [2] == iduser | aantal mee betalers (String)
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group;
    if($group->AuthenticationGroup($result[0], $result[1]["idGroup"])){
        $bool = false;
        foreach($result[2] as $r){
            $u = explode('|',$r);
            if($u[1] >= 1){
                $bool = true;
            }
        }
        if($bool){
            $amount = str_replace(',', '.', $result[1]["amount"]);
            $idCost = $DBObject->AddCost($amount,0,$result[1]["description"],$result[1]["idUser"],$result[1]["idGroup"],$result[1]["isDinner"],$result[1]["date"]);
            foreach($result[2] as $r){
                $u = explode('|',$r);
                $DBObject->AddUserCost($u[0],$idCost,$u[1]);
            }

            if($result[1]["isDinner"] == 1)
            {
                $DBObject->AddDinnerCost($result[1]["idGroup"],$result[1]["date"],$idCost);
            }
            $group = $group->GetGroupById($result[1]["idGroup"]);
            $DBObject->UpdateGroupUserAvgCooked($group);
            $DBObject->UpdateGroupUserSaldo($group->id);

            $lb = new Logbook(null, $result[1]["idGroup"], $result[0]->id, null, $idCost, null, null, 'CA', null);
            $DBObject->AddLogbookItem($lb);

            //All Users
            $usersOutput = array();
            $users = $group->getUsers();
            //var_dump($users);
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