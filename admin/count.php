<!DOCTYPE html>
<head>
    <meta charset="utf-8" />
    
    <title>Count Monipal</title>
    
    <script src="/js/libs/modernizr-2.6.1.min.mobile.js"></script>
    <meta name="HandheldFriendly" content="True" />
    <meta name="MobileOptimized" content="320" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="cleartype" content="on" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="icons/icon144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="icons/icon114.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="icons/icon72.png" />
    <link rel="apple-touch-icon-precomposed" href="icons/icon57.png" />
    <link rel="shortcut icon" href="icons/icon144.png" />

    <meta name="msapplication-TileImage" content="icons/icon144.png" />
    <meta name="msapplication-TileColor" content="#e14b41" />

</head>
<body>

<img src="http://www.monipal.com/images/logoweb2.png"/>
<?php

$db = new PDO('mysql:host=localhost;dbname=monipal_final;charset=utf8', 'monipal_webfinal', 'lyeb4cbU', array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
try {
    //connect as appropriate as above
    $stmt10 = $db->query('SELECT count(1) as sum_users FROM `monipal_final`.`user`'); //invalid query!
    $results10 = $stmt10->fetchAll(PDO::FETCH_ASSOC);
    echo '<br />Total users: ' . ($results10[0]['sum_users'] - 16);

    $stmt = $db->query('SELECT count(1) as sum_users FROM `monipal_final`.`user` WHERE activation is null'); //invalid query!
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo '<br />Activated users: ' . ($results[0]['sum_users'] - 16);

    $stmt9 = $db->query('SELECT count(1) as sum_users FROM `monipal_final`.`user` WHERE activation is not null'); //invalid query!
    $results9 = $stmt9->fetchAll(PDO::FETCH_ASSOC);
    echo '<br />Not activated users: ' . ($results9[0]['sum_users']);
	
	$stmt2 = $db->query('SELECT count(1) as sum_groups FROM `monipal_final`.`group`'); //invalid query!
    $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo '<br /><br />Groups: ' . ($results2[0]['sum_groups'] - 5);
    $stmt11 = $db->query('SELECT idgroup, COUNT(*) AS cnt FROM user_group GROUP BY idgroup HAVING COUNT(*) > 1'); //invalid query!
    $results11 = $stmt11->fetchAll(PDO::FETCH_ASSOC);
    echo '<br />More than 1 user in group: ' . (COUNT($results11));

    $stmt3 = $db->query('SELECT count(1) as sum_thisweek FROM session WHERE date_created BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()'); //invalid query!
    $results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    echo '<br /><br />Users logged in this week: ' . ($results3[0]['sum_thisweek']);

    $stmt4 = $db->query('SELECT count(1) as sum_today FROM session WHERE date_created BETWEEN DATE_SUB(NOW(), INTERVAL 1 DAY) AND NOW()'); //invalid query!
    $results4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    echo '<br />Users logged in today: ' . ($results4[0]['sum_today']);

    $stmt6 = $db->query('SELECT count(1) as mobileLogins FROM device WHERE last_connected BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()'); //invalid query!
    $results6 = $stmt6->fetchAll(PDO::FETCH_ASSOC);
    echo '<br />Mobile logins this week: ' . ($results6[0]['mobileLogins']);

    $stmt7 = $db->query('SELECT count(1) as mobileLogins FROM device WHERE last_connected BETWEEN DATE_SUB(NOW(), INTERVAL 1 DAY) AND NOW()'); //invalid query!
    $results7 = $stmt7->fetchAll(PDO::FETCH_ASSOC);
    echo '<br />Mobile logins today: ' . ($results7[0]['mobileLogins']);

    $stmt5 = $db->query('SELECT count(1) as invites FROM invite'); //invalid query!
    $results5 = $stmt5->fetchAll(PDO::FETCH_ASSOC);
    echo '<br /><br />Open invites: ' . ($results5[0]['invites']);

    $stmt8 = $db->query('SELECT count(1) as oldInvites FROM invite WHERE date < NOW() - INTERVAL 2 WEEK'); //invalid query!
    $results8 = $stmt8->fetchAll(PDO::FETCH_ASSOC);
    echo '<br />Open invites older than 2 weeks: ' . ($results8[0]['oldInvites']);

} catch(PDOException $ex) {
    echo "An Error occured!"; //user friendly message
}


?>
</body>
</html>