<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId | [2] == userId for unpayed dinners
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group();
    if($group->AuthenticationGroup($result[0], $result[1])){
        $unpayedDinners = array();
        $userDate = $DBObject->CheckCostDinner($result[2],$result[1]);
        $userName = $DBObject->GetUserNameById($result[2]);
        if(isset($userDate)){
            foreach($userDate as $ud){
                $idusers = $DBObject->GetAllGuestsFromDinner($result[1],$ud[0]);
                $cooked = $DBObject->GetDinner($ud[0], $result[1]);
                $desc = "";
                if(isset($cooked)){
                    if($cooked[0] == $result[2]){
                        $desc = $cooked[1];
                    }
                }
                $unpayedDinners[] = new CostData(0,0,0,$desc,$result[2],$userName,$result[1],1,$ud[0],$idusers,COUNT($idusers),0);
            }
        }
        else{
            echo json_encode(false);
        }

        echo json_encode($unpayedDinners);
    }
    else{
        echo json_encode(false);
    }
}
?>
