<?php
class Push_Device {
    function __construct($id,$userId,$push_reg,$platform,$devicenumber,$last_connected,$is_enabled)
    {
        $this->idDevice = $id;
        $this->idUser = $userId;
        $this->push_reg = $push_reg;
        $this->platform = $platform;
        $this->devicenumber = $devicenumber;
        $this->last_connected = $last_connected;
        $this->is_enabled = $is_enabled;
    }
    public $idDevice;
    public $idUser;
    public $push_reg;
    public $platform;
    public $devicenumber;
    public $last_connected;
    public $is_enabled;

    public function ChangePushReg($pushReg,$userId,$isEnabled)
    {
        global $DBObject;
        $DBObject->UpdateDevicePushReg($this->idDevice,$pushReg,$userId,$isEnabled);
        $this->push_reg = $pushReg;
        $this->idUser = $userId;
    }

    public function UpdateDeviceTime()
    {
        global $DBObject;
        $DBObject->UpdateDeviceTime($this->idDevice);
    }

    /**
     * @param $groupid
     * @param $title
     * @param $message
     * @return bool
     */
    public function SendPush($groupid,$title,$message)
    {
        $output = false;
        if($this->platform == 'android')
        {
            $output = $this->SendPushAndroid($title."||".$message);
        }
        else if($this->platform == 'wp')
        {
            $output = $this->SendPushWindowsPhone($groupid,$title,$message);
        }
		else if($this->platform == 'iphone')
        {
            $output = $this->SendPushiPhone($title.". ".$message,$groupid,0);
        }
		

        if($output == true)
        {

            //$this->UpdateDeviceTime();
        }
        else
        {
            var_dump("Fout gegaan met dit device:",$this,"Output: ".$output);
        }
        return $output;
    }

    private function SendPushAndroid($message)
    {

        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';
        $fields = array(
            'registration_ids'  => array($this->push_reg),
            'data'              => array( "message" => $message ),
        );

        $headers = array(
            'Authorization: key=AIzaSyCIpL2e98RCjrqryjO5Y-W9xHBlVUHniVI',
            'Content-Type: application/json'
        );

        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt( $ch, CURLOPT_URL, $url );

        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );

        // Execute post
        $result = curl_exec($ch);

        // Close connection
        curl_close($ch);

        $jsonOutput = json_decode($result, true);

        return (isset($jsonOutput['success']) && $jsonOutput['success'] == "1") ? true : false;
    }

    private function SendPushWindowsPhone($groupid,$title,$message)
    {
        // Create the toast message
        $toastMessage = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<wp:Notification xmlns:wp=\"WPNotification\">" .
            "<wp:Toast>" .
            "<wp:Text1>" . $title . "</wp:Text1>" .
            "<wp:Text2>" . $message . "</wp:Text2>" .
            "<wp:Param>/GroupPivot.xaml?gid=".$groupid."&amp;NavigatedFrom=toast</wp:Param>" .
            "</wp:Toast> " .
            "</wp:Notification>";

        // Create request to send
        $r = curl_init();
        curl_setopt($r, CURLOPT_URL,$this->push_reg);
        curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($r, CURLOPT_POST, true);
        curl_setopt($r, CURLOPT_HEADER, true);

        // add headers
        $httpHeaders = array('Content-type: text/xml; charset=utf-8', 'X-WindowsPhone-Target: toast',
            'Accept: application/*', 'X-NotificationClass: 2','Content-Length:'.strlen($toastMessage));
        curl_setopt($r, CURLOPT_HTTPHEADER, $httpHeaders);

        // add message
        curl_setopt($r, CURLOPT_POSTFIELDS, $toastMessage);

        // execute request
        $result = curl_exec($r);

        list($headers, $response) = explode("\r\n\r\n", $result, 2);
        // $headers now has a string of the HTTP headers
        // $response is the body of the HTTP response
        $return = false;
        $headers = explode("\n", $headers);
        foreach($headers as $header) {
            if (stripos($header, 'X-SubscriptionStatus: Active') !== false) {
                $return = true;
            }
        }
        curl_close($r);
        return $return;
    }
	
	private function SendPushiPhone($message,$groupId,$message)
    {        
		$array = array(time(),$message, $this->push_reg,$groupId,$message);
		$auth = $this->encryptOrDecryptiPhone(json_encode($array),'encrypt');

		$url = 'https://88.159.101.190/monipal/counter.php';
		$data = array('y' => 'y', 'auth' => $auth);
		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data),
			),
		);
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
        return $result;
    }
	
	private function encryptOrDecryptiPhone($mprhase, $crypt) {
		$description = "KqQuErdVzqJknKSJa3PT2LTn"; // key
		$td = mcrypt_module_open('tripledes', '', 'ecb', '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $description, $iv);
		if ($crypt == 'encrypt')
		{
            $return_value = base64_encode(mcrypt_generic($td, $mprhase));
		}
		else
		{
            $return_value = mdecrypt_generic($td, base64_decode($mprhase));
		}
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $return_value;
	}
}
?>