<?php
require_once("classes/config.mobile.inc.php");

$result = API_controller::extractString(isset($_POST['q']) ? $_POST['q'] : $_GET['q']); // [0] ==  USER object of ERROR | [1] == custom object (array)
if ($result[0] instanceof ErrorData) {
    echo json_encode($result[0]);
}
else
{
    //vraag groups op
    $group = new Group();
    $myGroups = $group->GetMyGroup($result[0]->id);

    $output = array();
    if(isset($myGroups)){
        foreach($myGroups as $group)
        {
            //Current user
            $currentUser = new UserData($result[0]->id,$result[0]->firstName,$DBObject->GetUserSaldo($group->id,$result[0]->id),$result[0]->preferredLanguage);

            //All Users
            $usersOutput = array();
            $users = $group->getUsers();
            if(isset($users))
            {
                foreach( $users as $u ) {
                    $usersOutput[] = new UserData($u->id, $u->firstName, $DBObject->GetUserSaldo($group->id,$u->id), $u->preferredLanguage);
                }
            }
            $output[] = new GroupData($group->id,$group->name,$usersOutput,$group->currency,$currentUser,$group->modules);
        }
    }
    //error_log("outut:".$output);
    //error_log("outut Json:".json_encode($output));
    echo json_encode($output);
}
?>