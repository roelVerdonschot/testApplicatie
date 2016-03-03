<?php
require_once("classes/config.mobile.inc.php");


$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group();
    $output = array();
    if($group->AuthenticationGroup($result[0], $result[1])){
        $dinnerStats = $DBObject->GetDinnerStatistics($result[1]);
        $dinnerStats2 = $DBObject->GetGroupUserAvgCookingCost($result[1]);
        $users = $DBObject->GetUsersIdsFromGroup($result[1]);
        foreach($users as $u){

            $name = $DBObject->GetUserNameById($u->id);
            $cooked = (isset($dinnerStats[$u->id][2]) ? $dinnerStats[$u->id][2]->getCount() : 0); //cooked
            $dinnersJoined = (isset($dinnerStats[$u->id][1]) ? $dinnerStats[$u->id][1]->getCount() : 0); //dinners joined
            $ratio = (isset($dinnerStats[$u->id][1]) && isset($dinnerStats[$u->id][2]) ? number_format((float)round(($dinnerStats[$u->id][2]->getCount() / $dinnerStats[$u->id][1]->getCount()),2), 2, '.', '') : 0);
            $avg = (isset($dinnerStats2[$u->id][1]) ? $dinnerStats2[$u->id][1] : 0); // avg dinner cost pp
            $points = (isset($dinnerStats2[$u->id]) ? $dinnerStats2[$u->id][2] : 0); // cooking points
            $AVGEaters = $DBObject->GetDinnerrStaticsAvgMeeEters($result[1],$u->id);

            $output[] = new DinnerStats($name,$cooked,$dinnersJoined,$ratio,$avg,$points,$AVGEaters);
        }
        echo json_encode($output);
    }
    else{
        echo json_encode(false);
    }
}
?>
