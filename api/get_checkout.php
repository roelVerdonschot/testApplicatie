<?php
require_once("classes/config.mobile.inc.php");


$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == groupId
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $group = new Group();
    $cost = new Cost();
    if($group->AuthenticationGroup($result[0], $result[1])){
        $group->GetGroupById($result[1]);
        if(COUNT($group->getUsers()) == 1){
        }
        else{
            $userIdAmount = $cost->CalculateGroupSaldo($result[1]);
            if(isset($userIdAmount)){
                $count = 0;
                foreach($userIdAmount as $us){
                    if($us->amount == 0){
                        $count++;
                    }
                }

                if(COUNT($userIdAmount) != $count){
                    $ToPay = $cost->PayOff($result[1]);
                    if($ToPay != null){
                        $output = array();
                        $userIdAmount = $cost->CalculateGroupSaldo($result[1]);
                        foreach($userIdAmount as $u){
                            $bank = $DBObject->GetBankAccountById($u->id);
                            $pay = null;
                            $pay = array();
                            foreach($ToPay as $p){
                                if($u->id == $p->id){
                                    $pay[] = new CheckOutDebt($DBObject->GetUserNameById($p->idUser),$p->numberOfUsers,true);
                                }
                                if($u->id == $p->idUser){
                                    $pay[] = new CheckOutDebt($DBObject->GetUserNameById($p->id),$p->numberOfUsers,false);
                                }
                            }
                            $user = new CheckoutData($DBObject->GetUserNameById($u->id),$u->amount,$pay,$bank);
                            $output[] = $user;
                        }
                        echo json_encode($output);
                    }
                    else{
                        echo json_encode("ErrorCode");
                    }
                }

            }
        }

    }
}
?>