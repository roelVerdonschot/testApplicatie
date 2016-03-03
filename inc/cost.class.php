<?php
class Cost
{
	function __construct()
	{
	}
    // property declaration
    public $id;
	public $amount;
	public $isPaid;
	public $description;
	public $idUser;
    public $nameUser;
	public $idGroup;
	public $isDinner;
	public $date;
    public $users;
    public $numberOfUsers;
    public $deleted;

    public static function withCosts($idcosts,$amount,$iduser,$idgroup,$numberOfUsers)
    {
        $instance = new Cost();
        $instance->loadCosts($idcosts,$amount,$iduser,$idgroup,$numberOfUsers);
        return $instance;
    }

    protected function loadCosts($idcosts,$amount,$iduser,$idgroup,$numberOfUsers)
    {
        $this->id = $idcosts;
        $this->amount = $amount;
        $this->idUser = $iduser;
        $this->idGroup = $idgroup;
        $this->numberOfUsers = $numberOfUsers;
    }

    public static function withCostUpdate($idCost,$amount,$description,$iduser,$date,$users,$deleted)
    {
        $instance = new Cost();
        $instance->loadCostUpdate($idCost,$amount,$description,$iduser,$date,$users,$deleted);
        return $instance;
    }

    protected function loadCostUpdate($idCost,$amount,$description,$iduser,$date,$users,$deleted)
    {
		$this->id = $idCost;
        $this->amount = $amount;
        $this->description = $description;
        $this->idUser = $iduser;
        $this->date = $date;
        $this->users = $users;
        $this->deleted = $deleted;
    }

    public static function withCostsUser($idcosts,$iduser,$numberOfUsers)
    {
        $instance = new Cost();
        $instance->loadCostsUser($idcosts,$iduser,$numberOfUsers);
        return $instance;
    }

    protected function loadCostsUser($idcosts,$iduser,$numberOfUsers)
    {
        $this->id = $idcosts;
        $this->idUser = $iduser;
        $this->numberOfUsers = $numberOfUsers;
    }

    public static function withLFullCost($idcost, $amount, $description, $iduser, $nameuser, $idgroup, $is_dinner, $date_of_cost,$users,$deleted)
    {
        $instance = new Cost();
        $instance->loadFullCost($idcost, $amount, $description, $iduser, $nameuser, $idgroup, $is_dinner, $date_of_cost,$users,$deleted);
        return $instance;
    }

    protected function loadFullCost($idcost, $amount, $description, $iduser, $nameuser,$idgroup, $is_dinner, $date_of_cost,$users,$deleted)
    {
        $this->id = $idcost;
        $this->amount = $amount;
        $this->description = $description;
        $this->idUser = $iduser;
        $this->nameUser = $nameuser;
        $this->idGroup = $idgroup;
        $this->isDinner = $is_dinner;
        $this->date = $date_of_cost;
        $this->users = $users;
        $this->deleted = $deleted;
    }

    public function getId(){
        return $this->id;
    }

    public function getAmount(){
        return $this->amount;
    }

    public function setAmount($amount){
        $this->amount = $amount;
    }

    public function getIsPaid(){
        return $this->isPaid;
    }

    public function getDescription(){
        return $this->description;
    }

    public function setDescription($des){
        $this->description = $des;
    }
	
	public function getIdUser(){
        return $this->idUser;
    }
	
	public function getIdGroup(){
        return $this->idGroup;
    }
	
	public function getIsDinner(){
        return $this->isDinner;
    }

    public function setIsDinner($dinner){
        $this->isDinner = $dinner;
    }
	
	public function getDate(){
        return $this->date;
    }

    public function setDate($date){
        $this->date = $date;
    }

    public function getUsers(){
        return $this->users;
    }

    public function setUsers($users){
        $this->users = $users;
    }

    public function getnumberOfUsers(){
        return $this->numberOfUsers;
    }

    public function setnumberOfUsers($numberOfUsers){
        $this->numberOfUsers = $numberOfUsers;
    }

    public function getDeleted(){
        return $this->deleted;
    }

    public function CalculateGroupSaldo($gid){
        global $DBObject;
        // (cost) in costObject zit totaal bedrag/totaal aantal personen/persoon die betaald heeft/costId
        $costObject = $DBObject->GetCostObject($gid);
        // (User) UserId's en amount (array met gebruikers van groep)
        $userIdAmount = $DBObject->GetUsersIdsFromGroup($gid);
		
        if(isset($costObject)){
            foreach ($costObject as $o){
                // kijkt of bedrag hoger is dan 0
                if($o->amount > 0){
                    foreach($userIdAmount as $u){
                        // telt bedrag op bij betaler
                        if($u->id == $o->idUser){
                            $useramount = $u->amount + $o->amount;
                            $useramount = str_replace(',', '', $useramount);
                            $u->amount = $useramount;
                        }
                    }
                    // in user zit costId/iduser van betaler/voor hoeveel personen
                    // (cost) $o->id = costid (array met gebruiker in costID)
                    $users = $DBObject->GetCostUser($o->id);

                    if(isset($users)){
                        // haalt bedrag af bij iedereen die mee betaalt.
                        if($o->numberOfUsers != 0){
                            $amountPP = $o->amount / $o->numberOfUsers;
                            $amountPP = str_replace(',', '', $amountPP);
                            foreach($users as $u){
                                $amount = $u->numberOfUsers * $amountPP;
                                $amount = str_replace(',', '', $amount);
                                foreach($userIdAmount as $ua){
                                    if($ua->id == $u->idUser){
                                        $useramount = $ua->amount - $amount;
                                        $useramount = str_replace(',', '', $useramount);
                                        $useramount = number_format(($useramount),2);
                                        $useramount = str_replace(',', '', $useramount);
                                        $ua->amount = $useramount;
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    // voor import van bedragen als het bedrag negatief is.
                    foreach($userIdAmount as $u){
                        if($u->id == $o->idUser){
                            $useramount = $u->amount - abs($o->amount) ;
                            $useramount = str_replace(',', '', $useramount);
                            $u->amount = $useramount;
                        }
                    }

                    $users = $DBObject->GetCostUser($o->id);

                    if(isset($users)){
                        // telt bedrag bij iedereen die mee betaalt.
                        if($o->numberOfUsers != 0){
                            $amountPP = abs($o->amount) / $o->numberOfUsers;
                            $amountPP = str_replace(',', '', $amountPP);
                            foreach($users as $u){
                                $amount = $u->numberOfUsers * $amountPP;
                                $amount = str_replace(',', '', $amount);
                                foreach($userIdAmount as $ua){
                                    if($ua->id == $u->idUser){
                                        $useramount = $ua->amount + $amount;
                                        $useramount = str_replace(',', '', $useramount);
                                        $useramount = number_format(($useramount),2);
                                        $useramount = str_replace(',', '', $useramount);
                                        $ua->amount = $useramount;
                                    }
                                }
                            }
                        }
                    }

                }
            }
        }
		return $userIdAmount;
    }

    public function CalculateUserSaldo($gid,$uid){
        global $DBObject;
        // (cost) in costObject zit totaal bedrag/totaal aantal personen/persoon die betaald heeft/costId
        $costObject = $DBObject->GetCostObject($gid);
        // (User) UserId's en amount (array met gebruikers van groep)
        $userAmount = 0;
        if(isset($costObject)){
            foreach ($costObject as $o){
                // kijkt of bedrag hoger is dan 0
                if($o->amount > 0){
                        // telt bedrag op bij betaler
                        if($uid == $o->idUser){
                            $userAmount = $userAmount + $o->amount;
                            $userAmount = str_replace(',', '', $userAmount);
                        }

                        // haalt bedrag af bij iedereen die mee betaalt.
                        if($o->numberOfUsers != 0){
                            $amountPP = $o->amount / $o->numberOfUsers;
                            $amountPP = str_replace(',', '', $amountPP);
                            $number_of_persons = $DBObject->GetNumberOfGuestsUser($o->id,$uid);
                            $userAmount = $userAmount - ($amountPP * $number_of_persons);
                            $userAmount = str_replace(',', '', $userAmount);
                        }

                }
                else{
                    // voor import van bedragen als het bedrag negatief is.
                        if($uid == $o->idUser){
                            $userAmount = $userAmount - abs($o->amount) ;
                            $userAmount = str_replace(',', '', $userAmount);
                        }
                }
            }
            return number_format($userAmount,2);
        }
    }

    public function PayOff($gid){
        // $userAmount = (User) UserId's en amount (array met gebruikers van groep)
        $userAmount = $this->CalculatePayOffSaldo($gid);
        $checkout = array();
        $ready = false;

        if(isset($userAmount)){
            $whileCount = 0;
            while($ready == false){
                $max = 0;
                $maxId = 0;
                $min = 0;
                $minId = 0;
                $count = 0;
                foreach($userAmount as $obj)
                {
                    // var_dump($obj->amount,$obj->id);
                    if($obj->amount > $max)
                    {
                        $maxId = $obj->id;
                        $max = $obj->amount;
                    }
                    else if($obj->amount < $min)
                    {
                        $minId = $obj->id;
                        $min = $obj->amount;
                    }
                }
                $min = str_replace(',', '', $min);
                $max = str_replace(',', '', $max);
                $som = $max - abs($min);
                if($som < 0){
                    $count = $som;
                    $som = 0;
                }
                if ($som >= 0){
                    foreach($userAmount as $obj)
                    {
                        if($obj->id == $maxId)
                        {
                            $obj->amount = $som;
                        }
                        else if($obj->id == $minId)
                        {
                            if($count == 0){
                                $amount = str_replace(',', '', $obj->amount);
                                $amount = number_format((abs($amount)),2);
                                $output = new Checkout($gid,$obj->id,$maxId,$amount,0);
                                //$output = $this->withCostsUser($obj->id,$maxId, $amount);
                                array_push($checkout,$output);
                                $obj->amount = 0;
                            }
                            else{
                                $count = str_replace(',', '', $count);
                                $topay = $obj->amount + abs($count);
                                $topay = str_replace(',', '', $topay);
                                $amount = number_format((abs($topay)),2);
                                $output = new Checkout($gid,$obj->id,$maxId,$amount,0);
                                //$output = $this->withCostsUser($obj->id,$maxId, $amount);
                                array_push($checkout,$output);
                                $obj->amount = $count;
                            }
                        }
                    }
                }
                $arrCount = 0;
                $whileCount++;
                $error_string = '';
                foreach($userAmount as $obj)
                {
                    $error_string = $error_string.'id:'.$obj->id.'amount:'.$obj->amount.', ';
                    if ($obj->amount > -0.1 && $obj->amount < 0.1)
                    {
                        $arrCount = $arrCount + 1;
                    }
                }
                if($arrCount == Count($userAmount)){
                    $ready = true;
                }
                if($whileCount == 500){
                    Email_Handler::mailFeedback('info@monipal.com', 'Dit een automatisch bericht! WARNING: Failed to created checkout tabel groupId: '.$gid.' Please check database!!!! user amounts over:'.$error_string.' ', '/cost.class.php PayOff($gid)');
                    return null;
                }
            }
            // checkout array is betalerID > ontvangerID > bedrag
            return $checkout;
        }
    }

    public function CalculatePayOffSaldo($gid){
        global $DBObject;
        // (cost) in costObject zit totaal bedrag/totaal aantal personen/persoon die betaald heeft/costId
        $costObject = $DBObject->GetCostObject($gid);
        // (User) UserId's en amount (array met gebruikers van groep)
        $userIdAmount = $DBObject->GetUsersIdsFromGroupForCheckout($gid);
        if(isset($costObject)){
            foreach ($costObject as $o){
                // kijkt of bedrag hoger is dan 0
                if($o->amount > 0){
                    foreach($userIdAmount as $u){
                        // telt bedrag op bij betaler
                        if($u->id == $o->idUser){
                            $useramount = $u->amount + $o->amount;
                            $useramount = str_replace(',', '', $useramount);
                            $u->amount = $useramount;
                        }
                    }
                    // in user zit costId/iduser van betaler/voor hoeveel personen
                    // (cost) $o->id = costid (array met gebruiker in costID)
                    $user = $DBObject->GetCostUser($o->id);

                    if(isset($user)){
                        // haalt bedrag af bij iedereen die mee betaalt.
                        if($o->numberOfUsers != 0){
                            $amountPP = $o->amount / $o->numberOfUsers;
                            $amountPP = str_replace(',', '', $amountPP);
                            foreach($user as $u){
                                $amount = $u->numberOfUsers * $amountPP;
                                $amount = str_replace(',', '', $amount);
                                foreach($userIdAmount as $ua){
                                    if($ua->id == $u->idUser){
                                        $useramount = $ua->amount - $amount;
                                        $useramount = str_replace(',', '', $useramount);
                                        $ua->amount = $useramount;
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    // voor import van bedragen als het bedrag negatief is.
                    foreach($userIdAmount as $u){
                        if($u->id == $o->idUser){
                            $useramount = $u->amount - abs($o->amount) ;
                            $useramount = str_replace(',', '', $useramount);
                            $u->amount = $useramount;
                        }
                    }

                    $user = $DBObject->GetCostUser($o->id);

                    if(isset($user)){
                        // telt bedrag bij iedereen die mee betaalt.
                        if($o->numberOfUsers != 0){
                            $amountPP = abs($o->amount) / $o->numberOfUsers;
                            $amountPP = str_replace(',', '', $amountPP);
                            foreach($user as $u){
                                $amount = $u->numberOfUsers * $amountPP;
                                $amount = str_replace(',', '', $amount);
                                foreach($userIdAmount as $ua){
                                    if($ua->id == $u->idUser){
                                        $useramount = $ua->amount + $amount;
                                        $useramount = str_replace(',', '', $useramount);
                                        $ua->amount = $useramount;
                                    }
                                }
                            }
                        }
                    }

                }
            }
        return $userIdAmount;
    }
    }

    public function ShowUserSaldos($group,$uid){
        global $DBObject, $lang, $settings;
        $userDate = null;
        $users = $group->getUsers();
        $userSaldo = $DBObject->GetGroupUserSaldo($group->id);
        echo '
               <ul>';
        if(isset($userSaldo)){
            foreach($users as $u){
                $date = $DBObject->CheckCostDinner($u->id,$group->id);
                $name = $DBObject->GetUserNameById($u->id);
                echo '<li'.($uid == $u->id ? ' class="selectedUser"' : '').'><a href="'.$settings['site_url'].'cost/'.$group->id.'/'.$u->id.'">'.(strlen($name) > 14 ? substr($name,0,12)
                        .'...' : $name).' '.((isset($date)) ? '('.COUNT($date).')' : ' ') . ($userSaldo[$u->id][1] >=  0 ? '<span class="txtPositive">' : '<span class="txtNegative">').$group->currency.' '.$userSaldo[$u->id][1].'</span></a></li>';
            }
        } else {
            foreach($users as $u){
                $date = $DBObject->CheckCostDinner($u->id,$group->id);
                echo '<li><a href="'.$settings['site_url'].'cost/'.$group->id.'/'.$u->id.'/">'.$DBObject->GetUserNameById($u->id) .' '.((isset($date)) ? '('.COUNT($date).') ' : ' ');
                echo '<span class="txtPositive">'. $group->currency .' 0</span></a></li>';
            }
        }
        echo '</ul>';
    }

    public function ShowUnpayedDinners($group,$uid,$errorData){
        global $lang, $settings,$DBObject;
        $userDate = $DBObject->CheckCostDinner($uid,$group->id);
        $users = $group->getUsers();
        if($userDate != null){
            echo '<h2 class="unpayed-dinners">'.$lang['DINNER_COST_STILL_NEEDED'].'</h2>
        <label>'.$lang['NO_DINNER_COST'].'</label>
        <form method="post" action="">';
            $calculatorCount = 0;
            foreach ($userDate as $ud){
                $explodeDate = explode("-",$ud[0]);
                $idusers = $DBObject->GetAllGuestsFromDinner($group->id,$ud[0]);
                $userstring = '';
                $count = 0;
                foreach ($idusers as $user){
                    $userstring = $userstring.(COUNT($idusers) == 1 ? '' : ($count > 0 && $count < COUNT($idusers) -1 ? ', ' : '')
                            .($count == COUNT($idusers) -1 ? ' '.$lang['AND'].' ' : '')) . $user->nameUser.
                        ($user->numberOfPersons == 1 ? '' : ' x'.$user->numberOfPersons);
                    $count++;
                }
                echo '<div class="dinner-cost'.(isset($errorData[$ud[0]]) ? ' dinner-cost-error': '').'">
                    <div>
						'.$explodeDate[2]."-".$explodeDate[1]."-".$explodeDate[0].' '.$lang['COOKED_FOR'].' '
                    .$userstring.' <a href="'.$settings['site_url'].'edit-dinner.php?gid='.$group->id.'&uid='.$uid.'&date='.$ud[0].'">['.strtolower($lang['EDIT']).']</a>
						<a href="'.$settings['site_url'].'cost/'.$group->id.'/'.$uid.'/'.$explodeDate[2]."-".$explodeDate[1]."-".$explodeDate[0].'" title="'.$lang['IGNOTE_DINNER_COST'].'" class="close-button">x</a>
					</div>
                    <span class="clear">
                        <label>'.$lang['DESCRIPTION'].': </label>
                        <input type="text" name="description[]" class="description-dinner-cost" value="'.($ud[1] != null ? $ud[1] : '').'">
                    </span>
                    <span>
                        <label>'.$lang['AMOUNT'].': </label>
                        <span>'.$group->currency.'</span> <input type="text" name="amount[]" id="basicCalculator'.$calculatorCount.'" class="amount" value="">
                    </span>
                    <span>
                        <label>'.$lang['PAID_BY'].': </label>
                        <select name="uid[]">';
                foreach ($users as $u){
                    echo '<option value="'.$u->id.'" '. ($u->id == $uid ? "SELECTED" : '') .'>'.$DBObject->GetUserNameById($u->id) .'</option>';
                }
                echo '</select></span><input type="hidden" name="date[]" value="'.$ud[0].'" /></div>';
                $calculatorCount++;
            }
            echo '<span class="clear"><input type="submit" value="'.$lang['SAVE'].'"></span></form>';
        }
    }

    public function ShowLastCostTable($group){
        global $DBObject;
        global $lang;
        global $settings;
        echo '<h2 class="last-updates clear">'.$lang['LAST_UPDATES'].'</h2>';
        $costs = $DBObject->GetLastCost($group->id);
        if(isset($costs)){
            echo '<table border="1">
        <tr><th>'.$lang['PAID_BY'].'</th><th>'.$lang['DESCRIPTION'].'</th><th>'.$lang['AMOUNT'].'</th><th>'.$lang['DATE'].'</th><th>'.$lang['WHO_SHARE_COST'].'</th><th></th></tr>';
            $w =0;
            foreach( $costs as $c ){
                ++$w;
                $explode = explode("-",$c->date);
                $CDate = $explode[2]."-".$explode[1]."-".$explode[0];
                echo '<tr '.($w % 2 ? '' : 'class="alt"').'><td>'.($c->deleted == '1' ? '<del>'.$DBObject->GetUserNameById($c->idUser).'</del>' :
                        $DBObject->GetUserNameById($c->idUser)).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$c->description.'</del>' : $c->description).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$group->currency.' '.$c->amount.'</del>' : $group->currency.' '.$c->amount).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$CDate.'</del>' : $CDate).'</td>';
                $ids = $c->getUsers();
                if(isset($ids)){
                    echo '<td>'.($c->deleted == '1' ? '<del>': '');
                    $count = 0;
                    foreach($ids as $u){
                        echo (COUNT($ids) == 1 ? '' : ($count > 0 && $count < COUNT($ids) -1 ? ', ' : '').($count == COUNT($ids) -1 ? ' '.$lang['AND'].' ' : '')).
                            $DBObject->GetUserNameById($u->idUser).($u->numberOfPersons > 1 ? ' '.$u->numberOfPersons.'x' : '');
                        ++$count;
                    }
                    echo ($c->deleted == '1' ? '</del></td>': '</td>');
                }
                else{
                    echo '<td></td>';
                }
				if($c->isDinner != 2){
                    echo ($c->deleted == '1' ? '<td></td></tr>': '</td><td style="white-space: nowrap;"><a href="'.$settings['site_url'].'edit-cost/'.$group->id
                        .'/'.$c->id.'/" class="icon-pencil icon-link" title="'.$lang['EDIT'].'"></a><label>/</label><a href="'.$settings['site_url'].'delete-cost.php?gid='.$group->id.'&cid='.$c->id.'"
                        onClick="return confirm(\''.$lang['ARE_YOU_SURE_DELETE_COST'].'\')" class="icon-trash-empty icon-link" title="'.$lang['DELETE'].'"></a></td></tr>');
                }
                else{
					echo '<td></td></tr>';
                    //echo '<td>'.$lang['IS_IMPORTED'].'</td></tr>';
                }
            }
            echo "</table>";
        }
        else{
            echo $lang['NO_OPEN_COST'];
        }
    }

    public function AddCosts($date, $amount, $description, $userId,$group,$uid){
        global $DBObject;
        $count = 0;
        if(isset($date)){
            foreach ($date as $d){
                if($amount[$count] != null && $description[$count] != null && $userId[$count] != null){
                    $a = str_replace(',', '.', $amount[$count]);
                    if(is_numeric($a)){
                        if($a > 0){
                            if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $d)){
                                list($yyyy,$mm,$dd) = explode('-',$d);
                                if (checkdate($mm,$dd,$yyyy)) {
                                    if($group->checkUser($userId[$count])){
                                        $idcost = $DBObject->AddCost($a,0,$description[$count],$userId[$count],$group->id,1,$d);
                                        $DBObject->AddDinnerCost($group->id,$d,$idcost);
                                        // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                                        $lb = new Logbook(null, $group->id, $uid, null, $idcost, null, null, 'CA', null);
                                        $DBObject->AddLogbookItem($lb);
                                    }
                                }
                            }
                        }
                    }
                }
                $count = $count + 1;
            }
            return true;
        }
        else{
            return false;
        }
    }

    public function Addcost($amount,$description,$uid, $group,$number, $date){
        global $DBObject;
        $amount = str_replace(',', '.', $amount);
        $cid = $DBObject->AddCost($amount,0,$description,$uid,$group->id,$number,$date);
        return $cid;
    }

    public function AddUserCost($u,$cid,$guests){
        global $DBObject;
        $DBObject->AddUserCost($u->id,$cid,$guests);
    }

    public function ShowCheckOutTable($group,$gid){
        global $DBObject;
        global $lang;
        echo '<h2>'.$lang['CHECKOUT'].'</h2>
        <p>'.$lang['CHECKOUT_INFORMATION'].'</p>';
        if(COUNT($group->getUsers()) == 1){
            echo '<div class="notification-bar">'.$lang['CHECKOUT_WARNING_ALONE_IN_GROUP'].'</div>';
        }
        else{
            $userIdAmount = $this->CalculateGroupSaldo($group->id);
            if(isset($userIdAmount)){
                $count = 0;
                foreach($userIdAmount as $us){
                    if($us->amount == 0){
                        $count++;
                    }
                }

                if(COUNT($userIdAmount) != $count){
                    $ToPay = $this->PayOff($gid);
                    if($ToPay != null){
                        echo '<form action="" method="post" onsubmit="return confirm(\''.$lang['ARE_YOU_SURE_CHECKOUT'].'\')">
                    <input type="submit" class="secundaire-btn" value="'.$lang['PAYOFF'].'"></form><br />';
                        $userIdAmount = $this->CalculateGroupSaldo($group->id);
                        echo '<table border=1><tr><th>'.$lang['GROUP_USER'].'</th><th>'.$lang['TO_PAY_TO_RECIEVE'].'</th></tr>';
                        // user and amount
                        foreach($userIdAmount as $u){
                            // gets bankaccount (was only able to set in first week) gevoelige info kunnen we niet in db houden
                            $bank = $DBObject->GetBankAccountById($u->id);
                            // set current amount
                            echo '<tr><td>'.$DBObject->GetUserNameById($u->id) .'<br />'.($u->amount >=  0 ? '<span class="txtPositive">' :
                                    '<span class="txtNegative">').$group->currency.' '.$u->amount.'</span><br /><i> '.($bank != null ? $lang['BANKNUMMER'].'<br />'.$bank : '').
                                '</i></td><td>';
                            // set what to pay or recieve
                            foreach($ToPay as $p){
                                // user id == id_user_payer
                                if($u->id == $p->idpayer){
                                    // p->numberofUsers == amount to pay . $p->iduser is reciever
                                    echo $lang['TO_PAY'].$group->currency.' '.$p->amount.' '.$lang['TO'].' '.$DBObject->GetUserNameById($p->idreceiver).'<br>';
                                }
                                // user id == id_user_reciever
                                if($u->id == $p->idreceiver){
                                    // p->numberofUsers == amount to receive . $p->id is payer
                                    echo $lang['TO_RECEIVE'].$group->currency.' '.$p->amount.' '.$lang['FROM'].' '.$DBObject->GetUserNameById($p->idpayer).'<br>';
                                }
                            }
                            echo '</td></tr>';
                        }
                        echo "</table>";
                    }
                    else{
                       // var_dump($ToPay);
                        echo '<div class="error-bar">'.$lang['CHECKOUT_FAILED_TABLE'].'</div>';
                        //Email_Handler::mailFeedback("bug@monipal.com", "WARNING!! CHECKOUT FAILED GROUP: ".$group->id, "/checkout");
                    }
                }
                else{
                    echo '<div class="notification-bar">'.$lang['NOTHING_TO_CHECKOUT'].'</div>';
                }

            }
            else{
                echo '<div class="notification-bar">'.$lang['NOTHING_TO_CHECKOUT'].'</div>';
            }
        }
    }

    public function ShowOldCheckOutTable($group,$ToPay){
        global $DBObject;
        global $lang;
        //$userIdAmount = $this->CalculateGroupSaldo($group->id);
        echo '<table border=1><tr><th>'.$lang['GROUP_USER'].'</th><th>'.$lang['TO_PAY_TO_RECIEVE'].'</th></tr>';
        // user and amount
        foreach($group->getUsers() as $u){
            $userAmount = 0;
            foreach($ToPay as $p){
                // user id == id_user_payer
                if($u->id == $p->idpayer){
                    $userAmount = $userAmount - $p->amount;
                }
                // user id == id_user_reciever
                if($u->id == $p->idreceiver){
                    $userAmount = $userAmount + $p->amount;
                }
            }
            // set current amount
            echo '<tr><td>'.$DBObject->GetUserNameById($u->id) .'<br />'.($userAmount >=  0 ? '<span class="txtPositive">' :
                    '<span class="txtNegative">').$group->currency.' '.$userAmount.'</span><br /></td><td>';
            // set what to pay or recieve
            foreach($ToPay as $p){
                // user id == id_user_payer
                if($u->id == $p->idpayer){
                    // p->numberofUsers == amount to pay . $p->iduser is reciever
                    echo $lang['TO_PAY'].$group->currency.' '.$p->amount.' '.$lang['TO'].' '.$DBObject->GetUserNameById($p->idreceiver).'<br>';
                }
                // user id == id_user_reciever
                if($u->id == $p->idreceiver){
                    // p->numberofUsers == amount to receive . $p->id is payer
                    echo $lang['TO_RECEIVE'].$group->currency.' '.$p->amount.' '.$lang['FROM'].' '.$DBObject->GetUserNameById($p->idpayer).'<br>';
                }
            }
            echo '</td></tr>';
        }
        echo "</table>";
    }

    public function PayOffDB($gid){
        global $DBObject;
        return $DBObject->PayOff($gid);
    }

    public function DeleteCostById($cid){
        global $DBObject;
        return $DBObject->DeleteCostById($cid);
    }

    public function GetCostById($cid){
        global $DBObject;
        return $DBObject->GetCostById($cid);
    }

    public function EditCostData($cost){
        global $DBObject;
        $DBObject->EditCostData($cost);
    }

    public function ShowCostTable($group,$costs){
        global $DBObject, $lang, $settings;
        if(isset($costs) && COUNT($costs) > 0){
            echo '<table border="1">
            <tr><th>'.$lang['PAID_BY'].'</th><th>'.$lang['DESCRIPTION'].'</th><th>'.$lang['AMOUNT'].'</th><th>'.$lang['DATE'].'</th><th>'.$lang['IS_DINNER'].'</th><th>'.
                $lang['WHO_SHARE_COST'].'</th></tr>';
            $w =0;
            foreach( $costs as $c ){
                ++$w;
                $explode = explode("-",$c->date);
                $CDate = $explode[2]."-".$explode[1]."-".$explode[0];
                echo '<tr '.($w % 2 ? '' : 'class="alt"').'><td>'.($c->deleted == '1' ? '<del>'.$DBObject->GetUserNameById($c->idUser).'</del>' : $DBObject->GetUserNameById($c->idUser)).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$c->description.'</del>' : $c->description).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$group->currency.' '.$c->amount.'</del>' : $group->currency.' '.$c->amount).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.$CDate.'</del>' : $CDate).'</td>
                <td>'.($c->deleted == '1' ? '<del>'.($c->isDinner == 0 ? $lang['NO'] : $lang['YES']).'</del>' : ($c->isDinner != 1 ? $lang['NO'] : $lang['YES'])).'</td>';
                $ids = $c->users;
                if(isset($ids)){
                    echo '<td>'.($c->deleted == '1' ? '<del>': '');
                    $count = 0;
                    foreach($ids as $u){
                     echo (COUNT($ids) == 1 ? '' : ($count > 0 && $count < COUNT($ids) -1 ? ', ' : '').($count == COUNT($ids) -1 ? ' '.$lang['AND'].' ' : '')).
                         $DBObject->GetUserNameById($u->idUser).($u->numberOfPersons > 1 ? ' '.$u->numberOfPersons.'x' : '');
                        ++$count;
                }
                echo ($c->deleted == '1' ? '</del></td>': '');
                }
                else{
                    echo '<td></td>';
                }
                if($c->isDinner != 2){
                     echo ($c->deleted == '1' ? '<td></td></tr>': '</td><td style="white-space: nowrap;"><a href="'.$settings['site_url'].'edit-cost/'.$group->id
                        .'/'.$c->id.'/" class="icon-pencil icon-link" title="'.$lang['EDIT'].'"></a><label>/</label><a href="'.$settings['site_url'].'delete-cost.php?gid='.$group->id.'&cid='.$c->id.'"
                        onClick="return confirm(\''.$lang['ARE_YOU_SURE_DELETE_COST'].'\')" class="icon-trash-empty icon-link" title="'.$lang['DELETE'].'"></a></td></tr>');
                }
                else{
					echo '<td></td></tr>';
                    //echo '<td>'.$lang['IS_IMPORTED'].'</td></tr>';
                }
            }
            echo "</table>";
        }
        else{
            echo '<div class="notification-bar">'.$lang['NO_COST_FOUND'].'</div>';
        }
    }

    public function ShowCostHistory($user,$group,$range){
        global $DBObject;
        global $lang;
        echo '<h1>'.$lang['COSTMANAGEMENT_HISTORY'].'</h1>';
            $costs = $DBObject->GetCostHistory($user->id,$group->id,$range);
            $this->ShowCostTable($group,$costs);
    }

    public function GetCostGuests($cid,$uid){
        global $DBObject;
        return $DBObject->GetCostGuests($cid,$uid);
    }

    public function UpdateCostGuests($uid,$guests,$cid){
        global $DBObject;
        $DBObject->UpdateCostGuests($uid,$guests,$cid);
    }

    public function ShowLastGroupCostTable($group,$range){
        global $DBObject;
        global $lang;
        echo '<h1>'.$lang['COSTMANAGEMENT_HISTORY'].'</h1>';
            $costs = $DBObject->GetLastGroupCost($group->id,$range);
            $this->ShowCostTable($group,$costs);
    }
}
?>