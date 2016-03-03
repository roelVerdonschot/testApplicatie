<?php
ob_start();
require_once("inc/config.inc.php");
$DBObject = DBHandler::GetInstance(null);
$_page_title = $lang['PAGENAME_NEWGROUP'];
require_once("inc/header.inc.php");
?>
<script>
$(document).ready(function(){
    $('#errorBar').hide();
	document.getElementById("cost_group_delete").style.visibility="hidden";
    //document.getElementById("errorBar").style.visibility="hidden";
	var person = [];
	var groupObject;
	var group;
    var selectedButton;

	$('.userSelect').click(function(){ 
		var button = $(this);
		button.blur();
		var buttonId = parseInt(button.attr("name"));
		if( $.inArray(buttonId, person) === -1 ) {			
			button.css("border", "3px solid #777");
			person.push(buttonId);
		}
		else{
			button.css("border", "3px solid #ef8a00");
			var index = person.indexOf(buttonId);
			person.splice(index, 1); 
		}
	});
	
	$('.groupSelect').click(function(){ 
		var button = $(this);
		button.blur();
		var buttonId = button.attr("name");
		if(group != buttonId) {
			// clear old value's
			var users = person
				   .filter(function (el) {
							return el.name !== 0;
						   });
			users.forEach(function(entry) {
				document.getElementById(entry).click();
			});
            if(selectedButton !== undefined){
                selectedButton.css("border", "3px solid #ef8a00");
            }

			// update new value's
			document.getElementById("cost_group_delete").style.visibility="visible";
			button.css("border", "3px solid #777");
			group = buttonId;
            selectedButton = button;
			document.getElementById("cost_group_title").innerHTML = "Groep wijzigen";
			document.getElementById("submitbtn").value = "Groep wijzigen";
			document.getElementById('Group_name').innerHTML = button.attr("value");
			document.getElementById('Group_name').value = button.attr("value");
			groupObject = JSON.parse(decodeURIComponent(buttonId));
			for (var i = 0; i < groupObject.userIds.length; i++) {
				document.getElementById(groupObject.userIds[i]).click();
			}
		}
		else{
			document.getElementById("cost_group_title").innerHTML = "Groep aanmaken";
			document.getElementById("submitbtn").value = "Groep aanmaken";
			button.css("border", "3px solid #ef8a00");
			// clear old value's
			document.getElementById("cost_group_delete").style.visibility="hidden";
			group = "";
			document.getElementById('Group_name').value = "";
			var users = person
				   .filter(function (el) {
							return el.name !== 0;
						   });
			users.forEach(function(entry) {
				document.getElementById(entry).click();
			});
		}
	});
	
	// to delete subgroup
	$('.deleteClick').click(function(){ 
		if (confirm("Weet je zeker dat je deze group wilt verwijderen?"))
		{
			$.post( "http://www.monipal.com/ajax/new_cost_subgroup.php", { deleted: JSON.stringify(groupObject) })
				.done(function( data ) {
                    if(data == "true")
                    {
                        location.href="http://www.monipal.com/cost/"+$('#gid').val();
                    }
                    else
                    {
                        $('#errorBar').show();
                    }
			});
		}	
	});

	// to add or update subgroup
	$('#addCostGroupForm').submit(function(e){
		e.preventDefault();
		
		var groupName = $('#Group_name').val();
		if (groupName === undefined || groupName === null || groupName == "") {
			document.getElementById('Group_name').style.border = "2px solid #f00";
		}else{
		
			// nieuwe group
			if(group == "" || group === undefined || group == null)
			{
				document.getElementById('Group_name').style.border = "2px solid #777777";
				var users = person
					   .filter(function (el) {
								return el.name !== 0;
							   });
				if(users.length > 0){
					var data = [];
					data[0] = $('#gid').val();
					data[1] = groupName;
					data[2] = users;
					$.post( "http://www.monipal.com/ajax/new_cost_subgroup.php", { data: JSON.stringify(data) })
						.done(function( data ) {
						//alert( "Data Loaded: " + data );
                            if(data == "true")
                            {
                                location.href="http://www.monipal.com/cost/"+$('#gid').val();
                            }
                            else
                            {
                                $('#errorBar').show();
                            }
					});
				}
				else{
					alert("Selecteer groepsleden");
				}
			}
			else // update group
			{
				var users = person
					   .filter(function (el) {
								return el.name !== 0;
							   });
				if(users.length > 0){
					var data = [];
					data[0] = groupObject;
					data[1] = groupName;
					data[2] = users;
					$.post( "http://www.monipal.com/ajax/new_cost_subgroup.php", { update: JSON.stringify(data) })
						.done(function( data ) {
                            if(data == "true")
                            {
                                location.href="http://www.monipal.com/cost/"+$('#gid').val();
                            }
                            else
                            {
                                $('#errorBar').show();
                            }
					});
				}
			}
		}
	});
});
</script>
<div class="normal-content">
	<div class="pure-g">
<?php
$showform = true;
if(!Authentication_Controller::IsAuthenticated()) {
    header ('Location: '.$settings['site_url'].'login/');
    $showform = false;
}
if(isset($_GET['gid'])){	
    $user = Authentication_Controller::GetAuthenticatedUser();
    $group = new Group;
    $errorData = array();
    if($group->AuthenticationGroup($user, $_GET['gid'])){
        $group = $group->GetGroupById($_GET['gid']);
	}
}
if ($showform) {
    echo '<div class="l-box pure-u-3-4">
	<div class="l-box pure-u-1">';
	echo '<div class="error-bar" name="errorBar" id="errorBar">Kosten subgroup aanmaken is niet gelukt.</div>';
    echo '<form id="addCostGroupForm" method="post" action="">';
	$costSubGroups = $DBObject->GetCostSubGroups($group->id);
	if(isset($costSubGroups) && COUNT($costSubGroups) > 0){
		echo '<h1>Groepen</h1>';
		$first  = true;
		foreach($costSubGroups as $csg){
			echo '<span class="'.($first ? 'clear' : '').'"><input type="button" class="groupSelect" style="background-color: #ef8a00 !important; color: #fff !important; min-width: 100px !important; height: 35px !important; border:3px solid #ef8a00;padding:7px 20px;" id="'.rawurlencode(json_encode($csg)).'" name="'.rawurlencode(json_encode($csg)).'" value="'.$csg->name.'" onclick="" /></span>';
			$first = false;
		}	
	}
	echo '<span class="clear"><h1 name="cost_group_title" id="cost_group_title">'.$lang['MAKEGROUP'].'</h1></span>
	<input type="hidden" name="gid" id="gid" value="'.$group->id.'">
	<span class="clear">
		<label>'.$lang['GROUPOFNAME'].'</label> 
		<input type="text" id="Group_name" name="Group_name" tabindex="1">
	</span>
	<span class="clear">
		<label>Selecteer de personen die je wilt toevoegen aan de groep:</label>
	</span>';
		$count = 0;
		foreach($group->getUsers() as $user){ // onclick="window.location.href=\''.$settings['site_url'].'accept-invite.php?inv='.$i[0].'&gid='.$i[1].'\'"
			echo '<span class="'.($count == 0 ? 'clear' : '').'"><input type="button" style="background-color: #ef8a00 !important; color: #fff !important; min-width: 100px !important; height: 35px !important; border:3px solid #ef8a00;padding:7px 20px;" class="userSelect" id="'.$user->id.'" name="'.$user->id.'" value="'.$user->getFullName().'" /></span>';
			$count++;
		}

	echo '
		<span class="clear">
			<input type="submit" id="submitbtn" name="submitbtn" class="alt_btn" value="'.$lang['MAKEGROUP'].'" >
			<input type="button" class="alt_btn" value="'.$lang['CANCEL'].'" onclick="window.location.href=\''.$settings['site_url'].'cost/'.$group->id.'/\'" tabindex="10"/>
			<input type="button" class="alt_btn deleteClick" name="cost_group_delete" id="cost_group_delete" value="Verwijder groep" onclick="" tabindex="5"/>
		</span>
	</form>
	</div>
	</div>
    <aside class="l-box pure-u-1-4">';
        $group->ShowSideBarStickyNotes();
		$group->ShowSideBarSettings();
    echo '</aside>';
}
echo '	</div>';
echo '</div>';
include_once("inc/footer.inc.php");
?>