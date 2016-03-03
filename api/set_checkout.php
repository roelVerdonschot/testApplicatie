<?php
require_once("classes/config.mobile.inc.php");


$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $instant = new cost();
    $user = $result[0];
    $group = new Group();
    if($group->AuthenticationGroup($result[0], $result[1])){
        $group = $DBObject->GetGroupById($result[1]);
        $ToPay = $instant->PayOff($group->id);
        $userIdAmount = $instant->CalculateGroupSaldo($group->id);
        if($instant->PayOffDB($group->id)){
            // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
            $lb = new Logbook(null, $group->id, $user->id, null, null, null, null, 'PO', null);
            $DBObject->AddLogbookItem($lb);
            foreach($userIdAmount as $u){
                $pay = '';
                $get = '';
                foreach($ToPay as $p){
                    if($u->id == $p->id){
                        $pay = $pay . $lang['TO_PAY_AMOUNT'].$user->GetUserNameById($p->idUser).': '.$group->currency.' '.$p->numberOfUsers.'<br>';
                    }
                    if($u->id == $p->idUser){
                        $get = $get . $lang['TO_RECIEVE_AMOUNT'].$user->GetUserNameById($p->id).': '.$group->currency.' '.$p->numberOfUsers.'<br>';
                    }
                }
                Email_Handler::mailPayOff($pay,$get,$group->name,$u->id);
            }
            echo json_encode(true);
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

