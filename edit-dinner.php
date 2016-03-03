<?php
/**
 * User: roel
 * Date: 10-10-13
 * Time: 11:47
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_EDITCOST'];
require_once("inc/header.inc.php");
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
}
else{
    if(isset($_GET['gid'])){
        $user = Authentication_Controller::GetAuthenticatedUser();
        $group = new Group;
        if($group->AuthenticationGroup($user, $_GET['gid'])){
            $group = $group->GetGroupById($_GET['gid']);
            if(isset($_GET['uid'])){
                if($group->checkUser($_GET['uid'])){
                    $uid = $_GET['uid'];
                }
                else{
                    $uid = $user->id;
                }
            }
            else{
                $uid = $user->id;
            }
            if(isset($_GET['date'])){
                if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $_GET['date'])){
                    list($yyyy,$mm,$dd) = explode('-',$_GET['date']);
                    if (checkdate($mm,$dd,$yyyy)) {
                        $idcost = $DBObject->AddCost(0,0,'',$uid,$group->id,1,$_GET['date']);
                        $DBObject->AddDinnerCost($group->id,$_GET['date'],$idcost);
                        // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                        $lb = new Logbook(null, $group->id, $uid, null, $idcost, null, null, 'CA', null);
                        $DBObject->AddLogbookItem($lb);

                        header ('Location: '.$settings['site_url'].'edit-cost/'.$group->id.'/'.$idcost.'/');
                    }
                    else{
                        header ('Location: '.$settings['site_url'].'cost/'.$group->id.'/');
                    }
                }
                else{
                    header ('Location: '.$settings['site_url'].'cost/'.$group->id.'/');
                }
            }
            else{
                header ('Location: '.$settings['site_url'].'cost/'.$group->id.'/');
            }
        }
        else{
            header ('Location: '.$settings['site_url']);
        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}






