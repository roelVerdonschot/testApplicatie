<?php
class Checkout
{
	function __construct($id_group, $id_user_payer, $id_user_recevier, $amount, $idCheck)
	{
		$this->idgroup = $id_group;
		$this->idCheck = $idCheck;
		$this->idpayer = $id_user_payer;
		$this->idreceiver = $id_user_recevier;
		$this->amount = $amount;
	}
    // property declaration
	public $idgroup;
    public $idcheck;
	public $idpayer;
	public $idreceiver;
	public $amount;
}