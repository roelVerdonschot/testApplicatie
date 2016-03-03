<?php
class Sticky_Note
{
	function __construct($id,$title,$message,$date,$idUser,$idGroup)
	{
        $this->id = $id;
        $this->title = $title;
        $this->message = $message;
        $this->idUser = $idUser;
        $this->idGroup = $idGroup;
        $this->date = $date;
	}
    // property declaration
    public $id;
	public $title;
	public $idUser;
	public $message;
	public $idGroup;
	public $date;

    public function getId(){
        return $this->id;
    }

    public function getTitle(){
        return $this->title;
    }

    public function getMessage(){
        return $this->message;
    }
	
	public function getIdUser(){
        return $this->idUser;
    }
	
	public function getIdGroup(){
        return $this->idGroup;
    }
	
	public function getDate(){
        return $this->date;
    }

    public function save()
    {
        global $DBObject;
        $DBObject->AddStickyNote($this);
    }


}
?>