<?php
class CheckoutData
{
    function __construct($UserName,$Saldo,$Debt,$BankAccount)
    {
        $this->Username = $UserName;
        $this->Saldo = $Saldo;
        $this->Debt = $Debt;
        $this->BankAccount = $BankAccount;
    }

    // property declaration
    public $Username;
    public $Saldo;
    public $Debt;
    public $BankAccount;
}

class CheckOutDebt
{
    function __construct($UserName,$Amount,$IsDebt)
    {
        $this->Username = $UserName;
        $this->Amount = $Amount;
        $this->IsDebt = $IsDebt;
    }

    // property declaration
    public $Username;
    public $IsDebt;
    public $Amount;
}
?>