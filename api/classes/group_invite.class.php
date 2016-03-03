<?php

class GroupInvite {
    public $Id;
    public $Name;

    function __construct($Id, $Name)
    {
        $this->Id = $Id;
        $this->Name = $Name;
    }

}