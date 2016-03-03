<?php
ob_start();
require_once("inc/config.inc.php");

$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_DELETETASK'];
require_once("inc/header.inc.php");

if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
} else {
    if(isset($_GET['tid'])){
        $user = Authentication_Controller::GetAuthenticatedUser();
        $task = $DBObject->GetTaskByID($_GET['tid']);
        if($task == null)
        {
            header ('Location: '.$settings['site_url']);
        }
        if($DBObject->AuthenticationGroup($user->id,$task->groupId)){
            $user = Authentication_Controller::GetAuthenticatedUser();
            $groupId = $task->groupId;
            echo '<div class="normal-content">';
            if($task->deleteTask())
            {
                // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                $lb = new Logbook(null, $groupId, $user->id, null, null, null, null, 'TD', $task->name);
                $DBObject->AddLogbookItem($lb);
                echo '<div class="success-bar">De taak is verwijderd.</div>';

            }
            else
            {
                echo '<div class="error-bar">Het verwijderen is mislukt, probeer het opnieuw.<br></div>';
            }
            echo '</div>';
            header('refresh:3; url='.$settings['site_url'].'tasks/'.$groupId, true);
        }
        else{
            header ('Location: '.$settings['site_url']);
        }
    }
}
include_once("inc/footer.inc.php");
?>