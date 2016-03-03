<?php

class GroupData {
    public $Id;
    public $Name;
    //public $Location;
    //public $Picture;
    public $Users; //array with users [id, name, balance, preferredLanguage]
    public $Currency;
    public $Modules;
    public $CurrentUser;
	
    public $Costs;
    public $UnpayedDinners;
    public $Tasks;
    public $UserTasks;
    public $Dinners;
    public $DinnerStatistics;
    public $DinnerClosingTime;
    public $StickyNotes;

    function __construct($Id, $Name, $Users, $Currency, $CurrentUser, $Modules)
    {
        $this->Currency = $Currency;
        $this->CurrentUser = $CurrentUser;
        $this->Id = $Id;
        $this->Modules = $Modules;
        $this->Name = $Name;
        $this->Users = $Users;
    }

}