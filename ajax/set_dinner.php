<?php
require_once('../inc/config.inc.php');
$whoCooksHtml = null;
if(Authentication_Controller::IsAuthenticated())
{
    $user = Authentication_Controller::GetAuthenticatedUser();
    if ($DBObject->AuthenticationGroup($user->id, $_POST['gid'])) {
        $group = new Group();
        $group = $group->GetGroupById($_POST['gid']);
        $user = Authentication_Controller::GetAuthenticatedUser();
        // gets the users from the selected group
        $users = $group->getUsers();

        // if the user is not entering the data for hims/herself it'll get the user out of the url, else it'll be the current user
        if (isset($_POST['uid'])) {
            if($group->checkUser($_POST['uid'])){
                $userData = $_POST['uid'];
            }
            else{
                $userData = $user->id;
            }
        } else {
            $userData = $user->id;
        }

        // if there's a date in the url that'll be the date, otherwise it'll pick today's date
        $dateBool = true;
        if (isset($_POST['date'])) {
            list($yyyy,$mm,$dd) = explode('-',$_POST['date']);
            if (checkdate($mm,$dd,$yyyy)) {
                //if( strtotime($_POST['date']) >= strtotime(date('Y-m-d')) && strtotime($_POST['date']) < strtotime(date('Y-m-d') . ' + 7 day')){
                    $dateValue = $_POST['date'];
                //}
                //else{
                //    $dateBool = false;
                //}
            }
            else{
                $dateBool = false;
            }
        } else {
            $dateValue = date('Y-m-d');
        }

        if(isset($_POST['persons']) && is_numeric($_POST['persons']) && $_POST['persons'] > 0){
            $nrOfPersons = $_POST['persons'];
        }
        else{
            $nrOfPersons = 1;
        }

        if (isset($_POST['role'])) {
            $role = $_POST['role'];
            if(!$group->ClosingTimeExceeded($dateValue))
            {
                if($dateBool == true){
                    if($role == 1 || $role == 0 || $role == 2 || $role = -1){
                        //$DBObject->UpdateDinner($group->id, $userData, $dateValue, $role);
                        $DBObject->UpdateAdvancedDinner($group->id, $userData, $dateValue, $role, $nrOfPersons,"");
                        //persons
                        if($role == 0){
                            $lbKey= 'ENW';
                            $lbValue = $DBObject->GetUserNameById($userData).'|'.$dateValue;
                        }
                        elseif($role == 1){
                            $lbKey= 'EW';
                            $lbValue = $DBObject->GetUserNameById($userData).'|0|'.$dateValue;
                        }
                        elseif($role == 2){
                            $lbKey= 'EC';
                            $lbValue = $DBObject->GetUserNameById($userData).'|0|'.$dateValue;
                        }
                        elseif($role == -1){
                            $lbKey= 'ENS';
                            $lbValue = $DBObject->GetUserNameById($userData).'|'.$dateValue;
                        }

                        // checks if there's a chef for the current day in the group
                        $chef = $DBObject->CheckChef($dateValue, $group->id);

                        if(isset($chef) && $chef == 1){
                            $whoCooksHtml = $group->showWhoCooksByDate($dateValue);
                        }

                        $output = array('role' => $role, 'userId' => $userData, 'userName' => $DBObject->GetUserNameById($userData), 'dateDinner'=>$dateValue, 'persons' => $nrOfPersons,'whoCooksString' => $whoCooksHtml);
                        echo json_encode($output);

                        // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                        $lb = new Logbook(null, $group->id, $user->id, null, $userData, $dateValue, null,$lbKey , $lbValue);
                        $DBObject->AddDinnerLogbookItem($lb);

                    }
                }
            }
            else
            {
                // checks if there's a chef for the current day in the group
                $chef = $DBObject->CheckChef($dateValue, $group->id);

                if(isset($chef) && $chef == 1){
                    $whoCooksHtml = $group->showWhoCooksByDate($dateValue);
                }

                $output = array('role' => 'X', 'userId' => $userData, 'userName' => $DBObject->GetUserNameById($userData), 'dateDinner'=>$dateValue, 'persons' => $nrOfPersons,'whoCooksString' => $whoCooksHtml);
                echo json_encode($output);
            }
        }

    }
}
else // DEMO!
{
    $group = new Group();
    $group = $group->GetGroupById($settings['DEFAULT_DEMO_GROUP']);
    // gets the users from the selected group
    $users = $group->getUsers();

    // if the user is not entering the data for hims/herself it'll get the user out of the url, else it'll be the current user
    $uidBool = false;
    if(isset($_POST['uid'])){
        $uidBool = true;
        if(!$group->checkUser($_POST['uid'])){
            $uidBool = false;
        }
        if(!$group->AuthenticationGroupId($_POST['uid'], $settings['DEFAULT_DEMO_GROUP'])){
            $uidBool = false;
        }
        if($_POST['uid'] == $settings['DEFAULT_DEMO_USER']){
            $uidBool = false;
        }
    }
    if($uidBool == false){
        $userData = $settings['DEFAULT_DEMO_USER'];
    }
    else{
        $userData = $_POST['uid'];
    }

    if(isset($_POST['persons']) && is_numeric($_POST['persons']) && $_POST['persons'] > 0){
        $nrOfPersons = $_POST['persons'];
    }
    else{
        $nrOfPersons = 1;
    }

    // if there's a date in the url that'll be the date, otherwise it'll pick today's date
    $dateBool = true;
    if (isset($_POST['date'])) {
        list($yyyy,$mm,$dd) = explode('-',$_POST['date']);
        if (checkdate($mm,$dd,$yyyy)) {
            //if( strtotime($_POST['date']) >= strtotime(date('Y-m-d')) && strtotime($_POST['date']) < strtotime(date('Y-m-d') . ' + 7 day')){
                $dateValue = $_POST['date'];
            //}
            //else{
            //    $dateBool = false;
            //}
        }
        else{
            $dateBool = false;
        }
    } else {
        $dateValue = date('Y-m-d');
    }

    // checks if there's a chef for the current day in the group
    $chef = $DBObject->CheckChef($dateValue, $group->id);
	
    if(isset($chef) && $chef == 1){
        $whoCooksHtml = $group->showWhoCooksByDate($dateValue);
    }


    if (isset($_POST['role'])) {
        $role = $_POST['role'];
        if(!$group->ClosingTimeExceeded($dateValue))
        {
            if($dateBool == true){
                if($role == 1 || $role == 0 || $role == 2 || $role = -1){
                    $DBObject->UpdateAdvancedDinner($group->id, $userData, $dateValue, $role, $nrOfPersons,"");
                    //$DBObject->UpdateDinner($group->id, $userData, $dateValue, $role);
                    if($role == 0){
                        $lbKey= 'ENW';
                        $lbValue = $DBObject->GetUserNameById($userData).'|'.$dateValue;
                    }
                    elseif($role == 1){
                        $lbKey= 'EW';
                        $lbValue = $DBObject->GetUserNameById($userData).'|0|'.$dateValue;
                    }
                    elseif($role == 2){
                        $lbKey= 'EC';
                        $lbValue = $DBObject->GetUserNameById($userData).'|0|'.$dateValue;
                    }
                    elseif($role == -1){
                        $lbKey= 'ENS';
                        $lbValue = $DBObject->GetUserNameById($userData).'|'.$dateValue;
                    }

                    // checks if there's a chef for the current day in the group
                    $chef = $DBObject->CheckChef($dateValue, $group->id);

                    if(isset($chef) && $chef == 1){
                        $whoCooksHtml = $group->showWhoCooksByDate($dateValue);
                    }

                    $output = array('role' => $role, 'userId' => $userData, 'userName' => $DBObject->GetUserNameById($userData), 'dateDinner'=>$dateValue, 'persons' => $nrOfPersons,'whoCooksString' => $whoCooksHtml);

                    echo json_encode($output);

                    // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                    $lb = new Logbook(null, $group->id, $settings['DEFAULT_DEMO_USER'], null, $userData, $dateValue, null, $lbKey , $lbValue); // 10 is demo user
                    $DBObject->AddDinnerLogbookItem($lb);

                }
            }
        }
        else
        {

            // checks if there's a chef for the current day in the group
            $chef = $DBObject->CheckChef($dateValue, $group->id);

            if(isset($chef) && $chef == 1){
                $whoCooksHtml = $group->showWhoCooksByDate($dateValue);
            }

            $output = array('role' => 'X', 'userId' => $userData, 'userName' => $DBObject->GetUserNameById($userData), 'dateDinner'=>$dateValue, 'persons' => $nrOfPersons,'whoCooksString' => $whoCooksHtml);
            echo json_encode($output);
        }
    }
}
// Terug geven: nieuwe role, userid, groupid, xpersons, date
?>