<?php
class CostData
{
    function __construct($id,$amount,$isPaid,$description,$iduser,$nameUser,$idgroup,$isDinner,$date,$users,$numberOfUsers,$deleted)
    {
        $this->id = $id;
        $this->amount = $amount;
        $this->isPaid = $isPaid;
        $this->description = $description;
        $this->idUser = $iduser;
        $this->nameUser = $nameUser;
        $this->idGroup = $idgroup;
        $this->isDinner = $isDinner;
        $this->date = $date;
        $this->users = $users;
        $this->numberOfUsers = $numberOfUsers;
        $this->deleted = $deleted;
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
}
?>