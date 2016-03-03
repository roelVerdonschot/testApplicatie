<?php
class User_Saldo
{
	function __construct($idUser,$idGroup,$saldo)
	{
	$this->id = $idUser;
	$this->idGroup = $idGroup;
	$this->amount = $saldo;
	}
    // property declaration
	public $id;
	public $idGroup;
	public $amount;
	
	public function getId(){
        return $this->id;
    }
	
	public function getIdGroup(){
        return $this->idGroup;
    }
	
	public function getAmount(){
        return $this->amount;
    }

    public function setAmount($amount){
        $this->amount = $amount;
    }
}
?>