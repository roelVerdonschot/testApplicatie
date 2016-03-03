<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_NEWGROUP'];
require_once("inc/header.inc.php");
?>
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
                <label for="Field2"><?php echo $lang['EMAILADRES']; ?></label>\
                <input id="dEmail'+(count)+'" name="email[]" type="text" class="field text ln" value="" size="24" tabindex="'+(count)+1+'"/>\
            </span>\
            ';
            }
        }
    </script>
<div class="normal-content">
	<div class="pure-g">
<?php
$showform = false;
if(Authentication_Controller::IsAuthenticated()) {

    $error = null;
    if (mb_strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
        $showform = true;

    } else {

        $showform = false;
        $user = Authentication_Controller::GetAuthenticatedUser();
        $groupname = trim($_POST['Group_name']);
            if (!empty($groupname)) {
                if(strlen(trim($_POST['Group_name'])) <= 2) {
                    $showform = true;
                    $error = $lang['GROUP_NAME_NOT_VALID'];
                }
                else
                {
                    $group = new Group();
                    $group->AddGroup($_POST['Group_name'], $user,$_POST['email']);
                    echo '<div class="pure-u-1 success-bar">'.$lang['GROUPCREATED'].' <a href="'.$settings['site_url'].'">'.$lang['CLICKHERE'].'</a></div> ';
                }
            }
            else{
                $showform = true;
                $error = $lang['FILLINGROUPNAME'];
            }
    }
    if ($showform) {
        echo '
		<div class="l-box pure-u-3-4">
			<div class="l-box pure-u-1-2">';
				if($error != null)
				{
					echo '<div class="error-bar">'.$error.'</div>';
				}
				echo '<h1>'.$lang['MAKEGROUP'].'</h1>
				<form method="post" action="">
				<span>
					<label>'.$lang['GROUPOFNAME'].'</label>
					<input type="text" name="Group_name" tabindex="1">
				</span>
				<div id="persons_area">
				<span class="clear">
					<label for="Field2">'.$lang['EMAILADRES'].'</label>
					<input id="dEmail_1" name="email[]" type="text" class="field text ln" value="" size="24" tabindex="2" />
				</span>
				</div>

				<span>
					<label>&nbsp;</label>
					<input type="button" value="'.$lang['ADDUSERTO'].'" onclick="addField(\'persons_area\',\'email[]\',25);" tabindex="3" />
				</span>

				<span class="clear">
					<input type="submit" class="alt_btn" value="'.$lang['MAKEGROUP'].'" tabindex="28">
				</span>
				</form>
			</div>
			<div class="l-box pure-u-1-2">
				<h2>Importeer</h2>
				<p>Via onderstaande buttons kan je een groep aanmaken op basis van WieBetaaltWat.nl of Eetlijst.nl. Heb je een kostenlijst ergens anders? Na het aanmaken van een groep kan je ook handmatig het huidige saldo invoeren.</p>
				<p>Importeer vanuit:</p>
            <input type="button" class="alt_btn btn-red" value="Wiebetaaltwat.nl" onclick="window.location.href=\''.$settings['site_url'].'import-wiebetaaltwat/\'" tabindex="4"/> 
            <input type="button" class="alt_btn btn-red" value="Eetlijst.nl" onclick="window.location.href=\''.$settings['site_url'].'import-eetlijst/\'" tabindex="4"/>
				';
				$import = new Import();
				if(!$import->testEetlijst())
				{
					echo '<div class="notification-bar">Het is mogelijk dat de importeer functie voor Eetlijst.nl momenteel niet goed functioneerd, we proberen z.s.m. dit te verhelpen.</div>';
				}
				if(!$import->testWieBetaaltWat())
				{
					echo '<div class="notification-bar">Het is mogelijk dat de importeer functie voor Wiebetaaltwat.nl momenteel niet goed functioneerd, we proberen z.s.m. dit te verhelpen.</div>';
				}
				echo '
			</div>
		</div>
        <aside class="l-box pure-u-1-4">
            <div class="box">
                <p>'.$lang['EDIT_ACCOUNT_NO_GROUPS'].'</p>
                <a href="'.$settings['site_url'].'edit-account" class="btn btn-white">'.$lang['UPDATE_ACCOUNT'].'</a>
            </div>
        </aside>';
    }

}
echo '	</div>';
echo '</div>';
include_once("inc/footer.inc.php");
?>