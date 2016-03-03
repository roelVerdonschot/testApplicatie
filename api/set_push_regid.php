<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == group ID | [2] == regID | [3] set on/off (0/1) | [4] == platform (wp,android,iphone) | [5] == device number
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $justAdded = false;
    $group = new Group;
    $pushDevice = $DBObject->GetDeviceIdByNumber($result[5]);
    //error_log($pushDevice->idUser." ".$pushDevice->is_enabled);
   // error_log("USerId: ".$result[0]->id." RegId: ".$result[2]." Platform: ".$result[4]." Device Nr: ".$result[5]." is enabled: ".$result[3]);
    if($pushDevice == null && $result[2] != "null") // device is al aangemaakt
    {
        $pushDevice = $DBObject->AddPushDevice($result[0]->id,$result[2],$result[4],$result[5],$result[3]); // ($userId, $regId, $platform, $devicenumber) {
        $justAdded = true;
    }
    else
    {
        if($pushDevice->push_reg != $result[2] || $pushDevice->idUser != $result[0]->id || $pushDevice->is_enabled != $result[3]){
            $pushDevice->changePushReg($result[2],$result[0]->id,$result[3]);
        }
    }

    /*if($group->AuthenticationGroup($result[0], $result[1])){
        //var_dump($pushDevice->idDevice,$result[1],$result[0]->id,$result[3]);
        if($result[3] == 0){
            if($justAdded == false){
                $DBObject->SetPushDeviceStatus($result[1],$result[0]->id,$pushDevice->idDevice,$result[3]);
            }
            else{
                $DBObject->SetupPushDeviceForGroup($pushDevice->idDevice,$group->id,$result[0]->id,$result[3]);
            }
        }
        else{
            if($justAdded == false){
                $DBObject->DeletePushDeviceStatus($group->id,$result[0]->id,$pushDevice->idDevice);
            }
        }
    }*/

    /*if($pushDevice != null)
    {
        if($result[1] == "ALL")
        {
            $myGroups = $group->GetMyGroup($result[0]->id);
            if(isset($myGroups)){
                foreach($myGroups as $group)
                {
                    if($group->AuthenticationGroup($result[0], $group->id)) {
                        $DBObject->SetupPushDeviceForGroup($pushDevice->idDevice,$group->id,$result[0]->id,$result[3]);
                    }
                }
            }
        }
        elseif($justAdded == false)
        {
            if($group->AuthenticationGroup($result[0], $result[1])){
                //var_dump($pushDevice->idDevice,$result[1],$result[0]->id,$result[3]);
                $DBObject->SetPushDeviceStatus($result[1],$result[0]->id,$pushDevice->idDevice,$result[3]);
            }
        }
    }*/
	echo json_encode(true);
}
?>