<?php
ob_start();
require_once("../inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
require_once("classes/config.mobile.inc.php");


$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId | [2] == costId
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group();
    if($group->AuthenticationGroup($result[0], $result[1])){
        $cost = new Cost();
        $cost = $cost->GetCostById($result[2]);
        $output = new CostData($cost->id,$cost->amount,$cost->isPaid,$cost->description,$cost->idUser,$cost->nameUser,$cost->idGroup,$cost->isDinner,$cost->date,$cost->users,$cost->numberOfUsers,$cost->deleted);
        echo json_encode($output);
    }
    else{
        echo json_encode(false);
    }
}
?>