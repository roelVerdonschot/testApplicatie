<?php
// Report all errors except E_NOTICE
error_reporting(E_ALL ^ E_NOTICE);

?><!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>Kostenbeheer, eetlijst, takenlijst, Monipal.com. Houd je gezamenlijk leven overzichtelijk. | Monipal</title>
    <meta name="robots" content="noindex, nofollow" />


    <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
</head>
<body>

<?php
$langArray = array();
foreach(glob('../inc/languages/lang.*.php') as $file) {
    include $file;
    $langArray[] = $lang;
    unset($lang);
}

if (isset($_POST["Submit"])) {


    foreach($langArray as $lang) {
        $output[$lang['_LANG_CODE']] = '<?php
    ';
        foreach($_POST as $key) {
            $output[$lang['_LANG_CODE']] .= '$lang[\''.$key[0].'\'] = \''.$key[$lang['_LANG_CODE']].'\';'."\xA";
        }
        $output[$lang['_LANG_CODE']] .= '
    ?>';
    }
        ;


    echo $output['nl'];



    //$fp = fopen("test.php", "w");

   // fwrite($fp, $string);

    //fclose($fp);



}
$diff = array_diff_key($langArray[0],$langArray[1]);


echo '

<form action="" method="post" name="install" id="install">
<p>Er zijn '.count($diff).' errors!</p>
    <table>
        <tr>
            <th>Key</th>';
    foreach($langArray as $lang) {
        echo '<th>'.$lang['_LANG_CODE'].'</th>';
    }
    echo '</tr>';

//Error rijen:
    foreach($diff as $diffKey=>$diffValue) {
        echo '<tr>';
        echo '<td>[ERROR]<input name="'.$diffKey.'[]" type="text" size="30" value="'.$diffKey.'"></td>';
        foreach($langArray as $lang) {
            if(preg_match("/<[^<]+>/",$lang[$diffKey],$m) != 0 || strlen($lang[$diffKey]) > 75 )// check for html
            {
                echo '<td><texterea name="'.$diffKey.'['.$lang['_LANG_CODE'].']" rows="4" cols="55">'.$lang[$diffKey].'</texterea></td>';
            }
            else
            {
                echo '<td><input name="'.$diffKey.'['.$lang['_LANG_CODE'].']" type="text" size="75" value="'.$lang[$diffKey].'"></td>';
            }
        }
        echo '</tr>';
    }


//Normale rijeen
    foreach($langArray[0] as $langKey=>$value) {
        echo '<tr>';
        echo '<td><input name="'.$langKey.'[]" type="text" size="30" value="'.$langKey.'"></td>';
        foreach($langArray as $lang) {
            if(preg_match("/<[^<]+>/",$lang[$langKey],$m) != 0 || strlen($lang[$langKey]) > 75 )// check for html
            {
                echo '<td><TEXTAREA Name="'.$langKey.'['.$lang['_LANG_CODE'].']" rows="4" cols="55">'.$lang[$langKey].'</TEXTAREA></td>';
            }
            else
            {
                echo '<td><input name="'.$langKey.'['.$lang['_LANG_CODE'].']" type="text" size="75" value="'.$lang[$langKey].'"></td>';
            }
        }
        echo '</tr>';
    }
    ?>


    </table>
    <p>

        <input type="submit" name="Submit" value="SAVE">

    </p>

</form>
</body>
</html>