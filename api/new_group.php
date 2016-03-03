<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == group name | [2] == user emails (array)
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $email[] = array();
    foreach($result[2] as $e){
        if(!empty($e)){
            $email[] = $e;
        }
    }
    $group = new Group();
    $group->AddGroup($result[1], $result[0],$email);
    echo json_encode(true);
}
?>