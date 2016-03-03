<?php
class Task
{
	function __construct($id, $name, $description,$groupId)
	{
        $this->id = $id;
		$this->name = $name;
		$this->description = $description;
        $this->groupId = $groupId;
	}
    // field declaration
    public $id;
	public $name;
	public $description;
    public $groupId;
	// methods
	
	public function getName(){
        return $this->name;
    }
    public function setName($name){
        $this->name= $name;
    }
	
	public function getDescription(){
        return $this->description;
    }
	
	public function setDescription($description){
        $this->description= $description;
    }
		
	public function getId(){
        return $this->id;
    }
	
	public function setId($id){
        $this->id= $id;
    }

    public function getGroupId(){
        return $this->groupId;
    }

    public function setGroupId($id){
        $this->groupId = $id;
    }

    public function deleteTask()
    {
        global $DBObject;
        return $DBObject->DeleteTaskById($this->id);
    }
	   
}
?>