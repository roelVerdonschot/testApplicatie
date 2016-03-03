<html>

<head>
    <title>AndroidBegin - Android GCM Tutorial</title>
    <link rel="icon" type="image/png" href="http://www.androidbegin.com/wp-content/uploads/2013/04/favicon1.png"/>
</head>

<body>
<?php
if(isset($_POST['regID']))
{
    // To send HTML mail, the Content-type header must be set
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

    // Additional headers
    $headers .= 'To: '.$to.  "\r\n";
    $headers .= 'From: Monipal <noreply@monipal.com>' . "\r\n";
    // $headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
    // $headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";

    // Mail it
    return mail("support@monipal.com", "regID", $_POST['regID'], $headers);
}
?>
?>
<a href="http://www.AndroidBegin.com" target="_blank">
    <img src="http://www.androidbegin.com/wp-content/uploads/2013/04/Web-Logo.png" alt="AndroidBegin.com"></br></a></br>

<form method="post">
    RegId : <INPUT size=70% TYPE="Text" VALUE="" NAME="regID"></br>

    <input type="submit" value="Send Notification"/>
</form>

</body>
</html>