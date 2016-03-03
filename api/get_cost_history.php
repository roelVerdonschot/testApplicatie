<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group();
    $cost = array();
    if($group->AuthenticationGroup($result[0], $result[1])){
        $output = $DBObject->GetLastGroupCost($result[1]);
        foreach($output as $o){
            //var_dump($o);
            $cost[] = new CostData($o->id,$o->amount,$o->isPaid,$o->description,$o->idUser,$o->nameUser,$o->idGroup,$o->isDinner,$o->date,$o->users,COUNT($o->users),$o->deleted);
            //break;
        }
        echo json_encode($cost);
    }
    else{
        echo json_encode(false);
    }
}
?>