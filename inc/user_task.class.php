<?php
class User_Task
{
	function __construct($idUser, $idTask, $date, $isDone)
	{
		$this->idUser = $idUser;
		$this->idTask = $idTask;
		$this->date = $date;
        $this->isDone = $isDone;
	}
    // field declaration
    public $idUser;
	public $idTask;
	public $date;
	public $isDone;
	// methods
	
	public function setIsDone(){
        $this->isDone=true;
    }
	
	public function getIdUser(){
        return $this->idUser;
    }
	public function getIdTask(){
        return $this->idTask;
    }
    public function getIsDone(){
        return $this->isDone;
    }
	public function getDate(){
        return $this->date;
    }
}
?>