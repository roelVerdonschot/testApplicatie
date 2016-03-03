<?php
/**
 * User: Roel Verdonschot
 * Date: 7-8-13
 * Time: 1:37
 */
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_ADDUSER'];
require_once("inc/header.inc.php");
require_once('inc/recaptchalib.php');
?>
<script type="text/javascript">
    var RecaptchaOptions = {
        theme : 'clean',
        tabindex : 12
    };
</script>
    <script>
        // Add more fields dynamically.
        function addField(area,field,limit) {
            if(!document.getElementById) return; //Prevent older browsers from getting any further.
            var field_area = document.getElementById(area);
            var all_inputs = field_area.getElementsByTagName("input"); //Get all the input fields in the given area.
            // Find the count of the last element of the list. It will be in the format '<field><number>'. If the
            //		field given in the argument is 'friend_' the last id will be 'friend_4'.
            var last_item = all_inputs.length - 1;
            var last = all_inputs[last_item].id;
            var count = Number(last.split("_")[1]) + 1;

            //If the maximum number of elements have been reached, exit the function.
            //		If the given limit is lower than 0, infinite number of fields can be created.
            if(count > limit && limit > 0) return;

            if(document.createElement) { //W3C Dom method.
                var li = document.createElement("li");

                var span2 = document.createElement("span");
                var label2 = document.createElement("label");
                label2.innerHTML = "Emailadres";
                var input2 = document.createElement("input");
                input2.id = 'dEmail_'+count;
                input2.name = 'email[]';
                input2.type = "text"; //Type of field - can be any valid input type like text,file,checkbox etc.
                input2.size = "24";
                input2.className = "field text fn";

                span2.appendChild(label2);
                span2.appendChild(input2);
                li.appendChild(span2);
                field_area.appendChild(li);


            } else { //Older Method
                field_area.innerHTML +=  '\
            <span class="clear">\
                <label for="Field2"><?php echo $lang['EMAIL']; ?></label>\
                <input id="dEmail'+(count)+'" name="email[]" type="text" class="field text ln" value="" size="24" tabindex="'+(count)+1+'"/>\
            </span>\
            ';
            }
        }
    </script>
<?php
$showform = false;
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login');
    $showform = false;
}

if(isset($_GET['gid'])){
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group;
    if($group->AuthenticationGroup($user, $_GET['gid'])){
        $group = $group->GetGroupById($_GET['gid']);
        $publickey = "6Lf_3ekSAAAAAMp1DPqvgzQWU9c3fmjGiRq-5B6G"; // you got this from the signup page
        $showform = true;

        if (mb_strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
            $showform = false;

            $privatekey = "6Lf_3ekSAAAAAIhi-mgRWSEYpPNjMidUdS-WxHws";
            $resp = recaptcha_check_answer ($privatekey,
                $_SERVER["REMOTE_ADDR"],
                $_POST["recaptcha_challenge_field"],
                $_POST["recaptcha_response_field"]);

            if (!$resp->is_valid) {
                // What happens when the CAPTCHA was entered incorrectly
                $showform = true;
                $error[] = $lang['INCORRECT_CAPTCHA'];
            }

            if (!empty($_POST['email'])) {
                $emails = $_POST['email'];
                $usersInGroup = $group->GetNumberOfUsersInGroup($group->id);
                $usersInvited = $group->GetNumberOfUsersInvited($group->id);
                $totaleUsers = $usersInGroup + $usersInvited;

                if($totaleUsers >= 30){
                    $showform = true;
                    $error[] = $lang['GROUP_HAS_MAX_USERS'];
                }

                if(($totaleUsers + COUNT($emails)) >= 30){
                    $showform = true;
                    $error[] = sprintf($lang['GROUP_ALREADY_HAS'], $totaleUsers,(30 - $totaleUsers));
                }
                if($showform === false){
                    foreach($emails as $email){
                        if(!empty($email)){
                            if($DBObject->CheckInvitedUserGroup($email, $group->id)){
                                Email_Handler::mailBodyInviteToGroup($email,$lang['GROUP_INVITE'],$user->firstName,$group->name,$group->id);
								$showform = true;
                            }
                            else{
                                $group->InviteUserToGroup($email,$group->id,$user->id);
                                Email_Handler::mailBodyInviteToGroup($email,$lang['GROUP_INVITE'],$user->firstName,$group->name,$group->id);
                                $lb = new Logbook(null, $group->id, $user->id, null, null, null, null, 'GUA', $email);
                                $DBObject->AddLogbookItem($lb);
                            }
                        }
                    }
				}
				if($showform === false)
				{
                    $notification = '<div class="l-box pure-u-1 notification-bar">'.$lang['USER_ARE_INVITES'].'</div>';
                    header("refresh:2; url=".$settings['site_url'].'group/'.$group->id, true);
                }
				else
				{
					$error[] = $lang['ERROR_FIELD'];
				}
            }
        }
    }
    else{
        header ('Location: '.$settings['site_url']);
    }
}

if($showform){
	echo '<div class="normal-content">
	<div class="pure-g">';
    if(isset($notification)) {
        echo $notification;
    }
    $invites = $DBObject->GetInvitedUsers($group->id);
    if(isset($error)){
        echo '<div class="l-box pure-u-1 error-bar">';
        foreach ($error as $r){
            echo '- '.$r.'<br />';
        }
        echo '</div>';
    }
    echo '
		<div class="l-box pure-u-3-4">
    <h1>'.$lang['ADD_USERS_BTN2'].'</h1>
        <form method="post" action="">
        <div id="persons_area">
        <span class="clear">
                <label for="Field2">'.$lang['EMAIL'].'</label>
                <input id="dEmail_1" name="email[]" type="text" class="field text ln" value="" size="24" tabindex="1" />
        </span>
        </div>
        <span>
            <label>&nbsp;</label>
            <input type="button" class="secundaire-btn" value="'.$lang['ADD_USER_BTN'].'" onclick="addField(\'persons_area\',\'email[]\',10);" tabindex="2"/>
        </span>
        <span class="clear">
        <label>'.$lang['LABEL_CAPTCHA'].'</label>'.
        recaptcha_get_html($publickey)
        .'</span>
        <span class="clear">
            <input type="submit" class="alt_btn" value="'.$lang['ADD_USERS_BTN2'].'" tabindex="13">
                </span>
        </form>';
    if($invites != null){
        echo '<table class="index-table">
        <tr><th>'.$lang['INVITED_USERS'].'</th><th></th></tr>';
        foreach($invites as $i){
            echo '<tr>
            <td>'.$i.'</td>
            <td class="onepx"><input type="button" class="alt_btn" value="'.$lang['DELETE_INVITE'].'" onclick="window.location.href=\''.$settings['site_url'].'accept-invite.php?dele='.$i.'&gid='.$group->id.'\'"/></td>
            </tr>
            ';
        }
        echo '</table>';
     }
       echo '</div>';
    echo '<aside class="l-box pure-u-1-4">';
    $group->ShowSideBarStickyNotes();
    $group->ShowSideBarSettings();
    echo '		</aside>';
	echo '	</div>';
	echo '</div>';
}
require_once("inc/footer.inc.php");
?>
