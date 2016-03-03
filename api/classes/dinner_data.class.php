<?php

class DinnerData {

    public $IdUsers;
    public $IdGroup;
    public $IdRole;
    public $Date;
    public $Description;
    public $NumberOfPersons;
    public $UserName;

    function __construct($idGroup, $idUsers, $idRole, $date, $description, $guests,$name)
    {
        $this->IdGroup = $idGroup;
        $this->IdUsers = $idUsers;
        $this->IdRole = $idRole;
        $this->Date = $date;
        $this->Description = $description;
        $this->NumberOfPersons = $guests;
        $this->UserName = $name;
    }
}
