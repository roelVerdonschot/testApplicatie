<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Arwin
 * Date: 5-10-13
 * Time: 15:15
 * To change this template use File | Settings | File Templates.
 */

class DinnerStatisticData {
    // property declaration
    public $idUser;
    public $role;
    public $count;

    function __construct($idUser, $role, $count)
    {
        $this->count = $count;
        $this->role = $role;
        $this->idUser = $idUser;
    }


    public function getCount()
    {
        return $this->count;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getIdUser()
    {
        return $this->idUser;
    }

}