<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractNonLoginString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  name | [1] == email | [2] == password
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    $user = new User();
    if(!$user->CheckEmail(strtolower($result[1]))){
        if(filter_var(strtolower($result[1]), FILTER_VALIDATE_EMAIL)){
            // sha1 email=activationcode
            $user->AddUser($result[0], strtolower($result[1]), Bcrypt::hash($result[2]), sha1($result[1]), 'NL', true);
            // sends the activation email
            Email_Handler::mailActivationRequest($result[1],$lang['SUBJECT_REG'],$result[0],sha1($result[1]));

            $idgroups = $DBObject->CheckInvitedUser(strtolower($result[1]));

            if($idgroups != null){
                $userId = $user->GetUserIdByEmail(strtolower($result[1]));
                foreach($idgroups as $i){
                    $user->DeleteUserFromInvite(strtolower($result[1]),$i, $userId);
                }
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




