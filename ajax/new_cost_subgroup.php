<?php
require_once('../inc/config.inc.php');
if(Authentication_Controller::IsAuthenticated())
{
	$user = Authentication_Controller::GetAuthenticatedUser();
	$group = new Group;
	if(isset($_POST['data'])){
		$data = json_decode($_POST['data']);
		$gid = $data[0];
		$groupName = $data[1];
		$userIds = $data[2];
		if(isset($gid) && isset($groupName) && isset($userIds)){
			$bool = false;
			if($group->AuthenticationGroup($user, $gid)){
				$bool = true;
			}

			if($bool){
				$DBObject->InsertCostSubGroup($userIds,$groupName,$gid);
				echo 'true';
			}
			else{
				echo 'false';
			}
		}
		else{
			echo 'false';
		}
	}
	elseif(isset($_POST['update'])){
		$data = json_decode($_POST['update'], true);
		$subgroup = $data[0];
		$groupName = $data[1];
		$userIds = $data[2];
		//echo $subgroup;
		if($group->AuthenticationGroup($user, $subgroup['idgroup'])){
			$bool = true;
		}
		
		if($bool){
			$DBObject->UpdateCostSubGroup($subgroup['id'], $subgroup['idgroup'], $userIds, $groupName);
			echo 'true';
		}
		else{
			echo 'false bool';
		}
	}
	elseif(isset($_POST['deleted'])){
		$subgroup = json_decode($_POST['deleted'], true);
		
		if($group->AuthenticationGroup($user, $subgroup['idgroup'])){
			$bool = true;
		}
		
		if($bool){
			$DBObject->DeleteCostSubGroup($subgroup['id']);
			echo 'true';
		}
		else{
			echo 'false';
		}
	}
}
else{
	echo 'false login';
}