<?php
class DinnerStats
{
    function __construct($name,$cooked,$dinnersJoined,$ratio,$AVGCost,$points,$AVGEaters)
    {
        $this->Name = $name;
        $this->Cooked = $cooked;
        $this->DinnersJoined = $dinnersJoined;
        $this->Ratio = $ratio;
        $this->AVGCost = $AVGCost;
        $this->Points = $points;
        $this->AVGEaters = $AVGEaters;
    }

    // property declaration
    public $Name;
    public $Cooked;
    public $DinnersJoined;
    public $Ratio;
    public $AVGCost;
    public $Points;
    public $AVGEaters;
}
?>