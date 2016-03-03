<?php
class Dinner
{

    function __construct($idGroup, $idUsers, $idRole, $date, $description, $guests)
    {
        //$this->_idUsers = $idUsers;
        $this->idGroup = $idGroup;
        $this->idUsers = $idUsers;
        $this->idRole = $idRole;
        $this->date = $date;
        $this->description = $description;
        $this->NumberOfPersons = $guests;
    }

    // property declaration
    public $idUsers;
    public $idGroup;
    public $idRole;
    public $date;
    public $description;
    public $NumberOfPersons;

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setIdUsers($idUsers)
    {
        $this->idUsers = $idUsers;
    }

    public function getIdUsers()
    {
        return $this->idUsers;
    }

    public function setIdGroup($idGroup)
    {
        $this->idGroup = $idGroup;
    }

    public function getIdGroup()
    {
        return $this->idGroup;
    }

        public function getDate()
    {
        return $this->date;
    }

    public function getDescription()
    {
        return $this->description;

    }
    public function getNumberOfPersons()
    {
        return $this->NumberOfPersons;
    }

    public function getIdRole()
    {
        return $this->idRole;
    }

    public function ShowDinnerTableTop($users,$group,$weeks){
        global $lang,$settings,$DBObject;
        echo '<table><tr><th><a href="' . $settings['site_url'] . 'dinner/' . $group->id . '/left"><img src="' . $settings['site_url'] . 'images/arrow-2-ways.png"
        alt="switch" /></a></th><th></th>';
        foreach ($users as $value) {
             // http://www.monipal.com/dinner.php?gid=67&uid=81
            echo '<th><a style="color: white" href="' . $settings['site_url'] . 'dinner.php?gid='. $group->id . '&uid=' . $value->id . '">' . $value->firstName . '</a></th>';
        }
        echo '</tr>';
        $weeks = $weeks * 7;
        $weeks = abs($weeks);
        $weeks = ($weeks == 0 ? 7 : $weeks);
        //var_dump($weeks);
       // for ($d = 0 ; $d < 7 ; $d++) {
        for ($d = ($weeks - 7) ; $d < $weeks ; $d++) {
           if ($d == 0) {
                echo ' <tr><th class="onepx">'.$lang['TODAY'].'</th>';
            }
            if ($d == 1) {
                echo '<tr><th class="onepx">'.$lang['TOMORROW'].'</th>';
            }
            if ($d > 1) {
                echo '<tr><th class="onepx">' . ucfirst(strftime('%a %d %b', strtotime(date('d-m-Y') .  ' + ' . $d . ' day'))) . '</th>';
            }

            $date = date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day'));
            $time = $DBObject->GetGroupDinnerTime($date, $group->id);
            echo '<td class="dinnertime" ><span name="'.$group->id.'" id="itm'.$d.'" >'.(isset($time) && !empty($time)  ? $time : '18:00').'</span><input name="'.$date.'" id="itm'.$d.'b"
            class="replace" type="text" value=""></td>';

            // get an array with dinners, each dinner contains a user, his/her role, and his/her number of guests
            $dinners = $DBObject->GetDinnerByDate($_GET['gid'], $date);

            // loops trough each user in the group
            foreach ($users as $value) {
                // if the user hasnt made a decision yet, requiresInput is 1
                $requiresInput = 1;
                if ($dinners != null) {
                    // loops trough each dinner
                    foreach ($dinners as $key => $dinval) {
                        // if the user is found in dinner set requiresInput on 0,
                        //  if the user is not found requiresInput = 1 and said user does not attend
                        if ($value->id == $key) {
                            $requiresInput = 0;

                            switch ($dinval->idRole) {
                                case DINNER_NOTHING_SET:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' .
                                        $value->id . '&quot;, &quot;date&quot;:&quot;' . $date .
                                        '&quot;, &quot;role&quot;:&quot;1&quot;}" class="nothingimg dinnerClick" href="' . $settings['site_url'] . 'dinner/'
                                        . $group->id . '/' . $value->id . '/' . $date . '/1">&nbsp;</a></td>';
                                    break;
                                case DINNER_NOT_EATING:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id
                                        . '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;-1&quot;}" class="noeatimg dinnerClick" href="'
                                        . $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/-1">&nbsp;</a></td>';
                                    break;
                                case DINNER_JOIN_DINNER:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;2&quot;, &quot;nopersons&quot;:&quot;'.
                                        $dinval->NumberOfPersons.'&quot;}" class="eatimg dinnerClick" href="' . $settings['site_url'] . 'dinner/' . $group->id .
                                        '/' . $value->id . '/' . $dinval->date . '/2">'.($dinval->NumberOfPersons >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').
                                        '</a></td>';
                                    break;
                                case DINNER_IS_COOK:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;0&quot;}" class="chefimg dinnerClick" href="' .
                                        $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/0/">'.($dinval->NumberOfPersons
                                        >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').'</a></td>';
                                    break;
                            }
                        }
                    }
                    if ($requiresInput == 1) {
                        echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                            '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;1&quot;}" class="nothingimg dinnerClick" href="' .
                            $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/1">&nbsp;</a></td>';
                    }

                } else {
                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                        '&quot;, &quot;date&quot;:&quot;' . $date . '&quot;, &quot;role&quot;:&quot;1&quot;}"
                        class="nothingimg dinnerClick" href="' . $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' .$date . '/1">&nbsp;</a></td>';
                }
            }

            echo '</tr>';
        }

        // STATISTICS
        $dinnerStats = $DBObject->GetDinnerStatistics($group->id);
        $dinnerStats2 = $DBObject->GetGroupUserAvgCookingCost($group->id);
        echo '<tr>
            <td colspan="2">'.$lang['COOKED'].'</td>';
        foreach ($users as $u) {
            if(isset($dinnerStats[$u->id][DINNER_IS_COOK])) // 2 staat voor dinner role
            {
                echo '<td>'.$dinnerStats[$u->id][DINNER_IS_COOK]->getCount().'</td>';
            }
            else
            {
                echo '<td>0</td>';
            }
        }

        echo '</tr>
        <tr>
        <td colspan="2">'.$lang['DINNERS_JOINED'].'</td>';
        foreach ($users as $u) {
            if(isset($dinnerStats[$u->id][DINNER_JOIN_DINNER])) // 1 staat voor dinner role
            {
                echo '<td>'.$dinnerStats[$u->id][DINNER_JOIN_DINNER]->getCount().'</td>';
            }
            else
            {
                echo '<td>0</td>';
            }
        }

        echo '</tr>
        <tr>
        <td colspan="2">'.$lang['RATIO'].'</td>';
        foreach ($users as $u) {
            if(isset($dinnerStats[$u->id][1]) && isset($dinnerStats[$u->id][DINNER_IS_COOK])) // 1 staat voor dinner role
            {
                echo '<td>'.number_format((float)round(($dinnerStats[$u->id][DINNER_IS_COOK]->getCount() / $dinnerStats[$u->id][DINNER_JOIN_DINNER]->getCount()),2), 2, '.', '')
                    .'</td>';
            }
            else
            {
                echo '<td>0</td>';
            }
        }

        echo '</tr>
        <tr>
        <td colspan="2">'.$lang['AVG_DINNERCOST'].'</td>';
        foreach ($users as $u) {
            echo '<td>'.$group->currency.' '.(isset($dinnerStats2[$u->id]) ? $dinnerStats2[$u->id][DINNER_JOIN_DINNER] : 0.00).'</td>';
        }
        echo '</tr>

        <tr>
        <td colspan="2">'.$lang['AVG_PPL_JOINING'].'</td>';
        foreach ($users as $u) {
            $avgMeeEters = $DBObject->GetDinnerrStaticsAvgMeeEters($group->id,$u->id);
            echo '<td>'.$avgMeeEters.'</td>';
        }

        echo '</tr>
        <tr>
        <td colspan="2">'.$lang['COOKING_POINTS'].'</td>';
        foreach ($users as $u) {
            echo '<td>'.(isset($dinnerStats2[$u->id]) ? $dinnerStats2[$u->id][DINNER_IS_COOK] : 0).'</td>';
        }

        echo '</tr></table>';
    }

    public function ShowDinnerTableLeft($users,$group,$weeks){
        global $lang,$settings,$DBObject;
        echo '<table><tr class="onepx"><th><a href="' . $settings['site_url'] . 'dinner/' . $group->id . '/top/"><img src="' . $settings['site_url'] .
            'images/arrow-2-ways.png" alt="switch" /></a></th>';

        $weeks = $weeks * 7;
        $weeks = abs($weeks);
        $weeks = ($weeks == 0 ? 7 : $weeks);
        for ($d = ($weeks - 7) ; $d < $weeks ; $d++) {
            echo '<th>' . ucfirst(strftime('%a %d %b', strtotime(date('d-m-Y') . ' + ' . $d . ' day'))) . '</th>';
        }
        echo '</tr>';
        echo '<tr><th></th>';
        for ($d = ($weeks - 7) ; $d < $weeks ; $d++) {
            $date = date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $d . ' day'));
            $time = $DBObject->GetGroupDinnerTime($date, $group->id);
            echo '<td class="dinnertime" ><span name="'.$group->id.'" id="itm'.$d.'" ">'.(isset($time) && !empty($time)  ? $time : '18:00').'</span><input name="'.$date.'"
             id="itm'.$d.'b" class="replace" type="text" value=""></td>';
        }
        echo '</tr>';
        foreach ($users as $value) {
            echo '<th><a style="color: white" href="' . $settings['site_url'] . 'dinner.php?gid='. $group->id . '&uid=' . $value->id . '">' . $value->firstName . '</a></th>';
            for ($d = ($weeks - 7) ; $d < $weeks ; $d++) {
                $date = date('Y-m-d', strtotime(date('Y-m-d') .  ' + ' . $d . ' day'));
                // get an array with dinners, each dinner contains a user, his/her role, and his/her number of guests
                $dinners = $DBObject->GetDinnerByDate($group->id,$date);
                // if the user hasnt made a decision yet, requiresInput is 1
                $requiresInput = 1;
                if ($dinners != null) {
                    // loops trough each dinner
                    foreach ($dinners as $key => $dinval) {
                        // if the user is found in dinner set requiresInput on 0,
                        //  if the user is not found requiresInput = 1 and said user does not attend
                        if ($value->id == $key) {
                            $requiresInput = 0;

                            switch ($dinval->idRole) {
                                case DINNER_NOTHING_SET:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' .$date . '&quot;, &quot;role&quot;:&quot;1&quot;}"
                                        class="nothingimg dinnerClick" href="' . $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' .
                                        $date . '/1">&nbsp;</a></td>';
                                    break;
                                case DINNER_NOT_EATING:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;-1&quot;}" class="noeatimg dinnerClick" href="' .
                                        $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/-1">&nbsp;</a></td>';
                                    break;
                                case DINNER_JOIN_DINNER:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;2&quot;, &quot;nopersons&quot;:&quot;'.
                                        $dinval->NumberOfPersons.'&quot;}" class="eatimg dinnerClick" href="' . $settings['site_url'] . 'dinner/' . $group->id . '/' .
                                        $value->id . '/' . $dinval->date . '/2">'.($dinval->NumberOfPersons >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').'</a></td>';
                                    break;
                                case DINNER_IS_COOK:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;0&quot;}" class="chefimg dinnerClick" href="' .
                                        $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/0">'.
                                        ($dinval->NumberOfPersons >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').'</a></td>';
                                    break;
                            }
                        }
                    }
                    if ($requiresInput == 1) {
                        echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                            '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;1&quot;}" class="nothingimg dinnerClick" href="' .
                            $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/1">&nbsp;</a></td>';
                    }

                } else {
                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                        '&quot;, &quot;date&quot;:&quot;' . $date . '&quot;, &quot;role&quot;:&quot;1&quot;}"
                        class="nothingimg dinnerClick" href="' . $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $date . '/1">&nbsp;</a></td>';
                }
            }
            echo '</tr>';
        }
        echo '    </table>';
    }

    public function ShowStatTableLeft($users,$group){
        global $lang,$DBObject;
        echo '<table><tr><th></th>';

        echo '<th>'.$lang['COOKED'].'</th><th>'.$lang['DINNERS_JOINED'].'</th><th>'.$lang['RATIO'].'</th><th>'.$lang['AVG_DINNERCOST'].'</th><th>'.$lang['AVG_PPL_JOINING'].'
        </th><th>'.$lang['COOKING_POINTS'].'</th></tr>';
        // STATISTICS
        $dinnerStats = $DBObject->GetDinnerStatistics($group->id);
        $dinnerStats2 = $DBObject->GetGroupUserAvgCookingCost($group->id);
        foreach ($users as $value) {
            echo '<th>' . $value->firstName . '</th>';

            if(isset($dinnerStats[$value->id][2])) // 2 staat voor dinner role
            {
                echo '<td>'.$dinnerStats[$value->id][2]->getCount().'</td>';
            }
            else
            {
                echo '<td>0</td>';
            }
            if(isset($dinnerStats[$value->id][1])) // 1 staat voor dinner role
            {
                echo '<td>'.$dinnerStats[$value->id][1]->getCount().'</td>';
            }
            else
            {
                echo '<td>0</td>';
            }
            if(isset($dinnerStats[$value->id][1]) && isset($dinnerStats[$value->id][2])) // 1 staat voor dinner role
            {
                echo '<td>'.number_format((float)round(($dinnerStats[$value->id][2]->getCount() / $dinnerStats[$value->id][1]->getCount()),2), 2, '.', '').'</td>';
            }
            else
            {
                echo '<td>0</td>';
            }
            echo '<td>'.$group->currency.' '.(isset($dinnerStats2[$value->id]) ? $dinnerStats2[$value->id][1] : 0.00).'</td>';
            $avgMeeEters = $DBObject->GetDinnerrStaticsAvgMeeEters($group->id,$value->id);
            echo '<td>'.$avgMeeEters.'</td>';
            echo '<td>'.(isset($dinnerStats2[$value->id]) ? $dinnerStats2[$value->id][2] : 0).'</td>';
            echo '</tr>';
        }
        echo '    </table>';
    }
	
	public function ShowDinnerTableHistoryTop($users,$group,$weeks){
        global $lang,$settings,$DBObject;
        echo '<table><tr><th><a href="' . $settings['site_url'] . 'dinner/' . $group->id . '/left"><img src="' . $settings['site_url'] . 'images/arrow-2-ways.png"
        alt="switch" /></a></th><th></th>';
        foreach ($users as $value) {
             // http://www.monipal.com/dinner.php?gid=67&uid=81
            echo '<th><a style="color: white" href="' . $settings['site_url'] . 'dinner.php?gid='. $group->id . '&uid=' . $value->id . '">' . $value->firstName . '</a></th>';
        }
        echo '</tr>';
        $weeks = $weeks * 7;
        for($d = $weeks; $d > ($weeks - 7); $d--){
            $nd = abs($d);
            echo '<tr><th class="onepx">' . ucfirst(strftime('%a %d %b', strtotime(date('d-m-Y') . ' - ' . $nd . ' day'))) . '</th>';

            $date = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $nd . ' day'));
            $time = $DBObject->GetGroupDinnerTime($date, $group->id);
            echo '<td class="dinnertime" ><span name="'.$group->id.'" id="itm'.$nd.'" >'.(isset($time) && !empty($time)  ? $time : '18:00').'</span><input name="'.$date.'" id="itm'.$nd.'b"
            class="replace" type="text" value=""></td>';

            // get an array with dinners, each dinner contains a user, his/her role, and his/her number of guests
            $dinners = $DBObject->GetDinnerByDate($_GET['gid'],$date);

            // loops trough each user in the group
            foreach ($users as $value) {
                // if the user hasnt made a decision yet, requiresInput is 1
                $requiresInput = 1;
                if ($dinners != null) {
                    // loops trough each dinner
                    foreach ($dinners as $key => $dinval) {
                        // if the user is found in dinner set requiresInput on 0,
                        //  if the user is not found requiresInput = 1 and said user does not attend
                        if ($value->id == $key) {
                            $requiresInput = 0;

                            switch ($dinval->idRole) {
                                case DINNER_NOTHING_SET:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' .
                                        $value->id . '&quot;, &quot;date&quot;:&quot;' . $date .
                                        '&quot;, &quot;role&quot;:&quot;1&quot;}" class="nothingimg dinnerClick" href="' . $settings['site_url'] . 'dinner/'
                                        . $group->id . '/' . $value->id . '/' . $date . '/1">&nbsp;</a></td>';
                                    break;
                                case DINNER_NOT_EATING:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id
                                        . '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;-1&quot;}" class="noeatimg dinnerClick" href="'
                                        . $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/-1">&nbsp;</a></td>';
                                    break;
                                case DINNER_JOIN_DINNER:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;2&quot;, &quot;nopersons&quot;:&quot;'.
                                        $dinval->NumberOfPersons.'&quot;}" class="eatimg dinnerClick" href="' . $settings['site_url'] . 'dinner/' . $group->id .
                                        '/' . $value->id . '/' . $dinval->date . '/2">'.($dinval->NumberOfPersons >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').
                                        '</a></td>';
                                    break;
                                case DINNER_IS_COOK:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;0&quot;}" class="chefimg dinnerClick" href="' .
                                        $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/0/">'.($dinval->NumberOfPersons
                                        >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').'</a></td>';
                                    break;
                            }
                        }
                    }
                    if ($requiresInput == 1) {
                        echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                            '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;1&quot;}" class="nothingimg dinnerClick" href="' .
                            $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/1">&nbsp;</a></td>';
                    }

                } else {
                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                        '&quot;, &quot;date&quot;:&quot;' . $date . '&quot;, &quot;role&quot;:&quot;1&quot;}"
                        class="nothingimg dinnerClick" href="' . $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $date . '/1">&nbsp;</a></td>';
                }
            }

            echo '</tr>';
        }

        // STATISTICS
        $dinnerStats = $DBObject->GetDinnerStatistics($group->id);
        $dinnerStats2 = $DBObject->GetGroupUserAvgCookingCost($group->id);
        echo '<tr>
            <td colspan="2">'.$lang['COOKED'].'</td>';
        foreach ($users as $u) {
            if(isset($dinnerStats[$u->id][DINNER_IS_COOK])) // 2 staat voor dinner role
            {
                echo '<td>'.$dinnerStats[$u->id][DINNER_IS_COOK]->getCount().'</td>';
            }
            else
            {
                echo '<td>0</td>';
            }
        }

        echo '</tr>
        <tr>
        <td colspan="2">'.$lang['DINNERS_JOINED'].'</td>';
        foreach ($users as $u) {
            if(isset($dinnerStats[$u->id][DINNER_JOIN_DINNER])) // 1 staat voor dinner role
            {
                echo '<td>'.$dinnerStats[$u->id][DINNER_JOIN_DINNER]->getCount().'</td>';
            }
            else
            {
                echo '<td>0</td>';
            }
        }

        echo '</tr>
        <tr>
        <td colspan="2">'.$lang['RATIO'].'</td>';
        foreach ($users as $u) {
            if(isset($dinnerStats[$u->id][1]) && isset($dinnerStats[$u->id][DINNER_IS_COOK])) // 1 staat voor dinner role
            {
                echo '<td>'.number_format((float)round(($dinnerStats[$u->id][DINNER_IS_COOK]->getCount() / $dinnerStats[$u->id][DINNER_JOIN_DINNER]->getCount()),2), 2, '.', '')
                    .'</td>';
            }
            else
            {
                echo '<td>0</td>';
            }
        }

        echo '</tr>
        <tr>
        <td colspan="2">'.$lang['AVG_DINNERCOST'].'</td>';
        foreach ($users as $u) {
            echo '<td>'.$group->currency.' '.(isset($dinnerStats2[$u->id]) ? $dinnerStats2[$u->id][DINNER_JOIN_DINNER] : 0.00).'</td>';
        }
        echo '</tr>

        <tr>
        <td colspan="2">'.$lang['AVG_PPL_JOINING'].'</td>';
        foreach ($users as $u) {
            $avgMeeEters = $DBObject->GetDinnerrStaticsAvgMeeEters($group->id,$u->id);
            echo '<td>'.$avgMeeEters.'</td>';
        }

        echo '</tr>
        <tr>
        <td colspan="2">'.$lang['COOKING_POINTS'].'</td>';
        foreach ($users as $u) {
            echo '<td>'.(isset($dinnerStats2[$u->id]) ? $dinnerStats2[$u->id][DINNER_IS_COOK] : 0).'</td>';
        }

        echo '</tr></table>';
    }

    public function ShowDinnerTableHistoryLeft($users,$group,$weeks){
        global $lang,$settings,$DBObject;
        echo '<table><tr class="onepx"><th><a href="' . $settings['site_url'] . 'dinner/' . $group->id . '/top/"><img src="' . $settings['site_url'] .
            'images/arrow-2-ways.png" alt="switch" /></a></th>';

        $weeks = $weeks * 7;
        for($d = $weeks; $d > ($weeks - 7); $d--){
            $nd = abs($d);
            echo '<th>' . ucfirst(strftime('%a %d %b', strtotime(date('d-m-Y') .  ' - '. $nd . ' day'))) . '</th>';

        }
        echo '</tr>';
        echo '<tr><th></th>';
        for($d = $weeks; $d > ($weeks - 7); $d--){
            $nd = abs($d);
            $date = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $nd . ' day'));
            $time = $DBObject->GetGroupDinnerTime($date, $group->id);
            echo '<td class="dinnertime" ><span name="'.$group->id.'" id="itm'.$nd.'" ">'.(isset($time) && !empty($time)  ? $time : '18:00').'</span><input name="'.$date.'"
             id="itm'.$nd.'b" class="replace" type="text" value=""></td>';
        }
        echo '</tr>';
        foreach ($users as $value) {
            echo '<th><a style="color: white" href="' . $settings['site_url'] . 'dinner.php?gid='. $group->id . '&uid=' . $value->id . '">' . $value->firstName . '</a></th>';
            for($d = $weeks; $d > ($weeks - 7); $d--){
                $nd = abs($d);
                $date = date('Y-m-d', strtotime(date('Y-m-d') .' - '. $nd . ' day'));
                // get an array with dinners, each dinner contains a user, his/her role, and his/her number of guests
                $dinners = $DBObject->GetDinnerByDate($group->id,$date);
                // if the user hasnt made a decision yet, requiresInput is 1
                $requiresInput = 1;
                if ($dinners != null) {
                    // loops trough each dinner
                    foreach ($dinners as $key => $dinval) {
                        // if the user is found in dinner set requiresInput on 0,
                        //  if the user is not found requiresInput = 1 and said user does not attend
                        if ($value->id == $key) {
                            $requiresInput = 0;

                            switch ($dinval->idRole) {
                                case DINNER_NOTHING_SET:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $date . '&quot;, &quot;role&quot;:&quot;1&quot;}"
                                        class="nothingimg dinnerClick" href="' . $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' .
                                        $date . '/1">&nbsp;</a></td>';
                                    break;
                                case DINNER_NOT_EATING:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;-1&quot;}" class="noeatimg dinnerClick" href="' .
                                        $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/-1">&nbsp;</a></td>';
                                    break;
                                case DINNER_JOIN_DINNER:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;2&quot;, &quot;nopersons&quot;:&quot;'.
                                        $dinval->NumberOfPersons.'&quot;}" class="eatimg dinnerClick" href="' . $settings['site_url'] . 'dinner/' . $group->id . '/' .
                                        $value->id . '/' . $dinval->date . '/2">'.($dinval->NumberOfPersons >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').'</a></td>';
                                    break;
                                case DINNER_IS_COOK:
                                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                                        '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;0&quot;}" class="chefimg dinnerClick" href="' .
                                        $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/0">'.
                                        ($dinval->NumberOfPersons >= 2 ? ' x'.$dinval->NumberOfPersons : '&nbsp;').'</a></td>';
                                    break;
                            }
                        }
                    }
                    if ($requiresInput == 1) {
                        echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                            '&quot;, &quot;date&quot;:&quot;' . $dinval->date . '&quot;, &quot;role&quot;:&quot;1&quot;}" class="nothingimg dinnerClick" href="' .
                            $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $dinval->date . '/1">&nbsp;</a></td>';
                    }

                } else {
                    echo '<td class="nopadding"><a name="{&quot;gid&quot;:&quot;' . $group->id . '&quot;, &quot;uid&quot;:&quot;' . $value->id .
                        '&quot;, &quot;date&quot;:&quot;' . date('Y-m-d', strtotime(date('Y-m-d') . ($weeks > 7 ? ' - ' : ' + ') . $d . ' day')) . '&quot;, &quot;role&quot;:&quot;1&quot;}"
                        class="nothingimg dinnerClick" href="' . $settings['site_url'] . 'dinner/' . $group->id . '/' . $value->id . '/' . $date. '/1">&nbsp;</a></td>';
                }
            }
            echo '</tr>';
        }
        echo '    </table>';
    }
}

?>