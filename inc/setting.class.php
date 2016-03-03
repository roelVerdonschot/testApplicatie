<?php
class Setting
{
    public $id;
    public $key;
    public $value;
    public $idUser;
    public $idGroup;

    function __construct()
    {
    }

    public static function forGroupUser($idSetting, $key, $value, $idGroup, $idUser)
    {
        $instance = new setting();
        $instance->loadForGroupUser($idSetting, $key, $value, $idGroup, $idUser);
        return $instance;
    }

    // field declaration

    protected function loadForGroupUser($idSetting, $key, $value, $idGroup, $idUser)
    {
        $this->id = $idSetting;
        $this->key = $key;
        $this->value = $value;
        $this->idGroup = $idGroup;
        $this->idUser = $idUser;
    }

    public static function forUser($key, $value, $idUser)
    {
        $instance = new setting();
        $instance->loadForUser($key, $value, $idUser);
        return $instance;
    }

    protected function loadForUser($key, $value, $idUser)
    {
        $this->key = $key;
        $this->value = $value;
        $this->idUser = $idUser;
    }

    public static function forGroup($key, $value, $idGroup)
    {
        $instance = new setting();
        $instance->loadForGroup($key, $value, $idGroup);
        return $instance;
    }

    protected function loadForGroup($key, $value, $idGroup)
    {
        $this->key = $key;
        $this->value = $value;
        $this->idGroup = $idGroup;
    }

    // methods

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getIdUser()
    {
        return $this->idUser;
    }

    public function setIdUser($iduser){
        $this->idUser = $iduser;
    }

    public function getIdGroup()
    {
        return $this->idGroup;
    }

    public function setIdGroup($idGroup)
    {
        $this->idGroup = $idGroup;
    }

    public function getKey()
    {
        return $this->key;
    }

    public
    function setKey($key)
    {
        $this->key = $key;
    }

    public
    function getValue()
    {
        return $this->value;
    }

    public
    function setValue($value)
    {
        $this->value = $value;
    }


}

?>