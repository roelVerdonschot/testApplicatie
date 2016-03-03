<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ilya
 * Date: 15-10-13
 * Time: 13:59
 * To change this template use File | Settings | File Templates.
 */

class WeekListData {
    function __construct()
    {
        $this->TaskList = array();
    }

    public function setNameOfWeek($NameOfWeek)
    {
        $this->NameOfWeek = $NameOfWeek;
    }

    public function getNameOfWeek()
    {
        return $this->NameOfWeek;
    }

    public function setDateOfWeek($DateOfWeek)
    {
        $this->DateOfWeek = $DateOfWeek;
    }

    public function getDateOfWeek()
    {
        return $this->DateOfWeek;
    }

    public function setTaskList($TaskList)
    {
        $this->TaskList = $TaskList;
    }

    public function getTaskList()
    {
        return $this->TaskList;
    }

    public $NameOfWeek;
    public $TaskList;
    public $DateOfWeek;


}