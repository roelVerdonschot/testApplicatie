<?php
$user = Authentication_Controller::GetAuthenticatedUser();
$group = new Group;
if(isset($_POST['data'])){
    $data = explode('|',$_POST['data']);
    $gid = $data[0];
    $tid = $data[1];
    $date = $data[2];
    $uid = $data[3];
}
if(isset($gid) && isset($tid) && isset($date) && isset($uid)){
    $bool = false;
    if($group->AuthenticationGroup($user, $gid)){
        $bool = true;
        $group = $group->GetGroupById($gid);
        if(!$group->checkUser($uid)){
            $bool = false;
        }
    }

    if($date != date('Y-m-d', strtotime('last Monday -1 week')) || $date != date('Y-m-d', strtotime('last Monday + 0 week')) || $date != date('Y-m-d', strtotime('last Monday + 1 week')))
    {
        $bool = false;
    }

    if($bool){
        $userTask = new User_Task($uid,$tid,$date,1);
        $DBObject->SetUserTask($userTask,$group,$user->id);
        echo 'true';
    }
    else{
        echo 'false';
    }
}
else{
    echo 'false';
}
