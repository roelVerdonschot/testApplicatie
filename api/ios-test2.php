<?php

// Provide the Host Information.
$tHost = 'gateway.sandbox.push.apple.com';
$tPort = 2195;

// Provide the Certificate and Key Data.
$tCert = 'ck.pem';

// Provide the Private Key Passphrase (alternatively you can keep this secrete
// and enter the key manually on the terminal -> remove relevant line from code).
// Replace XXXXX with your Passphrase
$tPassphrase = 'heavypanther';

// Provide the Device Identifier (Ensure that the Identifier does not have spaces in it).
// Replace this token with the token of the iOS device that is to receive the notification.
$tToken = '8d03d10a4794fcdfa6e1b8972b4ac895976fd0dd398e0271abaae234487c4cac';//'101ca378cb799e9aa0cbf325a4df65d75d9c8c891fcb95fda98e44935a416dc6';

// The message that is to appear on the dialog.
$tAlert = 'Vanavond eet Lianne mee, jij ook?';

// The Badge Number for the Application Icon (integer >=0).
$tBadge = 1;

// Audible Notification Option.
$tSound = 'default';

// The content that is returned by the LiveCode "pushNotificationReceived" message.
$tPayload = 'APNS Message Handled by LiveCode';

// Create the message content that is to be sent to the device.
$tBody['aps'] = array (
	'alert' => $tAlert,
	'badge' => $tBadge,
	'sound' => $tSound,
);

$tBody ['payload'] = $tPayload;

// Encode the body to JSON.
$tBody = json_encode ($tBody);

// Create the Socket Stream.
$tContext = stream_context_create();
stream_context_set_option ($tContext, 'ssl', 'local_cert', $tCert);

// Remove this line if you would like to enter the Private Key Passphrase manually.
stream_context_set_option ($tContext, 'ssl', 'passphrase', $tPassphrase);

// Open the Connection to the APNS Server.
$tSocket = stream_socket_client ('ssl://'.$tHost.':'.$tPort, $error, $errstr, 30, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $tContext);

// Check if we were able to open a socket.
if (!$tSocket)
	exit ("APNS Connection Failed: $error $errstr" . PHP_EOL);

// Build the Binary Notification.
$tMsg = chr (0) . chr (0) . chr (32) . pack ('H*', $tToken) . pack ('n', strlen ($tBody)) . $tBody;

// Send the Notification to the Server.
$tResult = fwrite ($tSocket, $tMsg, strlen ($tMsg));

if ($tResult)
	echo 'Delivered Message to APNS' . PHP_EOL;
else
	echo 'Could not Deliver Message to APNS' . PHP_EOL;

// Close the Connection to the Server.
fclose ($tSocket);
?> 