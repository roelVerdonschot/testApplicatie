<?php
class Cost_Subgroup
{
	function __construct($id,$idgroup, $name)
	{
		$this->id = $id;
		$this->name = $name;
		$this->idgroup = $idgroup;
	}
    // property declaration
	public $id;
	public $idgroup;
    public $name;
	public $userIds;
}