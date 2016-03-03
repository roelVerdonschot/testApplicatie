<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == stickynote
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    // ($id,$title,$message,$date,$idUser,$idGroup)
    $sticknote = new Sticky_Note($result[1]["id"],$result[1]["title"],$result[1]["message"],$result[1]["date"],$result[1]["idUser"],$result[1]["idGroup"]);
    $ourput = $DBObject->AddStickyNote($sticknote);

    echo json_encode($ourput);
    if($ourput){
        $users = $DBObject->GetUsersForPushNewStickyNote($result[1]["idGroup"]);
        foreach($users as $user){
            if($user->id != $result[1]["idUser"]){
                $title = $result[1]["title"];
                $message = $result[1]["message"];
                $output = $user[0]->pushDevice->SendPush($result[1]["idGroup"],$title,$message);
            }
        }
    }
}
?>