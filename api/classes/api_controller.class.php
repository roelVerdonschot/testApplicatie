<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Arwin
 * Date: 3-10-13
 * Time: 18:59
 * To change this template use File | Settings | File Templates.
 */

class API_controller {

    public static function extractString($inputString)
    {
        $inputString = mb_convert_encoding($inputString, "UTF-8", "UTF-8");
        $mcrypt = new MCrypt();
        $decriptedString = $mcrypt->decrypt($inputString);
        $object = json_decode(str_replace("\\","", $decriptedString), true); // [0] = always login, [1] could be the orther object

        if ($object === null && json_last_error() !== JSON_ERROR_NONE) {
            $decriptedString = $mcrypt->decryptWP($inputString);
            $object = json_decode(preg_replace('/[\x00-\x1F\x7F]/', '', $decriptedString), true); // [0] = always login, [1] could be the orther object
        }

        if ($object === null && json_last_error() !== JSON_ERROR_NONE) {
            $decriptedString = $mcrypt->decryptIOS($inputString);
            $object = json_decode(preg_replace('/[\x00-\x1F\x7F]/', '', $decriptedString), true); // [0] = always login, [1] could be the orther object
        }
		
		//error_log("decriptedString".$decriptedString); // todo, deze eruit ivm password

        if(!isset($object[0]["Username"]) || !isset($object[0]["Password"]) || !isset($object[0]["TimeStamp"])){
            error_log("Hey, er probeert iemand ons te hacken met dit ip addres: ".$_SERVER['REMOTE_ADDR']." url: ".$_SERVER['REQUEST_URI']." user agent: ".$_SERVER['HTTP_USER_AGENT']." decrypted q=".$decriptedString." q=".$inputString);
        }

        $loginObject = new Login($object[0]["Username"],$object[0]["Password"],$object[0]["TimeStamp"]);
        $object[0] = $loginObject->authenticate();
        return $object;

    }

    public static function extractNonLoginString($inputString){
        $inputString = mb_convert_encoding($inputString, "UTF-8", "UTF-8");
        $mcrypt = new MCrypt();
        $decriptedString = $mcrypt->decrypt($inputString);

        $object = json_decode(str_replace("\\","", $decriptedString), true);
		
		if ($object === null && json_last_error() !== JSON_ERROR_NONE) {
            $decriptedString = $mcrypt->decryptWP($inputString);
            $object = json_decode(preg_replace('/[\x00-\x1F\x7F]/', '', $decriptedString), true); // [0] = always login, [1] could be the orther object
        }

        if ($object === null && json_last_error() !== JSON_ERROR_NONE) {
            $decriptedString = $mcrypt->decryptIOS($inputString);
            $object = json_decode(preg_replace('/[\x00-\x1F\x7F]/', '', $decriptedString), true); // [0] = always login, [1] could be the orther object
        }
        return $object;
    }
}