<?php

class TaskData{

    public $TaskName;
    public $TaskId;
    public $TaskDescription;
    public $UserName;
    public $UserId;
    public $IsComplete;

    public function setIsComplete($IsComplete)
    {
        $this->IsComplete = $IsComplete;
    }

    public function getIsComplete()
    {
        return $this->IsComplete;
    }

    public function setTaskId($TaskId)
    {
        $this->TaskId = $TaskId;
    }

    public function getTaskId()
    {
        return $this->TaskId;
    }

    public function setTaskName($TaskName)
    {
        $this->TaskName = $TaskName;
    }

    public function getTaskName()
    {
        return $this->TaskName;
    }

    public function setUserId($UserId)
    {
        $this->UserId = $UserId;
    }

    public function getUserId()
    {
        return $this->UserId;
    }

    public function setUserName($UserName)
    {
        $this->UserName = $UserName;
    }

    public function getUserName()
    {
        return $this->UserName;
    }
}