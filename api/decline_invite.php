<?php
require_once("classes/config.mobile.inc.php");


$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $DBObject->DeleteInviteByGid($result[1],$result[0]->email);
    echo json_encode($result[1]);
}
?>

