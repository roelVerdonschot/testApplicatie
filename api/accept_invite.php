<?php
require_once("classes/config.mobile.inc.php");


$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $user = new User();
    if($user->DeleteUserFromInvite($result[0]->email,$result[1],$result[0]->id)){
        // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
        $lb = new Logbook(null, $result[1], $result[0]->id, null, null, null, null, 'UA', $result[0]->email);
        $DBObject->AddLogbookItem($lb);
        echo json_encode($result[1]);
    }
    else{
        echo json_encode(-1);
    }
}
?>
