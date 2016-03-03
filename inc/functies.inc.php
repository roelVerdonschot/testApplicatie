<?php
/**
 * @author Arwin van der Velden
 * @copyright 2009
 * @name functies.inc.php
 * @version 1.0.1
 */

function getSetting($name){
	global $settings, $DBObject;
	// Kijken of er cookies zijn gezet, en controleren of ze valid zijn
	 $query = "	SELECT 
					value 
				FROM 
					".$settings['db_general_settings']." 
				WHERE 
					name = '".$DBObject->CheckDBValue($name)."'";

	if ($DBObject->query($query)) 
	{		
		if ($DBObject->getNumRows() > 0) 
		{
			$row = $DBObject->getResult();
			return $row['value'];
		} 
		else 
		{
			return false;   
		}
	} 
	else 
	{
		return false;   
	}          
} 
?>