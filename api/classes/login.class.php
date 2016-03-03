<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Arwin
 * Date: 3-10-13
 * Time: 15:07
 * To change this template use File | Settings | File Templates.
 */

class Login {
    public $Username;
    public $Password;
    public $TimeStamp;

    public function __construct($username,$pass,$time)
    {
        $this->Username = $username;
        $this->Password = $pass;
        $this->TimeStamp = $time;
    }

    public function authenticate()
    {
        global $DBObject;
        if($this->authenticateTimeStamp() === false)
        {
            return new ErrorData(WRONG_TIMESTAMP); // tijd klopt niet
        }
        $loginUserId = $DBObject->GetUserIdByLogin(htmlspecialchars($this->Username), $this->Password);
        if($loginUserId == 'notActivated')
        {
            return new ErrorData(USER_NOT_ACTIVATED); // account nog niet geactiveerd
        }
        if ($loginUserId != null)
        {
            return $DBObject->GetUserById($loginUserId);
        }
        return new ErrorData(LOGIN_FAILED); // account niet gevonden, gebruikersnaam of wachtwoord verkeerd
    }

    private function authenticateTimeStamp()
    {
        $currentTimestamp = time();
        $maxTimestamp = $currentTimestamp + 1200; //20 minuten
        $minTimestamp = $currentTimestamp - 1200; //20 minuten

        //var_dump($this->TimeStamp,$maxTimestamp,$minTimestamp);

        if($this->TimeStamp <= $maxTimestamp && $this->TimeStamp >= $minTimestamp)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}