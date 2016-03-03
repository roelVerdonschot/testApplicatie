<?php
/**
 * Created by JetBrains PhpStorm.
 * User: roel
 * Date: 21-11-13
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */

class User_Cost {

    function __construct($uid,$nameUser,$nop)
    {
        $this->idUser = $uid;
        $this->nameUser = $nameUser;
        $this->numberOfPersons = $nop;
    }
    public $idUser;
    public $nameUser;
    public $numberOfPersons;
}