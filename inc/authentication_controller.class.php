<?php

$DBObject = DBHandler::GetInstance($db);

class Authentication_Controller {

    public static function Login($emailaddress, $password) {
        global $DBObject;
        $emailaddress = htmlspecialchars($emailaddress);
        $loginUserId = $DBObject->GetUserIdByLogin($emailaddress, $password);
        if ($loginUserId == 'notActivated') {
            return 'notActivated';
        }
        if ($loginUserId != null) {
            $hash_key = uniqid(mt_rand(), true);
            $hash = hash('sha512', $loginUserId . $_SERVER['HTTP_USER_AGENT'] . $hash_key);

            if ($DBObject->SetLoginSession($DBObject->CheckDBValue($loginUserId), $hash, $hash_key, $DBObject->CheckDBValue($_SERVER['REMOTE_ADDR']))) {
                $_SESSION['user_id'] = $loginUserId;
                $_SESSION['user_hash'] = $hash;
                return true;
            }
        }
        return false;
    }

    public static function LogOut() {
        global $DBObject;
        $DBObject->LogoutUser($_SESSION['user_id'], $_SERVER['REMOTE_ADDR']);

        // Unset all session values
        $_SESSION = array();
        // get session parameters
        $params = session_get_cookie_params();
        // Delete the actual cookie.
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        // Destroy session
        session_destroy();
    }

    private static $_authenticatedUser;

    public static function GetAuthenticatedUser() {
        global $DBObject;
        // Kijken of er cookies zijn gezet, en controleren of ze valid zijn
        if (!isset($_SESSION['user_id']) || !is_int($_SESSION['user_id']) || !isset($_SESSION['user_hash']) || !ctype_alnum($_SESSION['user_hash'])) {
            return false;
        }
        // Controleren of er niet al eerder deze functie is opgevraagd op dezelfde pagina
        if (!empty(self::$_authenticatedUser)) {
            return self::$_authenticatedUser;
        }

        // Sessie ophalen uit database
        if (($userSession = $DBObject->GetLoginSession($_SESSION['user_id'], $_SESSION['user_hash'], $_SERVER['REMOTE_ADDR'])) == null) {
            return false;
        }

        // Controleren of de sessie bestaat
        if (!is_int($userSession['iduser'])) {
            return false;
        }

        // Controleren of de hash ook klopt (browsercheck)
        if (hash('sha512', $_SESSION['user_id'] . $_SERVER['HTTP_USER_AGENT'] . $userSession['hash_key']) != $_SESSION['user_hash']) {
            $DBObject->LogoutUser($_SESSION['user_id'], $_SERVER['REMOTE_ADDR']); // Browser is veranderd
            return false;
        } else {
            self::$_authenticatedUser = $DBObject->GetUserById($userSession['iduser']);
            return self::$_authenticatedUser;
        }
    }

    public static function IsAuthenticated() {
        return Authentication_Controller::GetAuthenticatedUser() !== false;
    }

    public static function IsTemporarilyBanned() {
        global $DBObject, $settings;

        if ($attempts = $DBObject->GetLoginAttempts($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']) != 0) {
            if ($attempts > $settings['max_login_attempts']) { // Controleren of je bent geband
                return true;
            }
        }
        return false;
    }

}

?>