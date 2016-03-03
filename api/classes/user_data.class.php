<?php

class UserData {
    public $Id;
    public $Name;
    public $Balance;
    public $PreferredLanguage;

    function __construct($Id, $Name, $Balance, $PreferredLanguage)
    {
        $this->Balance = $Balance;
        $this->Id = $Id;
        $this->Name = $Name;
        $this->PreferredLanguage = $PreferredLanguage;
    }
}