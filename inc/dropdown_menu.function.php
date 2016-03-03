<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Pascal
 * Date: 6-8-13
 * Time: 20:01
 * To change this template use File | Settings | File Templates.
 */

function getLang($selected){
    global $settings;
    foreach ($settings['languages'] as $key => $value) {
        if ($selected == $key) {
            return $value;
        }
    }
}

function getCountry($selected){
    global $settings;
    foreach ($settings['country_array'] as $key => $value) {
        if ($selected == $key) {
            return $value;
        }
    }
}

function countryArray($name, $selected)
{
    global $settings;

    $str = '<select name="'.$name.'" class="'.$name.'">';

    foreach ($settings['country_array'] as $key => $value) {
        if ($selected == $key) {
            $thisExtra = " SELECTED";
        } else {
            $thisExtra = "";
        }
        $str .= '<option value="'.$key.'"' . $thisExtra . '>' . stripslashes($value) .'</option>';
    }
    $str .= "</select>";
    return $str;
}
function languageArray($name, $selected)
{
    global $settings;
    $str = '<select  name="'.$name.'" class="'.$name.'">';

    foreach ($settings['languages'] as $key => $value) {
        if ($selected == $key) {
            $thisExtra = " SELECTED";
        } else {
            $thisExtra = "";
        }
        $str .= '<option value="'.$key.'"' . $thisExtra . '>' . stripslashes($value) .'</option>';
    }
    $str .= "</select>";
    return $str;
}

function languageSelectFooter($name, $selected)
{
    global $settings;
    $str = '<select onChange="window.location.href=this.value" name="'.$name.'" class="'.$name.'">';

    if(!Authentication_Controller::IsAuthenticated())
    {
        $request = parse_url($_SERVER['REQUEST_URI']);
        $result = rtrim(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $request["path"]), '/');
        if(substr($result, 1, 2) != null)
        {
            if(array_key_exists(substr($result, 1, 2),$settings['languages']))
            {
                $result = substr($result, 4);
            }
        }
    }
    foreach ($settings['languages'] as $key => $value) {
        if ($selected == $key) {
            $thisExtra = " SELECTED";
        } else {
            $thisExtra = "";
        }
        if(Authentication_Controller::IsAuthenticated())
        {
            unset($_GET['editLang']);
            $str .= '<option value="'.$settings['site_url'].$_SERVER["PHP_SELF"].'?'.http_build_query($_GET + array("editLang"=>$key)).'"' . $thisExtra . '>' . stripslashes($value) .'</option>';
        }
        else
        {
            $str .= '<option value="'.$settings['site_url'].$key.'/'.$result.'"' . $thisExtra . '>' . stripslashes($value) .'</option>';
        }
    }
    $str .= "</select>";
    return $str;
}

function currencyArray($name, $selected)
{
    $currency_Array = array('EU' => '€ euro','$' => '$ dollar','£' => '£ pound'); //,'R' => 'R'
    $str = '<select name="'.$name.'" class="'.$name.'">';

    foreach ($currency_Array as $key => $value) {
        if ($selected == $value) {
            $thisExtra = " SELECTED";
        } else {
            $thisExtra = "";
        }
        $str .= '<option value="'.$key.'"' . $thisExtra . '>' . stripslashes($value) .'</option>';
    }
    $str .= "</select>";
    return $str;
}

function numberArray($name, $selected, $start = 0)
{
    $str = '<select name="'.$name.'" id="'.$name.'" class="numbers">';
    for($i = $start; $i < 26; $i++)
    {
        if ($selected == $i) {
            $thisExtra = " SELECTED";
        } else {
            $thisExtra = "";
        }
        $str .= '<option value="'.$i.'"' . $thisExtra . '>' . stripslashes($i) .'</option>';
    }
    $str .= '</select>';
    return $str;
}

// array with the coming 7days
function dateArray($name, $selected, $weeks)
{
    global $lang;
    $date = array();
    $weeks = $weeks * 7;
    if($weeks >= 7){
        for ($d = ($weeks - 7) ; $d < $weeks ; $d++)
        {
            if($d == 0)
            {
                $date[date('Y-m-d', strtotime(date('d-m-Y') . ' + ' . $d . ' day'))] = $lang['TODAY'];
            }
            elseif ($d == 1)
            {
                $date[date('Y-m-d', strtotime(date('d-m-Y') . ' + ' . $d . ' day'))] = $lang['TOMORROW'];
            }
            else
            {
                $date[date('Y-m-d', strtotime(date('d-m-Y') .' + ' . $d . ' day'))] = date('d-m-Y', strtotime(date('d-m-Y') . ' + ' . $d . ' day'));
            }
        }

    }
    else{
        for($d = $weeks; $d > ($weeks - 7); $d--){
            $nd = abs($d);
            $date[date('Y-m-d', strtotime(date('d-m-Y') . ' - ' . $nd . ' day'))] = date('d-m-Y', strtotime(date('d-m-Y') . ' - ' . $nd . ' day'));
        }
    }

    $str = '<select name="'.$name.'" id="'.$name.'" class="'.$name.'" onchange="this.form.submit();">';

    foreach ($date as $key => $value) {
        if ($selected == $key) {
            $thisExtra = " SELECTED";
        } else {
            $thisExtra = "";
        }
        $str .= '<option value="'.$key.'"' . $thisExtra . '>' . stripslashes($value) .'</option>';

    }
    $str .= "</select>";
    return $str;
}

function dayOfWeekList($name, $selected)
{
    global $lang;

    $str = '<select name="'.$name.'" id="'.$name.'" class="'.$name.'">';
    $str .= '<option value="-1"'.($selected == null ? ' SELECTED':'').'>' . $lang['SELECT']  .'</option>';
    $timestamp = strtotime('next Sunday');
    for ($i = 0; $i < 7; $i++) {
        if ($selected === $i) {
            $thisExtra = " SELECTED";
        } else {
            $thisExtra = "";
        }
        $str .= '<option value="'.$i.'"' . $thisExtra . '>' . strftime('%A', $timestamp) .'</option>';
        $timestamp = strtotime('+1 day', $timestamp);
    }
    $str .= "</select>";
    return $str;
}

function yesNoArray($name, $selected)
{
    global $lang;
    $number_Array = array('0' => $lang['NO'],'1' => $lang['YES']);
    $str = '<select name="'.$name.'" class="'.$name.'">';

    foreach ($number_Array as $key => $value) {
        if ($selected == $key) {
            $thisExtra = " SELECTED";
        } else {
            $thisExtra = "";
        }
        $str .= '<option value="'.$key.'"' . $thisExtra . '>' . stripslashes($value) .'</option>';
    }
    $str .= "</select>";
    return $str;
}

?>