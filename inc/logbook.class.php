<?php
class Logbook
{
	function __construct($idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
	{
        $this->idLogbook = $idLogbook;
        $this->idGroup = $idGroup;
        $this->idUser = $idUser;
        $this->idTask = $idTask;
        $this->idCost = $idCost;
        $this->dateCreated = $dateCreated;
        $this->dateOfDinner = $dateOfDinner;
        $this->code = $code;
        $this->value = $value;
	}
    // property declaration
    public $idLogbook;
	public $value;
    public $code;
	public $idUser;
	public $idGroup;
	public $dateOfDinner;
	public $idCost;
	public $idTask;
    public $dateCreated;

    public function getIdLogbook(){
        return $this->idLogbook;
    }

    public function getCode(){
        return $this->code;
    }

    public function getDateCreated(){
        return $this->dateCreated;
    }

	public function getValue(){
        return $this->value;
    }
	
	public function getIdUser(){
        return $this->idUser;
    }
	
	public function getIdGroup(){
        return $this->idGroup;
    }
	
	public function getDateOfDinner(){
        return $this->dateOfDinner;
    }
	
	public function getIdCost(){
        return $this->idCost;
    }
	
	public function getIdTask(){
        return $this->idTask;
    }
    public function save()
    {
        global $DBObject;
        return $DBObject->AddLogbookItem($this);
    }

    public function LogBookTable($logbookItems,$group,$count,$cat){
        global $DBObject, $lang,$settings;
        if($logbookItems != null)
        {
            echo '
            <table class="index-table">
                <tr><th>'.$lang['LOGBOOK_DATE'].'</th><th>'.$lang['WHO'].'</th><th>'.$lang['LOG'].'</th></tr>';
            $i = 0;
            $w =0;
            foreach ($logbookItems as $l) {
                ++$w;
                $user = $DBObject->GetUserById($l->idUser);
                echo '<tr '.($w % 2 ? '' : 'class="alt"').'><td>'.$l->dateCreated.'</td><td>'.$user->firstName.'</td><td>';
                $explode = explode("|",$l->value);
                switch($l->code)
                {
                    case 'TA': // Taak aanmaken + taak naam
                        echo $lang['LB_TA'].$l->value;
                        break;
                    case 'TD': // Taak verwijdere + taak naam
                        echo $lang['LB_TD'].$l->value;
                        break;
                    case 'TE': // Taak wijzigen + oude taak naam
                        $t = $DBObject->GetTaskByID($l->idTask);
                        echo $lang['LB_TE_1'].$l->value.$lang['LB_TE_2'].$t->name;
                        break;
                    case 'TDN': // Taak voltooid zetten + naam persoon + week
                        $date = explode("-",$explode[1]);
                        echo $lang['LB_TDN'].current($explode).$lang['LB_DATE'].$date[2].'-'.$date[1].'-'.$date[0];
                        break;
                    case 'TND': // Taak onvoltooid zetten + naam persoon + week
                        $date = explode("-",$explode[1]);
                        echo $lang['LB_IND'].current($explode).$lang['LB_DATE'].$date[2].'-'.$date[1].'-'.$date[0];
                        break;
                    case 'EC': // Chef zetten + naam + aantal extra + datum
                        $date = explode("-",$explode[2]);
                        echo $explode[0].$lang['LB_EC'].$date[2].'-'.$date[1].'-'.$date[0]. ($explode[1] > 1 ? $lang['LB_WITH'].($explode[1] - 1).$lang['LB_EXTRA_PPL'] : ' ');
                        break;
                    case 'EW': // meeeten + naam + aantal extra + datum
                        $date = explode("-",$explode[2]);
                        echo $explode[0].$lang['LB_EW'].$date[2].'-'.$date[1].'-'.$date[0]. ($explode[1] > 1 ? $lang['LB_WITH'].($explode[1] - 1).$lang['LB_EXTRA_PPL'] : ' ');
                        break;
                    case 'ENW': // Niet meeeten + naam + datum
                        $date = explode("-",$explode[1]);
                        echo $explode[0].$lang['LB_ENW'].$date[2].'-'.$date[1].'-'.$date[0];
                        break;
                    case 'ENS': // Meeeten op niet ingesteld zetten + naam + datum
                        $date = explode("-",$explode[1]);
                        echo $explode[0].$lang['LB_ENS'].$date[2].'-'.$date[1].'-'.$date[0];
                        break;
                    case 'GNE': // GroepsNaam wijzigen + oude naam
                        echo $lang['LB_GNE'].$l->value;
                        break;
                    case 'GUA': // User toevoegen + Naam persoon
                        echo $lang['LB_GUA'].$l->value;
                        break;
                    case 'GUD': // User verwijderen + Naam persoon
                        echo $lang['LB_GUD'].$l->value;
                        break;
                    case 'CA': // Nieuwe kosten + costID
                        $cost = $DBObject->GetCostByIdLogBook($l->idCost);
                        echo ($cost->isDinner == 0 ? $lang['LB_CA'] : $lang['LB_CA_1']).$cost->description.' '.$group->currency.' '.$cost->amount;
                        break;
                    case 'CE': // Kosten gewijzigd + costID
                        $cost = $DBObject->GetCostByIdLogBook($l->idCost);
                        echo $lang['LB_CE_1'].$cost->description.$lang['LB_CE_2'].$group->currency.' '.$l->value.$lang['LB_CE_3'].$group->currency.' '.$cost->amount;
                        break;
                    case 'CD': // Kosten verwijderen + costID
                        $cost = $DBObject->GetCostByIdLogBook($l->idCost);
                        echo $lang['LB_CD'].$cost->description.' '.$group->currency.' '.$cost->amount;
                        break;
                    case 'UA': // User accept invite
                        echo $user->firstName.$lang['LB_UA'];
                        break;
                    case 'PO': // afrekenen
                        echo $lang['LB_PO'];
                        break;
                    case 'GME': // groeps modules zijn gewijzigt
                        echo $lang['LB_GME'];
                        break;
                }

                echo '</td></tr>';
                $i++;
            }

            echo '</table>';
            if($cat != 'gtb'){
                if($count > 50){
                    $j = 1;
                    for($i = 1 ; $i < $count ; $i = $i + 50){
                        echo (isset($_GET['range']) && $_GET['range'] == $j ? '['.$j.'] ' : '<a href="'.$settings['site_url'].'logbook/'.$group->id.'/'.$cat.'/'.$j.'">'.$j.'</a> '); // class="btn btn-white"
                        $j++;
                    }
                }
            }
        }
        else
        {
            echo '<p>'.$lang['NOT_FOUND_LOGBOOK_ITEMS'].'</p>';
        }
    }
}
?>