<?php

/**
 * http://mattbango.com/notebook/web-development/prepared-statements-in-php-and-mysqli/
 * http://jream.com/blog/simple-mysqli-class
 * http://www.daniweb.com/web-development/php/code/289096/simple-mysql-database-class
 */
class DBHandler {

    protected $this, $result;

    public function __construct($config) {
        $this->mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);
        $this->mysqli->set_charset("utf8");
        if (mysqli_connect_errno()) {
            error_log("Connect failed: %s\n" + mysqli_connect_error());
            //printf("Connect failed: %s\n", mysqli_connect_error());
            //exit();
        }
    }

    /**
     * Retrieve a Singleton Instance of this class
     */
    public static function GetInstance($config) {
        static $Instances = array();
        if ($config != null) {
            $key = implode(":", $config);

            if (!isset($Instances[$key])) {
                $Instances[$key] = new DBHandler($config);
            }

            return $Instances[$key];
        } else {
            return end($Instances);
        }
    }

    public function CheckDBValue($value) {
        return $this->mysqli->real_escape_string($value);
    }

    ///// Custom functions
    /*     * ******************** BEGIN OF LogIn ********************* */

    public function GetUserById($id) {
        global $settings;
        $query = "	SELECT `iduser`,  `firstname`,  `surname`,  `zipcode`,  `address`,  `city`,  `country`,  `date_of_birth`,  `bankaccount`,  `preferred_language`,  `profile_picture`,  `school`,  `email`, `date_created`, `terms_accepted` FROM " . $settings['db_user_table'] . " WHERE iduser = ? AND is_deleted = 0";
        $output = null;
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param('s', $id);
            if (!$stmt->execute()) {
                error_log('DB ERROR: GetUserById ' . $stmt->error);
            } else {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($id, $firstName, $surname, $zipcode, $address, $city, $country, $dateOfBirth, $bankAccount, $preferredLanguage, $profilePicture, $school, $email, $datecreated,$terms_accepted);
                    $stmt->fetch();

                    $output = User::withUser($id, $firstName, $surname, $email, $zipcode, $address, $city, $country, $dateOfBirth, $bankAccount, $preferredLanguage, $profilePicture, $school, $datecreated,$terms_accepted);
                }
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserById ' . $stmt->error);
            $stmt->close();
        }
        $stmt->close();
        return $output;
    }

    public function GetUserIdByLogin($emailaddress, $password) {
        global $settings;
        //check if the user has activated
        $stmt = $this->mysqli->prepare('SELECT activation FROM ' . $settings['db_user_table'] . ' WHERE activation IS NOT NULL AND email = ? AND is_deleted = 0');
        $stmt->bind_param('s', $emailaddress);

        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserIdByLogin ' . $stmt->error);
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                return 'notActivated';
            }
        }
        $stmt->close();
        unset($stmt);

        // gets the hashed password from the database
        $stmt = $this->mysqli->prepare('SELECT password FROM ' . $settings['db_user_table'] . ' WHERE email = ? AND is_deleted = 0');
        $stmt->bind_param('s', $emailaddress);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserIdByLogin ' . $stmt->error);
        } else {
            $hashedpassword = null;

            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($passwordSQL);
                $stmt->fetch();
                $hashedpassword = $passwordSQL;

                $stmt->close();
                unset($stmt);
                if (bcrypt::check($password, $hashedpassword)) {
                    $query = "SELECT iduser FROM " . $settings['db_user_table'] . " WHERE email = ? AND is_deleted = 0";
                    $stmt = $this->mysqli->prepare($query);
                    $stmt->bind_param('s', $emailaddress);
                    if (!$stmt->execute()) {
                        error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserIdByLogin ' . $stmt->error);
                    } else {
                        $stmt->store_result();

                        if ($stmt->num_rows > 0) {
                            $stmt->bind_result($userIdSQL);
                            $stmt->fetch();
                            $userId = $userIdSQL;
                            if (is_int($userId)) {
                                $stmt->close();
                                return $userId;
                            }
                        }
                    }
                }
            }
        }


        //Dit uitvoeren als het niet goed ging
        $sql = "INSERT INTO " . $settings['db_login_attempt_table'] . " (date_time,	ip, sys_info)
								VALUES (
									NOW(),
									?,
									?
								)";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param('ss', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserIdByLogin ' . $stmt->error);
        }
        $stmt->close();

        return null;
    }

    public function GetLoginSession($userid, $hash, $ip) {
        global $settings;
        $query = "	SELECT
							iduser, hash_key
						FROM
							" . $settings['db_session_table'] . "
						WHERE
							iduser = ?
						AND
							hash = ?
						AND
							ip = ?";

        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('sss', $userid, $hash, $ip);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetLoginSession ' . $stmt->error);
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($iduser, $hash_key);
                $stmt->fetch();
                $stmt->close();
                return array("iduser" => $iduser, "hash_key" => $hash_key);
            }
        }
        $stmt->close();
        return null;
    }

    public function GetLoginAttempts($ip, $useragent) {
        global $settings;
        $query = "	SELECT
					COUNT(id) AS attempts
				FROM
					" . $settings['db_login_attempt_table'] . "
				WHERE
					date_time > (NOW() - INTERVAL 15 MINUTE)
				AND
					ip = ?
				AND
					sys_info = ?";

        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ss', $ip, $useragent);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetLoginAttempts ' . $stmt->error);
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($attempts);
                $stmt->fetch();
                $stmt->close();
                return $attempts;
            }
            $stmt->close();
            return 0;
        }
    }

    public function SetLoginSession($userID, $hash, $hash_key, $ip) {
        global $settings;
        $sql = "INSERT INTO " . $settings['db_session_table'] . " (iduser,hash,hash_key,date_created,ip)
				VALUES (
					?,
					?,
					?,
					NOW(),
					?
				)";
        // Query uitvoeren
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param('ssss', $userID, $hash, $hash_key, $ip);

        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: SetLoginSession ' . $stmt->error);
            $stmt->close();
            return false;
        } else {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return true;
            }
        }
    }

    public function LogoutUser($userId, $ip) {
        global $settings;
        $query = "	UPDATE " . $settings['db_session_table'] . " SET
						hash = NULL,
						hash_key = NULL,
						ip = ?
					WHERE iduser = ?";

        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ss', $ip, $userId);

        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: LogoutUser ' . $stmt->error);
            $stmt->close();
            return false;
        } else {
            $stmt->close();
            return true;
        }
    }

    public function GetUserIdByEmail($emailaddress) {
        global $settings;
        $query = "SELECT iduser FROM " . $settings['db_user_table'] . " WHERE email = ? AND is_deleted = 0";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $emailaddress);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserIdByEmail ' . $stmt->error);
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($userIdSQL);
                $stmt->fetch();
                $userId = $userIdSQL;
                $stmt->close();
                return $userId;
            } else {
                $stmt->close();
                return null;
            }
        }
    }

    /*     * ******************** END OF LogIn ********************* */

    public function CheckEmail($email) {
        global $settings;
        $stmt = $this->mysqli->prepare('Select email FROM ' . $settings['db_user_table'] . ' WHERE email = ? AND is_deleted = 0');
        $stmt->bind_param('s', $email);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CheckEmail ' . $stmt->error);
            $stmt->close();
            return false;
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
        return false;
    }

    /*     * ******************** BEGIN OF Account Change ********************* */

    public function ChangePassword($pass, $code) {
        global $settings;
        $pass = Bcrypt::hash($pass);
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_table'] . ' SET password = ?, resetcode = null where resetcode = ? AND is_deleted = 0');
        $stmt->bind_param('ss', $pass, $code);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: ChangePassword ' . $stmt->error);
            $stmt->close();
        }
    }

    public function CheckPassword($uid, $password) {
        global $settings;
        // gets the hashed password from the database
        $stmt = $this->mysqli->prepare('SELECT password FROM ' . $settings['db_user_table'] . ' WHERE iduser = ? AND is_deleted = 0');
        $stmt->bind_param('s', $uid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CheckPassword ' . $stmt->error);
        } else {
            $hashedpassword = null;

            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($passwordSQL);
                $stmt->fetch();
                $hashedpassword = $passwordSQL;

                $stmt->close();
                unset($stmt);
                if (bcrypt::check($password, $hashedpassword)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public function CheckResetCode($code) {
        global $settings;
        $stmt = $this->mysqli->prepare('Select iduser from ' . $settings['db_user_table'] . ' where resetcode = ? AND is_deleted = 0');
        $stmt->bind_param('s', $code);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CheckResetCode ' . $stmt->error);
            $stmt->close();
            return false;
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->close();
                return true;
            } else {
                $stmt->close();
                return false;
            }
        }
    }

    public function ResetPassword($email) {
        global $settings, $lang;

        $stmt = $this->mysqli->prepare('Select firstname, surname from ' . $settings['db_user_table'] . ' WHERE email = ? AND is_deleted = 0');
        $stmt->bind_param('s', $email);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: ResetPassword ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($firstname, $surname);
                $stmt->fetch();
                $name = $firstname . ' ' . $surname;
                $code = sha1($email . time());
                $stmt->close();

                $stmt = $this->mysqli->prepare('Update ' . $settings['db_user_table'] . '  set resetcode = ? where email = ? AND is_deleted = 0');
                $stmt->bind_param('ss', $code, $email);
                $stmt->execute();

                Email_Handler::mailBodyResetPass($email, 'Monipal ' . $lang['SUBJECT_PASSWORD_RESET'], $name, $code);
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
        return false;
    }

    public function EditUserData($user) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_table'] . ' SET firstname = ?, surname =?, zipcode=?, address=?, city=?, country=?,
        date_of_birth=?, bankaccount=?, preferred_language=?, school=? where iduser=?');
        $stmt->bind_param('ssssssssssi', $user->firstName, $user->surname, $user->zipcode, $user->address, $user->city, $user->country, $user->dateOfBirth, $user->bankAccount, $user->preferredLanguage, $user->school, $user->id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: EditUserData ' . $stmt->error);
            $stmt->close();
        }
    }

    public function EditEmail($email, $id) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_table'] . ' SET email=? WHERE iduser = ? AND is_deleted = 0');
        $stmt->bind_param('si', $email, $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: EditEmail ' . $stmt->error);
        } else {
            $stmt->close();
            $stmt = $this->mysqli->prepare('SELECT firstname FROM ' . $settings['db_user_table'] . ' WHERE iduser = ? AND is_deleted = 0');
            $stmt->bind_param('s', $id);
            if (!$stmt->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: EditEmail ' . $stmt->error);
                $stmt->close();
            } else {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($name);
                    $stmt->fetch();
                    $stmt->close();
                }

                Email_Handler::mailEmailChanged($email, $name);
            }
        }
    }

    public function EditPassword($oldpass, $newpass, $id) {

        global $settings;
        $stmt = $this->mysqli->prepare('SELECT password from ' . $settings['db_user_table'] . ' WHERE iduser=? AND is_deleted = 0');
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: EditPassword ' . $stmt->error);
            $stmt->close();
            return false;
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($pass);
                $stmt->fetch();
            }

            $stmt->close();

            if (Bcrypt::check($oldpass, $pass)) {

                $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_table'] . ' set password = ? where iduser = ? AND is_deleted = 0');
                $stmt->bind_param('si', Bcrypt::hash($newpass), $id);
                if (!$stmt->execute()) {
                    error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: EditPassword ' . $stmt->error);
                    $stmt->close();
                    return false;
                } else {
                    $stmt->close();
                    return true;
                }
            } else {
                $stmt->close();
                return false;
            }
        }
    }

    public function UpdatePassword($newpass, $id) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_table'] . ' set password = ? where iduser = ? AND is_deleted = 0');
        $stmt->bind_param('si', Bcrypt::hash($newpass), $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdatePassword ' . $stmt->error);
            $stmt->close();
            return false;
        } else {
            $stmt->close();
            return true;
        }
    }

    /*     * ******************** END OF Account Change ********************* */


    /*     * ******************** BEGIN OF New User ********************* */

    public function GetUserByAC($code) {
        global $settings;
		
        $query = "	SELECT `iduser`,  `firstname`,  `surname`,  `zipcode`,  `address`,  `city`,  `country`,  `date_of_birth`,  `bankaccount`,  `preferred_language`,  `profile_picture`,  `school`,  `email`, `date_created`, `terms_accepted` FROM " . $settings['db_user_table'] . " WHERE activation = ? AND is_deleted = 0";
        $output = null;
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param('s', $code);
            if (!$stmt->execute()) {
                error_log('DB ERROR: GetUserByAC ' . $stmt->error);
            } else {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($id, $firstName, $surname, $zipcode, $address, $city, $country, $dateOfBirth, $bankAccount, $preferredLanguage, $profilePicture, $school, $email, $datecreated,$terms_accepted);
                    $stmt->fetch();

                    $output = User::withUser($id, $firstName, $surname, $email, $zipcode, $address, $city, $country, $dateOfBirth, $bankAccount, $preferredLanguage, $profilePicture, $school, $datecreated,$terms_accepted);
                }
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserByAC ' . $stmt->error);
            $stmt->close();
        }
        $stmt->close();
        return $output;
    }

    public function ActivateUser($code) {
        global $settings;

        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_table'] . ' SET activation = NULL WHERE activation = ? AND is_deleted = 0');
        $stmt->bind_param('s', $code);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: ActivateUser ' . $stmt->error);
            $stmt->close();
        } else {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
        return false;
    }

    public function AddUser($name, $emailaddress, $password, $activation, $langCode, $terms_accepted) {
        global $settings;
		if($terms_accepted == true)
		{
			$terms_accepted = "NOW()";
		}
		else
		{
			$terms_accepted = "NULL";
		}
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_user_table'] . ' (firstname,preferred_language,email,password,activation,terms_accepted)VALUES(?,?,?,?,?,'.$terms_accepted.')');
        $stmt->bind_param('sssss', $name, $langCode, $emailaddress, $password, $activation);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddUser ' . $stmt->error);
            $stmt->close();
        } else {
            $userId = $stmt->insert_id;
            $stmt->close();
            return $userId;
        }
    }

    /*     * ******************** END OF New User ********************* */

    /*     * ******************** BEGIN OF User ********************* */

    public function ChangePreferredLanguage($lang, $userId) {
        global $settings;

        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_table'] . ' SET preferred_language = ? WHERE iduser = ? AND is_deleted = 0');
        $stmt->bind_param('si', $lang, $userId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: ChangePreferredLanguage ' . $stmt->error);
            $stmt->close();
        } else {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
        return false;
    }

    public function GetUserNameById($id) {
        global $settings;
        $query = "SELECT firstname FROM " . $settings['db_user_table'] . " WHERE iduser = ? AND is_deleted = 0"; //,surname
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserNameById ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($firstname); //, $surname);
                $stmt->fetch();
                $stmt->Close();
                if (isset($surname)) {
                    $output = $firstname; // .' '. $surname;
                } else {
                    $output = $firstname;
                }

                return $output;
            }
        }
    }
	
	 public function IsTermsAcceptedByActivation($activationcode) {
        global $settings;
        $query = "SELECT terms_accepted FROM " . $settings['db_user_table'] . " WHERE activation = ? AND is_deleted = 0"; //,surname
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $activationcode);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserNameById ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($terms_accepted); //, $surname);
                $stmt->fetch();
                $stmt->Close();
                return $terms_accepted;
            }
        }
    }
	
	public function UpdateTermsAcceptedByUserId($userId) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_table'] . ' SET terms_accepted = NOW() WHERE iduser = ? AND is_deleted = 0');
        $stmt->bind_param('s', $userId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateTermsAcceptedByUserId ' . $stmt->error);
        }
		$stmt->close();
		return true;	
       
    }
	
    public function GetBankAccountById($id) {
        global $settings;
        $query = "SELECT bankaccount FROM " . $settings['db_user_table'] . " WHERE iduser = ? AND is_deleted = 0"; //,surname
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetBankAccountById ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($firstname); //, $surname);
                $stmt->fetch();
                $stmt->Close();
                if (isset($surname)) {
                    $output = $firstname; // .' '. $surname;
                } else {
                    $output = $firstname;
                }

                return $output;
            } else {
                $stmt->Close();
                return null;
            }
        }
    }

    public function GetEmailByuserID($uid) {
        global $settings;
        $query = "SELECT email FROM " . $settings['db_user_table'] . " WHERE iduser = ? AND is_deleted = 0";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $uid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetEmailByUserId ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($email);
                $stmt->fetch();
                $stmt->close();
                return $email;
            } else {
                $stmt->close();
                return null;
            }
        }
    }

    public function DeleteUserAccount($deletedEmail, $uid) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_table'] . ' SET email = ?, password = "deleted", is_deleted = 1 WHERE iduser = ?');
        $stmt->bind_param('ss', $deletedEmail, $uid);

        $stmt2 = $this->mysqli->prepare('DELETE FROM ' . $settings['db_user_group_device_table'] . ' WHERE iduser = ?');
        $stmt2->bind_param('s', $uid);

        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeleteUserAccount ' . $stmt->error);
            $stmt->close();
        } else {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                $stmt2->execute();
                $stmt2->close();
                return true;
            }
            $stmt->close();
            $stmt2->close();
            return false;
        }
    }

    /*     * ******************** END OF User ********************* */

    /*     * ******************** BEGIN OF New Group/Operations group ********************* */

    public function AddGroup($name, $user, $userId, $users) {
        global $settings, $lang;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_group_table'] . ' (name)VALUES( ? )');
        $stmt->bind_param('s', $name);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $groupId = $stmt->insert_id;
            $stmt->close();

            $this->AddUserToGroup($userId, $groupId);

            foreach ($users as $v) {
                if (!empty($v)) {
                    $this->InviteUserToGroup($v, $groupId, $userId);
                    Email_Handler::mailBodyInviteToGroup($v, 'Monipal ' . $lang['SUBJECT_GROUPINVITE'], $user, $name, $groupId);
                    // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                    $lb = new Logbook(null, $groupId, $userId, null, null, null, null, 'GUA', $v);
                    $this->AddLogbookItem($lb);
                }
            }
        }
    }

    public function AddGroupNoUsers($name) {
        global $settings, $lang;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_group_table'] . ' (name)VALUES( ? )');
        $stmt->bind_param('s', $name);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddGroupNoUsers ' . $stmt->error);
            $stmt->close();
        } else {
            $groupId = $stmt->insert_id;
            $stmt->close();
            return $groupId;
        }
    }

    public function AddUserToGroup($userId, $groupId) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT * FROM ' . $settings['db_user_group_table'] . ' WHERE iduser = ? and idgroup = ?');
        $stmt->bind_param('ss', $userId, $groupId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddUserToGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows == 0) {
                $stmt->close();

                $stmt1 = $this->mysqli->prepare('INSERT INTO ' . $settings['db_user_group_table'] . ' (iduser, idgroup) VALUES( ?, ? )');
                $stmt1->bind_param('ss', $userId, $groupId);
                if (!$stmt1->execute()) {
                    error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddUserToGroup ' . $stmt1->error);
                } else {
                    if ($stmt1->affected_rows > 0) {
                        $stmt1->close();
                        return true;
                    }
                }
                $stmt1->close();
                return false;
            } else {
                $stmt2 = $this->mysqli->prepare('UPDATE ' . $settings['db_user_group_table'] . ' SET is_deleted = 0 WHERE iduser = ? AND idgroup = ?');
                $stmt2->bind_param('ss', $userId, $groupId);
                if (!$stmt2->execute()) {
                    error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddUserToGroup ' . $stmt->error);
                    $stmt2->close();
                } else {
                    if ($stmt2->affected_rows > 0) {
                        $stmt2->close();
                        return true;
                    }
                }
                $stmt2->close();
                return false;
            }
        }
        $stmt->close();
    }

    public function DeleteUserFromGroup($userId, $groupId) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_group_table'] . ' SET is_deleted = 1 WHERE iduser = ? AND idgroup = ?');
        $stmt->bind_param('ss', $userId, $groupId);

        $stmt2 = $this->mysqli->prepare('DELETE FROM ' . $settings['db_user_group_device_table'] . ' WHERE iduser = ?');
        $stmt2->bind_param('s', $uid);

        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeleteUserFromGroup ' . $stmt->error);
            $stmt->close();
        } else {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                $stmt2->execute();
                $stmt2->close();
                return true;
            }
        }
        $stmt->close();
        $stmt2->close();
        return false;
    }

    public function InviteUserToGroup($email, $idgroup, $iduser) {
        global $settings;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_invite_table'] . ' (email,idgroup,iduser)VALUES( ?, ?, ? )');
        $stmt->bind_param('sss', $email, $idgroup, $iduser);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: InviteUserToGroup ' . $stmt->error);
            $stmt->close();
        }
        $stmt->close();
    }

    public function CheckInvitedUserGroup($email, $idgroup) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT idinvite FROM ' . $settings['db_invite_table'] . ' WHERE email = ? AND idgroup = ?');
        $stmt->bind_param('si', $email, $idgroup);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CheckInvitedUserGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
        return false;
    }

    public function CheckInvitedUser($email) {
        global $settings;
        $idgroups = array();
        $stmt = $this->mysqli->prepare('SELECT idgroup FROM ' . $settings['db_invite_table'] . ' WHERE email = ?');
        $stmt->bind_param('s', $email);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CheckInvitedUser ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($groupId);
                while ($stmt->fetch()) {
                    $output = $groupId;
                    array_push($idgroups, $output);
                }
                $stmt->Close();
                return $idgroups;
            }
        }
        $stmt->close();
        return null;
    }

    public function DeleteUserFromInvite($email, $groupId, $userId) {
        global $settings;
        $stmt = $this->mysqli->prepare('DELETE FROM ' . $settings['db_invite_table'] . ' WHERE email = ? AND idgroup = ?');
        $stmt->bind_param('ss', $email, $groupId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeleteUserFromInvite ' . $stmt->error);
            $stmt->close();
        } else {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                $this->AddUserToGroup($userId, $groupId);
                return true;
            }
        }
        $stmt->close();
        return false;
    }

    public function DeleteInviteById($iid, $email) {
        global $settings;
        $stmt = $this->mysqli->prepare('DELETE FROM ' . $settings['db_invite_table'] . ' WHERE idinvite = ? AND email = ?');
        $stmt->bind_param('ss', $iid, $email);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeleteInviteById ' . $stmt->error);
            $stmt->close();
        }
    }

    public function DeleteInviteByGid($gid, $email) {
        global $settings;
        $stmt = $this->mysqli->prepare('DELETE FROM ' . $settings['db_invite_table'] . ' WHERE idgroup = ? AND email = ?');
        $stmt->bind_param('ss', $gid, $email);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeleteInviteByGid ' . $stmt->error);
            $stmt->close();
        }
    }

    public function DeleteUserFromInviteById($iid, $userId, $groupId) {
        global $settings;
        $stmt = $this->mysqli->prepare('DELETE FROM ' . $settings['db_invite_table'] . ' WHERE idinvite = ?');
        $stmt->bind_param('s', $iid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeleteUserFromInviteById ' . $stmt->error);
            $stmt->close();
        } else {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                $this->AddUserToGroup($userId, $groupId);
                return true;
            }
        }
        $stmt->close();
        return false;
    }

    public function EditGroupData($group) {
        global $settings;
        $stmt = $this->mysqli->prepare('Update ' . $settings['db_group_table'] . ' set name = ?, location = ? , currency = ?, modules = ? Where idgroup = ? ');
        $stmt->bind_param('sssss', $group->name, $group->location, $group->currency, $group->modules, $group->id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: EditGroupData ' . $stmt->error);
        }
        $stmt->Close();
    }

    /*     * ******************** END OF New Group/Operations group ********************* */

    /*     * ******************** BEGIN OF Group ********************* */

    public function GetMyGroups($userId) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT idgroup,name,currency,modules FROM ' . $settings['db_group_table'] . ' WHERE idgroup IN (SELECT idgroup FROM ' . $settings['db_user_group_table'] . ' WHERE iduser = ? AND is_deleted = 0)');
        $stmt->bind_param('s', $userId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetMyGroups ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $group = array();
                $stmt->bind_result($groupId, $name, $currency, $modules);
                while ($stmt->fetch()) {
                    if ($modules == null) {
                        $output = Group::withNameId($groupId, $name, $currency);
                        array_push($group, $output);
                    } else {
                        $output = Group::withNameIdMod($groupId, $name, $currency, $modules);
                        array_push($group, $output);
                    }
                }
                $stmt->Close();
                return $group;
            }
        }
    }

    public function GetGroupById($id) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT idgroup,name,location,picture,date_created,currency,modules FROM ' . $settings['db_group_table'] . ' WHERE idgroup = ?');
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetGroupById ' . $stmt->error);
            $stmt->Close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idgroup, $name, $location, $picture, $datecreated, $currency, $modules);
                $stmt->fetch();
                $output = Group::withGroupNoUsers($idgroup, $name, $location, $picture, $datecreated, $currency, $modules);
                $stmt->Close();
                return $output;
            }
        }
    }

    public function GetGroupName($id) {
        global $settings;
        $stmt = $this->mysqli->prepare('Select name FROM ' . $settings['db_group_table'] . ' WHERE idgroup = ?');
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetGroupName ' . $stmt->error);
            $stmt->Close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($name);
                $stmt->fetch();
                $stmt->Close();
                return $name;
            }
        }
    }

    public function AuthenticationGroup($userId, $groupId) {
        global $settings;
        $stmt = $this->mysqli->prepare('Select * From ' . $settings['db_user_group_table'] . ' Where iduser = ? and idgroup = ? AND is_deleted != 1');
        $stmt->bind_param('ss', $userId, $groupId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AuthenticationGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
        return false;
    }

    public function GetUsersFromGroup($id) {
        global $settings;
        $query = "SELECT iduser,firstname,surname FROM " . $settings['db_user_table'] . " WHERE iduser IN (select iduser from " . $settings['db_user_group_table'] . " WHERE idgroup = ? AND is_deleted != 1)";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersFromGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $group = array();
                $stmt->bind_result($userId, $firstname, $surname);
                while ($stmt->fetch()) {
                    if ($surname == null) {
                        $output = User::withNameId($userId, $firstname);
                        array_push($group, $output);
                    } else {
                        $output = User::withFullNameId($userId, $firstname, $surname);
                        array_push($group, $output);
                    }
                }
                $stmt->Close();
                return $group;
            }
        }
    }

    public function GetEmailSettingsByUserGroup($groupId) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT idsetting, `key`, value, idgroup, iduser FROM ' . $settings['db_setting_table'] . ' WHERE `key` = \'etr\' AND idgroup = ?');
        $stmt->bind_param('s', $groupId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetEmailSettingsByUserGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idSetting, $key, $value, $idGroup, $idUser);
                while ($stmt->fetch()) {
                    $output[$idUser] = Setting::forGroupUser($idSetting, $key, $value, $idGroup, $idUser);
                }
                $stmt->close();
                return $output;
            }
            $stmt->close();
            return array();
        }
    }

    public function GetUsersForEmailTaskReminder() {
        global $settings;
        $query = 'SELECT u.iduser, u.firstname, u.surname, u.preferred_language, u.email, s.idgroup FROM ' . $settings['db_setting_table'] . ' as s
        JOIN ' . $settings['db_user_table'] . ' as u ON s.iduser = u.iduser
        WHERE s.key = \'etr\' AND s.value = \'1\'  AND u.is_deleted = 0';
        //var_dump($query);
        $output = array();
        if ($stmt = $this->mysqli->prepare($query)) {
            if (!$stmt->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersForEmailTaskReminder ' . $stmt->error);
                $stmt->close();
            } else {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($idUser, $firstName, $surname, $preferredLanguage, $emai, $idGroup);
                    while ($stmt->fetch()) {
                        $output[] = array(User::forEmail($idUser, $firstName, $surname, $preferredLanguage, $emai), $idGroup);
                    }
                }
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersForEmailTaskReminder ' . $stmt->error);
            $stmt->close();
        }
        $stmt->close();
        return $output;
    }

    public function SetEmailTaskReminderSetting($setting) {
        global $settings;
        $stmt = $this->mysqli->prepare("INSERT INTO " . $settings['db_setting_table'] . " VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE value = 'delete';");
        $stmt->bind_param('sssss', $setting->id, $setting->key, $setting->value, $setting->idGroup, $setting->idUser);
        $stmt2 = $this->mysqli->prepare("DELETE FROM " . $settings['db_setting_table'] . " WHERE value LIKE '%delete%'");

        if ($stmt->execute()) {
            if (!$stmt2->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: SetEmailTaskReminderSetting ' . $stmt2->error);
            } else {
                $stmt2->close();
                $stmt->close();
                return true;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: SetEmailTaskReminderSettings ' . $stmt->error);
        }
        $stmt2->close();
        $stmt->close();
        return false;
    }

    public function GetUsersIdsFromGroup($id) {
        global $settings;
        $query = "SELECT iduser FROM " . $settings['db_user_group_table'] . " WHERE idgroup = ? AND is_deleted != 1";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersIdsFromGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $group = array();
                $stmt->bind_result($userId);
                while ($stmt->fetch()) {
                    $output = new User_Saldo($userId, $id, '0');
                    array_push($group, $output);
                }
                $stmt->Close();
                return $group;
            }
        }
    }

    public function GetUsersIdsFromGroupForCheckout($id) {
        global $settings;
        $query = "SELECT iduser FROM " . $settings['db_user_group_table'] . " WHERE idgroup = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersIdsFromGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $group = array();
                $stmt->bind_result($userId);
                while ($stmt->fetch()) {
                    $output = new User_Saldo($userId, $id, '0');
                    array_push($group, $output);
                }
                $stmt->Close();
                return $group;
            }
        }
    }

    public function GetNumberOfUsersInGroup($gid) {
        global $settings;
        $query = "SELECT idgroup FROM " . $settings['db_user_group_table'] . " WHERE idgroup = ? AND is_deleted != 1";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $gid);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $rows = $stmt->num_rows;
                $stmt->close();
                return $rows;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetNumberOfUsersInGroup ' . $stmt->error);
            $stmt->close();
        }
        $stmt->close();
        return 0;
    }

    public function GetNumberOfUsersInvited($gid) {
        global $settings;
        $query = "SELECT idgroup FROM " . $settings['db_invite_table'] . " WHERE idgroup = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $gid);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $rows = $stmt->num_rows;
                $stmt->close();
                return $rows;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetNumberOfUsersInvited ' . $stmt->error);
        }
        $stmt->close();
        return 0;
    }

    public function GetInvitedUsers($gid) {
        global $settings;
        $query = "SELECT email, DATE_FORMAT(date, '%d-%m-%Y') as date FROM " . $settings['db_invite_table'] . " WHERE idgroup = ? ORDER BY date DESC";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $gid);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($email, $date);
                while ($stmt->fetch()) {
                    $emails[] = $email;
                }
                $stmt->Close();
                return $emails;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetInvitedUsers ' . $stmt->error);
        }
        $stmt->close();
        return null;
    }

    public function UpdateSetting($setting) {
        global $settings;
        $stmt = $this->mysqli->prepare("SELECT idsetting FROM " . $settings['db_setting_table'] . " WHERE idgroup = ? AND `key` = ?");
        $stmt->bind_param('is', $setting->idGroup, $setting->key);

        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt2 = $this->mysqli->prepare("UPDATE " . $settings['db_setting_table'] . " SET value = ? WHERE idgroup = ? AND `key` = ?");
                $stmt2->bind_param('sis', $setting->value, $setting->idGroup, $setting->key);

                if ($stmt2->execute()) {
                    $stmt2->close();
                    $stmt->close();
                    return true;
                }
                $stmt2->close();
                $stmt->close();
                return false;
            } else {
                $stmt2 = $this->mysqli->prepare("INSERT INTO " . $settings['db_setting_table'] . " (`key`, value, idgroup) VALUES (?,?,?);");
                $stmt2->bind_param('ssi', $setting->key, $setting->value, $setting->idGroup);

                if ($stmt2->execute()) {
                    $stmt2->close();
                    $stmt->close();
                    return true;
                }
                $stmt2->close();
                $stmt->close();
                return false;
            }
        }
    }

    public function GetDinnerClosingTimeSetting($gid, $key) {
        global $settings;
        $stmt = $this->mysqli->prepare("SELECT idsetting, `key`, value, idgroup FROM " . $settings['db_setting_table'] . " WHERE idgroup=? AND `key`=?");
        $stmt->bind_param('is', $gid, $key);

        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idSetting, $key, $value, $idGroup);
                $stmt->fetch();
                $dctsSetting = new Setting();
                $dctsSetting->setId($idSetting);
                $dctsSetting->setKey($key);
                $dctsSetting->setValue($value);
                $dctsSetting->setIdGroup($idGroup);
                $stmt->close();

                return $dctsSetting;
            }

            $stmt->close();
            return null;
        }
    }

    public function GetUserSetting($gid, $key, $uid) {
        global $settings;
        $stmt = $this->mysqli->prepare("SELECT idsetting, `key`, value, idgroup, iduser FROM " . $settings['db_setting_table'] . " WHERE idgroup=? AND `key`=? AND iduser = ?");
        $stmt->bind_param('isi', $gid, $key, $uid);

        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idSetting, $key, $value, $idGroup, $iduser);
                $stmt->fetch();
                $Setting = new Setting();
                $Setting->setId($idSetting);
                $Setting->setKey($key);
                $Setting->setValue($value);
                $Setting->setIdGroup($idGroup);
                $Setting->setIdUser($iduser);
                $stmt->close();

                return $Setting;
            }

            $stmt->close();
            return null;
        }
    }

    public function UpdateUserSetting($setting) {
        global $settings;
        $stmt = $this->mysqli->prepare("SELECT idsetting FROM " . $settings['db_setting_table'] . " WHERE idgroup = ? AND `key` = ? AND iduser = ?");
        $stmt->bind_param('isi', $setting->idGroup, $setting->key, $setting->idUser);

        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt2 = $this->mysqli->prepare("UPDATE " . $settings['db_setting_table'] . " SET value = ? WHERE idgroup = ? AND `key` = ? AND iduser = ?");
                $stmt2->bind_param('sisi', $setting->value, $setting->idGroup, $setting->key, $setting->idUser);

                if ($stmt2->execute()) {
                    $stmt2->close();
                    $stmt->close();
                    return true;
                }
                $stmt2->close();
                $stmt->close();
                return false;
            } else {
                $stmt2 = $this->mysqli->prepare("INSERT INTO " . $settings['db_setting_table'] . " (`key`, value, idgroup, iduser) VALUES (?,?,?,?);");
                $stmt2->bind_param('ssii', $setting->key, $setting->value, $setting->idGroup, $setting->idUser);

                if ($stmt2->execute()) {
                    $stmt2->close();
                    $stmt->close();
                    return true;
                }
                $stmt2->close();
                $stmt->close();
                return false;
            }
        }
    }

    public function CheckInvites($email) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT idinvite FROM ' . $settings['db_invite_table'] . ' WHERE email = ?');
        $stmt->bind_param('s', $email);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CheckInvite ' . $stmt->error);
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $v = $stmt->num_rows;
                $stmt->close();
                return $v;
            }
        }
        $stmt->close();
        return 0;
    }

    public function GetInvites($email) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT idinvite,idgroup,iduser FROM ' . $settings['db_invite_table'] . ' WHERE email = ? ');
        $stmt->bind_param('s', $email);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetInvite ' . $stmt->error);
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $invites = array();
                $count = 0;
                $stmt->bind_result($idinvite, $idgroup, $iduser);
                while ($stmt->fetch()) {
                    $invites[$count][0] = $idinvite;
                    $invites[$count][1] = $idgroup;
                    $invites[$count][2] = $iduser;
                    $count++;
                }
                $stmt->Close();
                return $invites;
            }
        }
        $stmt->close();
        return null;
    }

    // (modules = 2 OR modules = 3 OR modules = 6 OR modules = 7) AND
    //

    public function GetAllGroupIds() {
        global $settings;
        $groupIds = array();
        $two = 2;
        $stmt = $this->mysqli->prepare('SELECT idgroup FROM ' . $settings['db_group_table'] . ' WHERE modules & ? ');
        $stmt->bind_param('s', $two);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetAllGroupIds ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($groupId);
                while ($stmt->fetch()) {
                    $groupIds[] = $groupId;
                }
                $stmt->Close();
                return $groupIds;
            }
        }
    }

    public function UpdateCookingPoints($saldo, $gid, $uid, $role, $guests) {
        global $settings;
        if ($role == 2) {
            $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_group_table'] . ' SET cooking_point = cooking_point + ? WHERE idgroup = ? AND iduser = ?');
            $stmt->bind_param('sss', $saldo, $gid, $uid);
            if (!$stmt->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateCookingPoints ' . $stmt->error);
                $stmt->close();
            }
            $stmt->close();
        } elseif ($role == 1) {
            if ($guests <= 0) {
                $guests = 1;
            }
            $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_group_table'] . ' SET cooking_point = cooking_point - ? WHERE idgroup = ? AND iduser = ?');
            $stmt->bind_param('sss', $guests, $gid, $uid);
            if (!$stmt->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateCookingPoints2 ' . $stmt->error);
                $stmt->close();
            }
            $stmt->close();
        }
    }

    /*     * ******************** END OF Group ********************* */

    /*     * ******************** BEGIN OF Cost ********************* */

    public function AddCost($amount, $ispaid, $description, $iduser, $idgroup, $isdinner, $date) {
        global $settings;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_cost_table'] . ' (amount,is_paid,description,iduser,idgroup,is_dinner,date_of_cost)VALUES( ?, ?, ?, ?, ?, ?, ? )');
        $stmt->bind_param('sssssss', $amount, $ispaid, $description, $iduser, $idgroup, $isdinner, $date);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddCost ' . $stmt->error);
            $stmt->close();
        } else {
            $costId = $stmt->insert_id;
            $stmt->close();
            return $costId;
        }
    }

    public function AddUserCost($iduser, $idcost, $guest) {
        global $settings;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_user_cost_table'] . ' (iduser,idcost,number_of_persons)VALUES( ?, ?, ? )');
        $stmt->bind_param('sss', $iduser, $idcost, $guest);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddUserCost ' . $stmt->error);
            $stmt->close();
        }
    }

    public function GetLastCost($groupId) {
        global $settings;
        $query = "SELECT idcost,amount,description,iduser,date_of_cost,is_deleted FROM " . $settings['db_cost_table'] . " WHERE idgroup = ? AND is_paid = 0 ORDER BY date_created DESC LIMIT 10";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $groupId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetLastCost ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $costs = array();
                $stmt->bind_result($idcost, $amount, $description, $iduser, $date, $deleted);
                while ($stmt->fetch()) {
                    $user = $this->GetUsersByCost($idcost);
                    $output = Cost::withCostUpdate($idcost, $amount, $description, $iduser, $date, $user, $deleted);
                    array_push($costs, $output);
                }
                $stmt->Close();
                return $costs;
            }
        }
    }

    public function GetUsersByCost($id) {
        global $settings;
        $query = "SELECT c.iduser, u.firstname, c.number_of_persons FROM " . $settings['db_user_cost_table'] . " as c INNER JOIN " . $settings['db_user_table'] . " as u ON c.iduser = u.iduser WHERE c.idcost = ? AND c.number_of_persons != 0";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersByCost ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $users = array();
                $stmt->bind_result($iduser, $username, $guests);
                while ($stmt->fetch()) {
                    $users[] = new User_Cost($iduser, $username, $guests);
                }
                $stmt->Close();
                return $users;
            }
        }
    }

    public function GetCostUser($id) {
        global $settings;
        $query = "SELECT iduser,number_of_persons as number FROM " . $settings['db_user_cost_table'] . " WHERE idcost = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetCostUser ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($iduser, $numberOfGuests);
                $costs = array();
                while ($stmt->fetch()) {
                    $output = Cost::withCostsUser($id, $iduser, $numberOfGuests);
                    array_push($costs, $output);
                }
                $stmt->Close();
                return $costs;
            }
        }
    }

    public function GetNumberOfGuests($id) {
        global $settings;
        $query = "SELECT SUM(number_of_persons) as number FROM " . $settings['db_user_cost_table'] . " WHERE idcost = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetNumberOfGuests ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($number);
                while ($stmt->fetch()) {
                    $output = $number;
                }
                $stmt->Close();
                return $output;
            }
        }
    }

    public function GetNumberOfGuestsUser($id, $uid) {
        global $settings;
        $query = "SELECT SUM(number_of_persons) as number FROM " . $settings['db_user_cost_table'] . " WHERE idcost = ? AND iduser = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ss', $id, $uid);
        $output = 0;
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetNumberOfGuestsUser ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($number);
                while ($stmt->fetch()) {
                    $output = $number;
                }
                $stmt->Close();
                return $output;
            }
        }
    }

    public function GetCostObject($id) {
        global $settings;
        $query = "SELECT idcost, amount, iduser FROM " . $settings['db_cost_table'] . " WHERE idgroup = ? AND is_paid = 0 AND is_deleted = 0";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetCostObject ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idcost, $amount, $iduser);
                $costs = array();
                while ($stmt->fetch()) {
                    $numberOfGuests = $this->GetNumberOfGuests($idcost);
                    if ($numberOfGuests != null) {
                        $output = Cost::withCosts($idcost, $amount, $iduser, $id, $numberOfGuests);
                        array_push($costs, $output);
                    } else {
                        $output = Cost::withCosts($idcost, $amount, $iduser, $id, 0);
                        array_push($costs, $output);
                    }
                }
                $stmt->Close();
                return $costs;
            }
            $stmt->close();
            return null;
        }
    }

    public function PayOff($gid) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_cost_table'] . ' SET is_paid = 1 WHERE idgroup = ?');
        $stmt->bind_param('s', $gid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: PayOff ' . $stmt->error);
            $stmt->close();
        } else {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return true;
            }
            $stmt->close();
            return false;
        }
    }

    public function CheckCostDinner($iduser, $idgroup) {
        global $settings;
        $query = "SELECT date_of_dinner,description FROM " . $settings['db_dinnerplanner_table'] . " WHERE iduser = ? AND idgroup = ? AND dinner_role = 2 AND (date_of_dinner < DATE(NOW()) OR (date_of_dinner = DATE(NOW()) AND CAST(NOW() AS time) > '16:00:00')) AND date_of_dinner NOT IN (SELECT date_of_cost FROM " . $settings['db_cost_table'] . " WHERE idgroup = ? AND is_dinner = 1 AND is_deleted = 0)";
        
		$stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('sss', $iduser, $idgroup, $idgroup);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CheckCostDinner ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($dateDinner, $description);
                $date = array();
                $count = 0;
                while ($stmt->fetch()) {
                    $date[$count][0] = $dateDinner;
                    $date[$count][1] = $description;
                    $count++;
                }
                $stmt->Close();
                return $date;
            }
        }
    }

    public function AddDinnerCost($idgroup, $date, $idcost) {
        global $settings;
        $query = "SELECT iduser,number_of_persons FROM " . $settings['db_dinnerplanner_table'] . " WHERE idgroup = ? AND date_of_dinner = ? AND dinner_role != 0";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ss', $idgroup, $date);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddDinnerCost ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $group = array();
                $stmt->bind_result($userId, $guests);
                while ($stmt->fetch()) {
                    $output = new User_Saldo($userId, $idcost, $guests);
                    array_push($group, $output);
                }
                $stmt->Close();
                foreach ($group as $g) {
                    if ($g->amount == 0 || $g->amount == null) {
                        $g->amount = 1;
                    }
                    $this->AddUserCost($g->id, $idcost, $g->amount);
                }
            }
        }
    }

    public function GetCostHistory($uid, $gid,$range) {
        global $settings;
        $position = 0;
        if ($range > 1) {
            $range--;
            $position = (50 * $range);
        }
        $query = "SELECT c.idcost, c.amount, c.description, c.iduser, u.firstname, c.idgroup, c.is_dinner, c.date_of_cost, c.is_deleted FROM " . $settings['db_cost_table'] . " as c INNER JOIN " . $settings['db_user_table'] . " as u ON c.iduser = u.iduser WHERE c.iduser = ? AND c.idgroup = ? AND c.is_paid = 0 ORDER BY c.date_created DESC LIMIT ?,50";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ssi', $uid, $gid,$position);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idcost, $amount, $description, $iduser, $nameUser, $idgroup, $is_dinner, $date_of_cost, $deleted);
                $cost = array();
                while ($stmt->fetch()) {
                    $user = $this->GetUsersByCost($idcost);
                    $output = Cost::withLFullCost($idcost, $amount, $description, $iduser, $nameUser, $idgroup, $is_dinner, $date_of_cost, $user, $deleted);
                    array_push($cost, $output);
                }
                $stmt->close();
                return $cost;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetCostHistory ' . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function GetCostById($cid) {
        global $settings;
        $query = "SELECT c.idcost, c.amount, c.description, c.iduser, u.firstname, c.idgroup, c.is_dinner, c.date_of_cost, c.is_deleted FROM " . $settings['db_cost_table'] . " as c INNER JOIN " . $settings['db_user_table'] . " as u ON c.iduser = u.iduser WHERE c.idcost = ? AND c.is_paid = 0 ";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $cid);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idcost, $amount, $description, $iduser, $nameuser, $idgroup, $is_dinner, $date_of_cost, $deleted);
                while ($stmt->fetch()) {
                    $user = $this->GetUsersByCost($idcost);
                    $cost = Cost::withLFullCost($idcost, $amount, $description, $iduser, $nameuser, $idgroup, $is_dinner, $date_of_cost, $user, $deleted);
                    $stmt->close();
                    return $cost;
                }
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetCostById ' . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function EditCostData($cost) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_cost_table'] . ' SET description = ?, amount = ? , date_of_cost = ?, is_dinner = ?, iduser = ? WHERE idcost = ? ');
        $stmt->bind_param('ssssss', $cost->description, $cost->amount, $cost->date, $cost->isDinner, $cost->idUser, $cost->id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: EditCostData ' . $stmt->error);
        }
        $stmt->close();
    }

    public function DeleteCostById($cid) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_cost_table'] . ' SET is_deleted = 1 WHERE idcost = ? ');
        $stmt->bind_param('s', $cid);
        if ($stmt->execute()) {
            $stmt->Close();
            return true;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeleteCostById ' . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    public function GetCostGuests($cid, $uid) {
        global $settings;
        $query = "SELECT number_of_persons as number FROM " . $settings['db_user_cost_table'] . " WHERE iduser = ? AND idcost = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ss', $uid, $cid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetCostGuests ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($numberOfGuests);
                $stmt->fetch();
                $stmt->close();
                return $numberOfGuests;
            } else {
                $stmt->close();
                return 0;
            }
        }
    }

    public function UpdateCostGuests($uid, $guests, $cid) {
        global $settings;
        $query = "SELECT number_of_persons as number FROM " . $settings['db_user_cost_table'] . " WHERE iduser = ? AND idcost = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ss', $uid, $cid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateCostGuests ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->Close();
                $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_cost_table'] . ' SET number_of_persons = ? where iduser = ? AND idcost = ?');
                $stmt->bind_param('sss', $guests, $uid, $cid);
                $stmt->execute();
                $stmt->Close();
            } else {
                $stmt->Close();
                if ($guests > 0) {
                    $this->AddUserCost($uid, $cid, $guests);
                }
            }
        }
    }

    public function GetLastGroupCost($groupId,$range) {
        global $settings;
        $position = 0;
        if ($range > 1) {
            $range--;
            $position = (50 * $range);
        }
        $query = "SELECT c.idcost, c.amount, c.description, c.iduser, u.firstname, c.idgroup, c.is_dinner, c.date_of_cost, c.is_deleted FROM " . $settings['db_cost_table'] . " as c INNER JOIN " . $settings['db_user_table'] . " as u ON c.iduser = u.iduser WHERE c.idgroup = ? AND c.is_paid = 0 ORDER BY c.date_created DESC LIMIT ?,50";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('si', $groupId,$position);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: getLastGroupCost ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            $costs = array();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idcost, $amount, $description, $iduser, $nameUser, $idgroup, $is_dinner, $date_of_cost, $deleted);
                while ($stmt->fetch()) {
                    $user = $this->GetUsersByCost($idcost);
                    $output = Cost::withLFullCost($idcost, $amount, $description, $iduser, $nameUser, $idgroup, $is_dinner, $date_of_cost, $user, $deleted);
                    array_push($costs, $output);
                }
                $stmt->Close();
            }
            return $costs;
        }
    }

    public function GetPayedGroupCosts($groupId,$range) {
        global $settings;
        $position = 0;
        if ($range > 1) {
            $range--;
            $position = (50 * $range);
        }
        $query = "SELECT c.idcost, c.amount, c.description, c.iduser, u.firstname, c.idgroup, c.is_dinner, c.date_of_cost, c.is_deleted FROM " . $settings['db_cost_table'] . " as c INNER JOIN " . $settings['db_user_table'] . " as u ON c.iduser = u.iduser WHERE c.idgroup = ? AND c.is_paid = 1 ORDER BY c.date_created DESC LIMIT ?,50";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('si', $groupId,$position);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetPayedGroupCosts ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            $costs = array();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idcost, $amount, $description, $iduser, $nameUser, $idgroup, $is_dinner, $date_of_cost, $deleted);
                while ($stmt->fetch()) {
                    $user = $this->GetUsersByCost($idcost);
                    $output = Cost::withLFullCost($idcost, $amount, $description, $iduser, $nameUser, $idgroup, $is_dinner, $date_of_cost, $user, $deleted);
                    array_push($costs, $output);
                }
                $stmt->Close();
            }
            return $costs;
        }
    }

    public function CountCostByGroupId($idgroup, $what, $userid) {
        global $settings;
        switch ($what) {
            case 'all':
                $query = "SELECT COUNT(idcost) FROM " . $settings['db_cost_table'] . " WHERE idgroup = ? AND is_paid = 0";
                $stmt = $this->mysqli->prepare($query);
                $stmt->bind_param('i', $idgroup);
                break;
            case 'my':
                $query = "SELECT COUNT(idcost) FROM " . $settings['db_cost_table'] . " WHERE iduser = ? AND idgroup = ? AND is_paid = 0";
                $stmt = $this->mysqli->prepare($query);
                $stmt->bind_param('ii', $userid, $idgroup);
                break;
            case 'payed':
                $query = "SELECT COUNT(idcost) FROM " . $settings['db_cost_table'] . " WHERE idgroup = ? AND is_paid = 1";
                $stmt = $this->mysqli->prepare($query);
                $stmt->bind_param('i', $idgroup);
                break;
            default:
                $query = "SELECT COUNT(idcost) FROM " . $settings['db_cost_table'] . " WHERE idgroup = ? AND is_paid = 0";
                $stmt = $this->mysqli->prepare($query);
                $stmt->bind_param('i', $idgroup);
        }

        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($count);
                $stmt->fetch();
                $c = $count;
                $stmt->Close();
                return $c;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CountCostByGroupId ' . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function CheckImportCost($gid) {
        global $settings;
        $query = "SELECT idcost FROM " . $settings['db_cost_table'] . " WHERE idgroup = ? AND is_dinner = 2";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $gid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CheckImportCost ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->close();
                return true;
            } else {
                $stmt->close();
                return false;
            }
        }
    }

    public function GetUserSaldo($gid, $uid) {
        global $settings;
        $query = "SELECT saldo FROM " . $settings['db_user_group_table'] . " WHERE idgroup = ? AND iduser = ? AND is_deleted != 1";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ss', $gid, $uid);
        if ($stmt->execute()) {
            $stmt->store_result();
            $stmt->bind_result($saldo);
            $stmt->fetch();
            $stmt->close();
            return $saldo;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserSaldo ' . $stmt->error);
        }
        $stmt->close();
        return null;
    }

    public function GetGroupUserSaldo($gid) {
        global $settings;
        $users = array();
        $query = "SELECT iduser,saldo FROM " . $settings['db_user_group_table'] . " WHERE idgroup = ? AND is_deleted != 1";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $gid);
        if ($stmt->execute()) {
            $stmt->store_result();
            $stmt->bind_result($uid, $saldo);
            while ($stmt->fetch()) {
                $users[$uid][0] = $uid;
                $users[$uid][1] = $saldo;
            }
            $stmt->close();
            return $users;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetGroupUserSaldo ' . $stmt->error);
        }
        $stmt->close();
        return null;
    }

    public function GetGroupUserAvgCookingCost($gid) {
        global $settings;
        $users = array();
        $query = "SELECT iduser,avg_cooked,cooking_point FROM " . $settings['db_user_group_table'] . " WHERE idgroup = ? AND is_deleted != 1";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $gid);
        if ($stmt->execute()) {
            $stmt->store_result();
            $stmt->bind_result($uid, $saldo, $cp);
            while ($stmt->fetch()) {
                $users[$uid][0] = $uid;
                $users[$uid][1] = $saldo;
                $users[$uid][2] = $cp;
            }
            $stmt->close();
            return $users;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetGroupAvgCookingCost ' . $stmt->error);
        }
        $stmt->close();
        return null;
    }

    public function UpdateGroupUserSaldo($gid) {
        global $settings;
        $cost = new Cost();
        $userIdAmount = $cost->CalculateGroupSaldo($gid);

        if (isset($userIdAmount)) {
            foreach ($userIdAmount as $u) {
                $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_group_table'] . ' SET saldo = ? WHERE idgroup = ? AND iduser = ?');
                $stmt->bind_param('sss', $u->amount, $gid, $u->id);
                if (!$stmt->execute()) {
                    error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateGroupUserSaldo ' . $stmt->error);
                }
                $stmt->close();
            }
        }
    }

    public function UpdateGroupUserAvgCooked($group) {
        global $settings;
        $avgDinnerCost = $this->GetDinnerStaticsAvgDinnerCost($group);
        $users = $group->getUsers();
        foreach ($users as $u) {
            $avg = (isset($avgDinnerCost[$u->id]) ? $avgDinnerCost[$u->id] : 0.00);
            $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_group_table'] . ' SET avg_cooked = ? WHERE idgroup = ? AND iduser = ?');
            $stmt->bind_param('sss', $avg, $group->id, $u->id);
            if (!$stmt->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateGroupUserAvgCooked ' . $stmt->error);
            }
            $stmt->close();
        }
    }

    /*     * ******************** END OF Cost ********************* */

    /*     * ******************** BEGIN OF TASK ********************* */

    public function AddTask($name, $description, $idgroup) {
        global $settings;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_task_table'] . ' VALUES( 0, ?, ?, ?, 0)');
        $stmt->bind_param('sss', $name, $description, $idgroup);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddTask ' . $stmt->error);
        }
        $stmt->close();
    }

    public function EditTask($task) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_task_table'] . ' SET name = ?, description = ? WHERE idtask = ? ');
        $stmt->bind_param('sss', $task->name, $task->description, $task->id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: EditTask ' . $stmt->error);
        }
        $stmt->close();
    }

    public function DeleteTaskById($taskId) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_task_table'] . ' SET is_deleted = 1 WHERE idtask = ?');
        $stmt->bind_param('s', $taskId);
        if ($stmt->execute()) {
            $stmt->store_result();
            while ($stmt->fetch()) {
                $output = new Task($idtask, $name, $description, $groupId);
                array_push($tasks, $output);
            }
            return true;
        }
        error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeleteTaskById ' . $stmt->error);
        $stmt->close();
        return false;
    }

    public function GetTasksByGroupId($groupId) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT idtask, name, description, idgroup FROM ' . $settings['db_task_table'] . ' WHERE idgroup = ? AND is_deleted != 1');
        $stmt->bind_param('s', $groupId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetTaskByGroupId ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $tasks = array();
                $stmt->bind_result($idtask, $name, $description, $groupId);
                while ($stmt->fetch()) {
                    $output = new Task($idtask, $name, $description, $groupId);
                    array_push($tasks, $output);
                }
                $stmt->close();
                return $tasks;
            }
            $stmt->close();
            return array();
        }
    }

    public function GetTaskById($taskId) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT idtask, name, description, idgroup FROM ' . $settings['db_task_table'] . ' WHERE idtask = ? AND is_deleted != 1');
        $stmt->bind_param('s', $taskId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetTaskById ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idtask, $name, $description, $groupId);
                $stmt->fetch();
                $output = new Task($idtask, $name, $description, $groupId);
                $stmt->close();
                return $output;
            }
            $stmt->close();
            return null;
        }
    }

    public function GetUserTasks($date, $groupId) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT ut.idtask, t.name, t.description, t.idgroup, ut.iduser FROM ' . $settings['db_user_task_table'] . ' as ut JOIN ' . $settings['db_task_table'] . ' as t ON ut.idtask = t.idtask WHERE ut.date = ? AND t.idgroup = ? AND t.is_deleted != 1');
        $stmt->bind_param('si', $date, $groupId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserTasks ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idtask, $name, $description, $groupId, $iduser);
                while ($stmt->fetch()) {
                    $output[$iduser] = new Task($idtask, $name, $description, $groupId);
                }
                $stmt->close();
                return $output;
            }
            $stmt->close();
            return null;
        }
    }

    public function SetUserTask($usertask, $group, $uid) {
        global $settings;
        $stmt = $this->mysqli->prepare("INSERT INTO " . $settings['db_user_task_table'] . " VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE is_done = '0';");
        $stmt->bind_param('ssss', $usertask->idTask, $usertask->idUser, $usertask->date, $usertask->isDone);
        if ($stmt->execute()) {
            $stmt2 = $this->mysqli->prepare("DELETE FROM " . $settings['db_user_task_table'] . " WHERE is_done LIKE '%0%'");
            if (!$stmt2->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: SetUserTask2 ' . $stmt2->error);
            } else {
                if ($stmt2->affected_rows > 0) {
                    $stmt2->close();
                    $stmt->close();
                    // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                    $lb = new Logbook(null, $group->id, $uid, null, null, null, null, 'TND', $this->GetUserNameById($usertask->idUser) . '|' . $usertask->date);
                    $this->AddLogbookItem($lb);
                    return true;
                } else {
                    $stmt2->close();
                    $stmt->close();
                    // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
                    $lb = new Logbook(null, $group->id, $uid, null, null, null, null, 'TDN', $this->GetUserNameById($usertask->idUser) . '|' . $usertask->date);
                    $this->AddLogbookItem($lb);
                    return true;
                }
            }
            $stmt2->close();
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: SetUserTask ' . $stmt->error);
            error_log('Additional info User task: ' . var_dump($usertask) . ' groupId: ' . $group->id . ' userId: ' . $uid);
        }
        $stmt->close();
        return false;
    }

    /*     * ******************** END OF TASK ********************* */

    /*     * ******************** BEGIN OF DINNER ********************* */

    public function GetDinnerStatistics($groupId) {
        global $settings;

        $stmt = $this->mysqli->prepare('SELECT idUser, dinner_role, COUNT(1) FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE idgroup = ? AND date_of_dinner < CURDATE() GROUP BY iduser, dinner_role');
        $stmt->bind_param('i', $groupId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDinnerStatistics ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idUser, $dinner_role, $count);
                $dinnerStats = array();
                while ($stmt->fetch()) {
                    $dinnerStats[$idUser][$dinner_role] = new DinnerStatisticData($idUser, $dinner_role, $count);
                }
                $stmt->close();
                return $dinnerStats;
            }
            $stmt->close();
            return null;
        }
    }

    public function GetDinnerrStaticsAvgMeeEters($gid, $uid) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT SUM(number_of_persons) FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE idgroup = ? AND date_of_dinner  IN (
        SELECT date_of_dinner FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE iduser = ? AND idgroup = ? AND dinner_role = 2 AND date_of_dinner < CURDATE());');
        $stmt->bind_param('iii', $gid, $uid, $gid);
        $stmt1 = $this->mysqli->prepare('SELECT COUNT(date_of_dinner) FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE iduser = ? AND idgroup = ? AND dinner_role = 2 AND date_of_dinner < CURDATE()');
        $stmt1->bind_param('ii', $uid, $gid);
        $bool = true;
        $SUM = 0;
        $COUNT = 0;
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDinnerrStaticsAvgMeeEters ' . $stmt->error);
            $bool = false;
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($SUM);
                $stmt->fetch();
                $SUM = $SUM;
                $stmt->close();
            }
        }
        if (!$stmt1->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDinnerrStaticsAvgMeeEters2 ' . $stmt1->error);
            $bool = false;
        } else {
            $stmt1->store_result();
            if ($stmt1->num_rows > 0) {
                $stmt1->bind_result($COUNT);
                $stmt1->fetch();
                $COUNT = $COUNT;
                $stmt1->close();
            }
        }
        if ($bool == true) {
            if ($COUNT != 0 && $SUM != 0) {
                $AVG = $SUM / $COUNT;
                return round($AVG, 2);
            } else {
                return 0;
            }
        } else {
            $stmt->close();
            $stmt1->close();
            return 0;
        }
    }

    public function GetDinnerStaticsAvgDinnerCost($group) {
        global $settings;
        $AVG = array();
        $AMOUNT = array();
        $stmt = $this->mysqli->prepare(' SELECT SUM(amount),iduser FROM ' . $settings['db_cost_table'] . ' WHERE idgroup = ? AND is_dinner = 1 AND is_deleted = 0 GROUP BY iduser;');
        $stmt->bind_param('i', $group->id);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDinnerStaticsAvgDinnerCost ' . $stmt->error);
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($SUM, $uid);
                while ($stmt->fetch()) {
                    if ($SUM != 0) {
                        $AMOUNT[$uid] = $SUM;
                    } else {
                        $AMOUNT[$uid] = 0;
                    }
                }
                $stmt->close();
            }
        }
        foreach ($group->getUsers() as $u) {
            $SUM = 0;
            if (isset($AMOUNT[$u->id])) {
                if ($AMOUNT[$u->id] != 0) {
                    $stmt1 = $this->mysqli->prepare('SELECT SUM(number_of_persons) FROM ' . $settings['db_user_cost_table'] . ' WHERE idcost IN (
  SELECT idcost FROM ' . $settings['db_cost_table'] . ' WHERE iduser = ? AND idgroup = ? AND is_dinner = 1 AND is_deleted = 0 )');
                    $stmt1->bind_param('ii', $u->id, $group->id);

                    if (!$stmt1->execute()) {
                        error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDinnerStaticsAvgDinnerCost1 ' . $stmt->error);
                        $stmt1->close();
                    } else {
                        $stmt1->store_result();
                        if ($stmt1->num_rows > 0) {
                            $stmt1->bind_result($SUM);
                            while ($stmt1->fetch()) {
                                $SUM = $SUM;
                            }
                            $stmt1->close();
                        } else {
                            $SUM = 0;
                        }
                    }
                    if ($SUM != 0) {
                        $avg = $AMOUNT[$u->id] / $SUM;
                        $AVG[$u->id] = round($avg, 2);
                    }
                }
            }
        }
        return $AVG;
    }

    public function GetDinnerByDate($id, $date) {
        global $settings;

        $stmt = $this->mysqli->prepare('SELECT idUser, dinner_role, number_of_persons, description FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE
        idgroup = ? AND date_of_dinner = ?');
        $stmt->bind_param('is', $id, $date);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDinnerByDate ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idUser, $dinner_role, $guests, $description);
                $dinner = array();
                while ($stmt->fetch()) {
                    $dinner[$idUser] = new Dinner($id, $idUser, $dinner_role, $date, $description, $guests);
                }
                $stmt->close();
                return $dinner;
            }
            $stmt->close();
            return null;
        }
    }

    public function UpdateDinner($gid, $uid, $date, $role) {
        global $settings;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_dinnerplanner_table'] . '(iduser, idgroup, date_of_dinner, dinner_role) VALUES
        (?,?,?,?)
        ON DUPLICATE KEY UPDATE dinner_role = VALUES(dinner_role)');
        $stmt->bind_param('iisi', $uid, $gid, $date, $role);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateDinner ' . $stmt->error);
        }
        $stmt->close();
		
		$this->InsertOrUpdateTotalChefpoints($gid, $date);
		$this->UpdateCookingPoints3($uid, $gid);
    }

    public function UpdateAdvancedDinner($gid, $uid, $date, $role, $guests, $description) {
        global $settings;
        if ($guests == null || $guests == 0) {
            $guests = 1;
        }
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_dinnerplanner_table'] . '(iduser, idgroup, number_of_persons, date_of_dinner, dinner_role, description) VALUES
        (?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE dinner_role = VALUES(dinner_role),
        number_of_persons = VALUES(number_of_persons),
        description = VALUES(description)');
        $stmt->bind_param('isisis', $uid, $gid, $guests, $date, $role, $description);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateAdvancedDinner ' . $stmt->error);
        }
        $stmt->close();
		
		$this->InsertOrUpdateTotalChefpoints($gid, $date);
		$this->UpdateCookingPoints3($uid, $gid);
    }

    public function GetAllGuestsFromDinner($idgroup, $date) {
        global $settings;
        $query = "SELECT dp.iduser, u.firstname, dp.number_of_persons AS guests FROM " . $settings['db_dinnerplanner_table'] . " as dp INNER JOIN " . $settings['db_user_table'] . " as u ON dp.iduser = u.iduser WHERE dp.idgroup = ? AND dp.date_of_dinner = ? AND dp.dinner_role != 0 GROUP BY dp.iduser";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ss', $idgroup, $date);

        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetAllGuestsFromDinner ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($iduser, $username, $guests);
                while ($stmt->fetch()) {
                    $output[] = new User_Cost($iduser, $username, $guests);
                }
                $stmt->Close();
                return $output;
            }
        }
    }

    public function GetAllDinnerDatesByGroupId($idgroup) {
        global $settings;
        $query = "SELECT date_of_dinner FROM " . $settings['db_dinnerplanner_table'] . " WHERE idgroup = ? GROUP BY date_of_dinner";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $idgroup);

        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetAllDinnerDates ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($date_of_dinner);
                while ($stmt->fetch()) {
                    $output[] = $date_of_dinner;
                }
                $stmt->Close();
                return $output;
            }
        }
    }

    // gets the dinner data for the specific user on the specific date
    public function GetDinnerByUserDate($date, $idgroup, $idUser) {

        global $settings;

        $stmt = $this->mysqli->prepare('SELECT dinner_role, number_of_persons, description FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE date_of_dinner=? AND iduser=? AND idgroup=?');
        $stmt->bind_param('sii', $date, $idUser, $idgroup);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDinneerByUserDate ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($dinner_role, $guests, $description);
                $stmt->fetch();

                $dinner = new Dinner($idgroup, $idUser, $dinner_role, $date, $description, $guests);
                $stmt->close();
                return $dinner;
            }
        }
    }

    public function CheckChef($date, $groupid) {
        global $settings;

        $stmt = $this->mysqli->prepare('SELECT iduser FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE date_of_dinner=? AND idgroup=? AND dinner_role=2');
        $stmt->bind_param('si', $date, $groupid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CheckChef ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->close();
                return 1;
            }
            $stmt->close();
            return 0;
        }
    }

    public function GetDinner($date, $groupid) {
        global $settings;
        $uid = array();
        $desc = array();
        $stmt = $this->mysqli->prepare('SELECT iduser, description FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE date_of_dinner=? AND idgroup=? AND dinner_role=2');
        $stmt->bind_param('si', $date, $groupid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDinner ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($iduser, $description);
                while ($stmt->fetch()) {
                    $uid[] = $iduser;
                    $desc[] = $description;
                }
                $stmt->close();
                $dinner[0] = $uid;
                $dinner[1] = $desc;
                return $dinner;
            }
            $stmt->close();
            return null;
        }
    }

    public function GetGroupDinners($date, $groupid) {
        global $settings;
        $user = array();
        $stmt = $this->mysqli->prepare('SELECT iduser, dinner_role, number_of_persons FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE (dinner_role = 2 OR dinner_role = 1) AND date_of_dinner = ? AND idgroup = ?');
        $stmt->bind_param('si', $date, $groupid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetGroupDInners ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $count = 0;
                $stmt->bind_result($iduser, $role, $guests);
                while ($stmt->fetch()) {
                    $user[$count][0] = $iduser;
                    $user[$count][1] = $role;
                    $user[$count][2] = $guests;
                    $count++;
                }
                $stmt->close();
                return $user;
            }
            $stmt->close();
            return null;
        }
    }

    public function GetGroupDinnerTime($date, $groupid) {
        global $settings;
        $stmt = $this->mysqli->prepare('SELECT time FROM ' . $settings['db_dinner_table'] . ' WHERE idgroup = ? AND date = ?');
        $stmt->bind_param('ss', $groupid, $date);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetGroupDinnerTime ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($time);
                $stmt->fetch();
                $stmt->close();
                return $time;
            }
            $stmt->close();
            return null;
        }
    }

    public function UpdateGroupDinnerTime($gid, $date, $time) {
        global $settings;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_dinner_table'] . '(idgroup, date, time) VALUES
        (?,?,?) ON DUPLICATE KEY UPDATE time = VALUES(time)');
        $stmt->bind_param('sss', $gid, $date, $time);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateGroupDinnerTime ' . $stmt->error);
        }
        $stmt->close();
    }

    /*     * ******************** END OF DINNER ********************* */

    /*     * ******************** BEGIN OF LOGBOOK ********************* */

    public function InsertLogbookItem($role, $userData, $dateValue, $nrOfPersons, $dateValue, $group, $user) {
        $lbKey = "";
        $lbValue = "";
        if ($role == 0) {
            $lbKey = 'ENW';
            $lbValue = $this->GetUserNameById($userData) . '|' . $dateValue;
        } elseif ($role == 1) {
            $lbKey = 'EW';
            $lbValue = $this->GetUserNameById($userData) . '|' . $nrOfPersons . '|' . $dateValue;
        } elseif ($role == 2) {
            $lbKey = 'EC';
            $lbValue = $this->GetUserNameById($userData) . '|' . $nrOfPersons . '|' . $dateValue;
        } elseif ($role == -1) {
            $lbKey = 'ENS';
            $lbValue = $this->GetUserNameById($userData) . '|' . $dateValue;
        }
        // $idLogbook,$idGroup,$idUser,$idTask,$idCost,$dateOfDinner,$dateCreated,$code,$value)
        $lb = new Logbook(null, $group->id, $user->id, null, $userData, $dateValue, null, $lbKey, $lbValue);
        $this->AddDinnerLogbookItem($lb);
    }

    public function GetLogbookByGroupId($idgroup, $what, $range) {
        // SQL_CALC_FOUND_ROWS
        // SELECT FOUND_ROWS()
        // http://stackoverflow.com/questions/8060213/how-to-count-all-records-but-only-retrieve-limit-a-specific-number-for-display
        $position = 0;
        if ($range > 1) {
            $range--;
            $position = (50 * $range);
        }
        global $settings;
        switch ($what) {
            case 'all':
                $query = "SELECT idlogbook, idgroup, iduser, idtask, idcost, date_of_dinner, DATE_FORMAT(date_created, '%d-%m-%Y')
                as date_created, code, value FROM " . $settings['db_logbook_table'] . " WHERE idgroup = ? ORDER BY idlogbook DESC LIMIT ?,50";
                break;
            case 'cost':
                $query = "SELECT idlogbook, idgroup, iduser, idtask, idcost, date_of_dinner, DATE_FORMAT(date_created, '%d-%m-%Y')
        as date_created, code, value FROM " . $settings['db_logbook_table'] . " WHERE (code = \"CA\" OR code = \"CE\" OR code = \"CD\" OR code = \"PO\") AND idgroup = ? ORDER BY idlogbook DESC LIMIT ?,50";
                break;
            case 'dinner':
                $query = "SELECT idlogbook, idgroup, iduser, idtask, idcost, date_of_dinner, DATE_FORMAT(date_created, '%d-%m-%Y')
         as date_created, code, value FROM " . $settings['db_logbook_table'] . " WHERE (code = \"EC\" OR code = \"EW\" OR code = \"ENW\" OR code = \"ENS\") AND idgroup = ? ORDER BY idlogbook DESC LIMIT ?,50";
                break;
            case 'task':
                $query = "SELECT idlogbook, idgroup, iduser, idtask, idcost, date_of_dinner, DATE_FORMAT(date_created, '%d-%m-%Y')
        as date_created, code, value FROM " . $settings['db_logbook_table'] . " WHERE (code = \"TA\" OR code = \"TD\" OR
        code = \"TE\" OR code = \"TDN\" OR code= \"TND\") AND idgroup = ? ORDER BY idlogbook DESC LIMIT ?,50";
                break;
            case 'group':
                $query = "SELECT idlogbook, idgroup, iduser, idtask, idcost, date_of_dinner, DATE_FORMAT(date_created, '%d-%m-%Y')
        as date_created, code, value FROM " . $settings['db_logbook_table'] . " WHERE (code = \"GNE\" OR code = \"GUA\"
        OR code = \"GUD\" OR code = \"UA\" OR code = \"GME\") AND idgroup = ? ORDER BY idlogbook DESC LIMIT ?,50";
                break;
            default:
                $query = "SELECT idlogbook, idgroup, iduser, idtask, idcost, date_of_dinner, DATE_FORMAT(date_created, '%d-%m-%Y')
                as date_created, code, value FROM " . $settings['db_logbook_table'] . " WHERE idgroup = ? ORDER BY idlogbook DESC LIMIT ?,50";
        }
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ii', $idgroup, $position);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idLogbook, $idGroup, $idUser, $idTask, $idCost, $dateOfDinner, $dateCreated, $code, $value);
                while ($stmt->fetch()) {
                    $output[] = new Logbook($idLogbook, $idGroup, $idUser, $idTask, $idCost, $dateOfDinner, $dateCreated, $code, $value);
                }
                $stmt->Close();
                return $output;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetLogBookByGroupId ' . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function CountLogbookByGroupId($idgroup, $what) {
        global $settings;
        switch ($what) {
            case 'all':
                $query = "SELECT COUNT(idlogbook) FROM " . $settings['db_logbook_table'] . " WHERE idgroup = ?";
                break;
            case 'cost':
                $query = "SELECT COUNT(idlogbook) FROM " . $settings['db_logbook_table'] . " WHERE (code = \"CA\" OR code = \"CE\"
                OR code = \"CD\" OR code = \"PO\") AND idgroup = ?";
                break;
            case 'dinner':
                $query = "SELECT COUNT(idlogbook) FROM " . $settings['db_logbook_table'] . " WHERE (code = \"EC\" OR code
                = \"EW\" OR code = \"ENW\" OR code = \"ENS\") AND idgroup = ?";
                break;
            case 'task':
                $query = "SELECT COUNT(idlogbook) FROM " . $settings['db_logbook_table'] . " WHERE (code = \"TA\" OR code = \"TD\" OR
        code = \"TE\" OR code = \"TDN\" OR code= \"TND\") AND idgroup = ?";
                break;
            case 'group':
                $query = "SELECT COUNT(idlogbook) FROM " . $settings['db_logbook_table'] . " WHERE (code = \"GNE\" OR code = \"GUA\"
        OR code = \"GUD\" OR code = \"UA\" OR code = \"GME\") AND idgroup = ?";
                break;
            default:
                $query = "SELECT COUNT(idlogbook) FROM " . $settings['db_logbook_table'] . " WHERE idgroup = ?";
        }
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('i', $idgroup);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($count);
                $stmt->fetch();
                $c = $count;
                $stmt->Close();
                return $c;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: CountLogBookByGroupId ' . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function AddLogbookItem($logbook) {
        global $settings;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_logbook_table'] . ' (idlogbook, idgroup, iduser, idtask, idcost, date_of_dinner, date_created, code, value)VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ? )');
        $stmt->bind_param('sssssssss', $logbook->idLogbook, $logbook->idGroup, $logbook->idUser, $logbook->idTask, $logbook->idCost, $logbook->dateOfDinner, $logbook->dateCreated, $logbook->code, $logbook->value);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddLogbookItem ' . $stmt->error);
        } else
        if ($stmt->affected_rows > 0) {
            $stmt->close();
            return true;
        }
        $stmt->close();
        return false;
    }

    public function AddDinnerLogbookItem($logbook) {
        global $settings;
        $query = "SELECT idlogbook FROM " . $settings['db_logbook_table'] . " WHERE idgroup = ? AND dinneruser = ? AND date_of_dinner = ? AND iduser = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ssss', $logbook->idGroup, $logbook->idCost, $logbook->dateOfDinner, $logbook->idUser);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idLogbook);
                $stmt->fetch();
                $stmt2 = $this->mysqli->prepare('UPDATE ' . $settings['db_logbook_table'] . ' SET value = ?, code = ? WHERE idlogbook = ?');
                $stmt2->bind_param('sss', $logbook->value, $logbook->code, $idLogbook);
                $stmt->Close();
                if (!$stmt2->execute()) {
                    error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddDinnerLogbookItem ' . $stmt2->error);
                } else
                if ($stmt2->affected_rows > 0) {
                    $stmt2->close();
                    return true;
                }
                $stmt2->close();
                return false;
            } else {
                $stmt->Close();
                $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_logbook_table'] . ' (idlogbook, idgroup, iduser, idtask, idcost, date_of_dinner, date_created, code, value,dinneruser)VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ? ,?)');
                $stmt->bind_param('ssssssssss', $logbook->idLogbook, $logbook->idGroup, $logbook->idUser, $logbook->idTask, $logbook->idTask, $logbook->dateOfDinner, $logbook->dateCreated, $logbook->code, $logbook->value, $logbook->idCost);
                if (!$stmt->execute()) {
                    error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddDinnerLogbookItem ' . $stmt->error);
                } else
                if ($stmt->affected_rows > 0) {
                    $stmt->close();
                    return true;
                }
                $stmt->close();
                return false;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddDinnerLogbookItem ' . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    public function GetCostByIdLogBook($cid) {
        global $settings;
        $query = "SELECT c.idcost, c.amount, c.description, c.iduser, u.firstname, c.idgroup, c.is_dinner, c.date_of_cost, c.is_deleted FROM " . $settings['db_cost_table'] . " as c INNER JOIN " . $settings['db_user_table'] . " as u ON c.iduser = u.iduser WHERE c.idcost = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $cid);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idcost, $amount, $description, $iduser, $nameUser, $idgroup, $is_dinner, $date_of_cost, $deleted);
                while ($stmt->fetch()) {
                    $userc = $this->GetUsersByCost($idcost);
                    $cost = Cost::withLFullCost($idcost, $amount, $description, $iduser, $nameUser, $idgroup, $is_dinner, $date_of_cost, $userc, $deleted);
                    $stmt->close();
                    return $cost;
                }
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetCostByIdLogBook ' . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function GetLogbookByGroupIdMax10($idgroup) {
        global $settings;
        $query = "SELECT idlogbook, idgroup, iduser, idtask, idcost, date_of_dinner, DATE_FORMAT(date_created, '%d-%m-%Y') as date_created, code, value FROM " . $settings['db_logbook_table'] . " WHERE idgroup = ? ORDER BY idlogbook DESC LIMIT 10";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('i', $idgroup);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idLogbook, $idGroup, $idUser, $idTask, $idCost, $dateOfDinner, $dateCreated, $code, $value);
                while ($stmt->fetch()) {
                    $output[] = new Logbook($idLogbook, $idGroup, $idUser, $idTask, $idCost, $dateOfDinner, $dateCreated, $code, $value);
                }
                $stmt->Close();
                return $output;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetLogBookByGroupId ' . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    /*     * ******************** END OF LOGBOOK ********************* */

    /*     * ******************** BEGIN OF Sticky notes ********************* */

    public function GetStickyNotesByGroup($gid) {
        global $settings;
        $query = "SELECT idstickynote, title, message, DATE_FORMAT(date_created, '%d-%m-%Y') as d_c, iduser, idgroup FROM " . $settings['db_stickynote_table'] . " WHERE idgroup = ? ORDER BY date_created DESC";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $gid);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idStickNote, $title, $message, $dateCreated, $iduser, $idgroup);
                while ($stmt->fetch()) {
                    $output[] = new Sticky_Note($idStickNote, $title, $message, $dateCreated, $iduser, $idgroup);
                }
                $stmt->close();
                return $output;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetStickyNoteByGroup ' . $stmt->error);
        }
        $stmt->close();
        return array();
    }

    public function GetSidebarStickyNotesByGroup($gid) {
        global $settings;
        $query = "SELECT idstickynote, title, message, DATE_FORMAT(date_created, '%d-%m-%Y') as d_c, iduser, idgroup FROM " . $settings['db_stickynote_table'] . " WHERE idgroup = ? ORDER BY date_created DESC LIMIT 0,3";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $gid);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idStickNote, $title, $message, $dateCreated, $iduser, $idgroup);
                while ($stmt->fetch()) {
                    $output[] = new Sticky_Note($idStickNote, $title, $message, $dateCreated, $iduser, $idgroup);
                }
                $stmt->close();
                return $output;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetSidebarStickyNotesByGroup ' . $stmt->error);
        }
        $stmt->close();
        return array();
    }

    public function GetStickyNotes($sid, $gid) {
        global $settings;
        $query = "SELECT idstickynote, title, message, DATE_FORMAT(date_created, '%d-%m-%Y') as d_c, iduser, idgroup FROM " . $settings['db_stickynote_table'] . " WHERE idgroup = ? AND idstickynote = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ss', $gid, $sid);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idStickNote, $title, $message, $dateCreated, $iduser, $idgroup);
                $stmt->fetch();
                $note = new Sticky_Note($idStickNote, $title, $message, $dateCreated, $iduser, $idgroup);
                $stmt->close();
                return $note;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetStickyNot ' . $stmt->error);
        }
        $stmt->close();
        return null;
    }

    public function AddStickyNote($stickyNote) {
        global $settings;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_stickynote_table'] . ' (idstickynote, title, message, date_created, iduser, idgroup)VALUES( 0, ?, ?, NOW(), ?, ?)');
        $stmt->bind_param('ssii', $stickyNote->title, $stickyNote->message, $stickyNote->idUser, $stickyNote->idGroup);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return true;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddStickyNote ' . $stmt->error);
        }
        $stmt->close();
        return false;
    }

    public function DeleteStikyNote($idStickyNote) {
        global $settings;
        $stmt = $this->mysqli->prepare('DELETE FROM ' . $settings['db_stickynote_table'] . ' WHERE idstickynote = ?');
        $stmt->bind_param('i', $idStickyNote);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return true;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeleteStickyNote ' . $stmt->error);
        }
        $stmt->close();
        return false;
    }

    public function EditStickyNote($sid, $title, $desc, $gid) {
        global $settings;
        $stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_stickynote_table'] . ' SET title = ?, message = ? WHERE idstickynote = ? AND idgroup = ?');
        $stmt->bind_param('ssss', $title, $desc, $sid, $gid);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: EditStickyNote ' . $stmt->error);
        $stmt->close();
        return false;
    }

    /*     * ******************** END OF Sticky notes ********************* */

    public function AddPushDevice($userId, $regId, $platform, $devicenumber,$isEnabled) {
        global $settings;
        $stmt = $this->mysqli->prepare("INSERT INTO " . $settings['db_device_table'] . " VALUES (0,?,?,?,?,null,?);");
        $stmt->bind_param('isssi', $userId, $regId, $platform, $devicenumber,$isEnabled);
        //$stmt2 = $this->mysqli->prepare("DELETE FROM ".$settings['db_user_group_device_table']." WHERE registration_id LIKE '%delete%'");

        if ($stmt->execute()) {
            $idDevice = $stmt->insert_id;
            $stmt->close();
            return new Push_Device($idDevice, $userId, $regId, $platform, $devicenumber, null,$isEnabled);
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddPushDevice ' . $stmt->error);
        }
        $stmt->close();
        return null;
    }

    public function GetDeviceIdByNumber($deviceNumber) {
        global $settings;
        $query = 'SELECT iddevice, iduser, push_reg, platform, devicenumber, last_connected, is_enabled FROM ' . $settings['db_device_table'] . ' WHERE devicenumber = ?';
        //var_dump($query);
        $output = null;
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param('s', $deviceNumber);
            if (!$stmt->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDeviceId ' . $stmt->error);
                $stmt->close();
            } else {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($iddevice, $userId, $push_reg, $platform, $devicenumber, $last_connected, $isEnabled);
                    while ($stmt->fetch()) {
                        $output = new Push_Device($iddevice, $userId, $push_reg, $platform, $devicenumber, $last_connected, $isEnabled);
                    }
                }
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDeviceId ' . $stmt->error);
            $stmt->close();
        }
        $stmt->close();
        return $output;
    }

    public function SetupPushDeviceForGroup($iddevice, $idgroup, $iduser, $onof) {
        global $settings;
        $stmt = $this->mysqli->prepare("INSERT INTO " . $settings['db_user_group_device_table'] . " VALUES (?,?,?,1) ON DUPLICATE KEY UPDATE is_enabled = ?");
        $stmt->bind_param('iiii', $iduser, $idgroup, $iddevice, $onof);
        var_dump($iduser, $idgroup);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: SetupPushDeviceForGroup ' . $stmt->error);
        }
        $stmt->close();
        return false;
    }

    public function SetPushDeviceStatus($groupId, $userId, $iddevice, $isEnabled) {
        global $settings;

        $stmt = $this->mysqli->prepare("UPDATE " . $settings['db_user_group_device_table'] . " SET is_enabled = ? WHERE iduser = ? AND idgroup = ? AND iddevice = ?");
        $stmt->bind_param('iiii', $isEnabled, $userId, $groupId, $iddevice);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DisablePushDevice ' . $stmt->error);
        }
        $stmt->close();
        return false;
    }

    public function DeletePushDeviceStatus($groupId, $userId, $iddevice) {
        global $settings;

        $stmt = $this->mysqli->prepare("DELETE FROM " . $settings['db_user_group_device_table'] . " WHERE iduser = ? AND idgroup = ? AND iddevice = ?");
        $stmt->bind_param('iii',  $userId, $groupId, $iddevice);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeletePushDeviceStatus ' . $stmt->error);
        }
        $stmt->close();
        return false;
    }

    public function UpdateDeviceTime($iddevice) {
        global $settings;

        $stmt = $this->mysqli->prepare("UPDATE " . $settings['db_device_table'] . " SET last_connected = null WHERE iddevice = ?");
        $stmt->bind_param('i', $iddevice);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateDeviceTime ' . $stmt->error);
        }
        $stmt->close();
        return false;
    }

    public function MobileLoginToday($iduser) {
        global $settings;

        $stmt = $this->mysqli->prepare("UPDATE " . $settings['db_device_table'] . " SET last_connected = now() WHERE iduser = ?");
        $stmt->bind_param('i', $iduser);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: MobileLoginToday ' . $stmt->error);
        }
        $stmt->close();
        return false;
    }

    public function UpdateDevicePushReg($iddevice, $pushReg, $userid, $isEnabled) {
        global $settings;

        $stmt = $this->mysqli->prepare("UPDATE " . $settings['db_device_table'] . " SET push_reg = ?, iduser = ?, is_enabled = ? WHERE iddevice = ?");
        $stmt->bind_param('siii', $pushReg, $userid, $isEnabled, $iddevice);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateDevicePushReg ' . $stmt->error);
        }
        $stmt->close();
        return false;
    }

    public function GetUsersForPushNewStickyNote($groupId) {
        global $settings;
        $query = 'SELECT u.iduser, u.firstname, u.surname, u.preferred_language, d.iddevice, d.push_reg, d.platform, d.devicenumber, d.is_enabled, ugd.idgroup FROM ' . $settings['db_user_group_table'] . ' as ugd
        INNER JOIN ' . $settings['db_device_table'] . ' as d ON ugd.iduser = d.iduser AND ugd.idgroup = ? JOIN ' . $settings['db_user_table'] . ' as u ON d.iduser = u.iduser WHERE u.is_deleted = 0 AND d.is_enabled = 1'; // TODO: check of user niet is gedelete!

        $output = array();
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param('i', $groupId);
            if (!$stmt->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersForPushNewStickyNote ' . $stmt->error);
                $stmt->close();
            } else {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($idUser, $firstName, $surname, $preferredLanguage, $iddevice, $pushReg, $platform, $devicenumber,$isEnabled, $idGroup);
                    while ($stmt->fetch()) {
                        $PushDevice = new Push_Device($iddevice, $idUser, $pushReg, $platform, $devicenumber, null,$isEnabled);
                        $output[] = array(User::forPush($idUser, $firstName, $surname, $preferredLanguage, $PushDevice), $idGroup);
                    }
                }
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersForPushNewStickyNote ' . $stmt->error);
            $stmt->close();
        }
        $stmt->close();
        return $output;
    }

    public function GetUsersForPushDinnerUpdate() {
        global $settings;
        $query = 'SELECT u.iduser, u.firstname, u.surname, u.preferred_language, d.iddevice, d.push_reg, d.platform, d.devicenumber, d.is_enabled, ugd.idgroup FROM ' . $settings['db_user_group_table'] . ' as ugd
        INNER JOIN ' . $settings['db_device_table'] . ' as d ON ugd.iduser = d.iduser JOIN ' . $settings['db_user_table'] . ' as u ON d.iduser = u.iduser WHERE u.is_deleted = 0 AND d.is_enabled = 1 AND ugd.is_deleted = 0'; // TODO: check of user niet is gedelete!
        //var_dump($query);
        $output = array();
        if ($stmt = $this->mysqli->prepare($query)) {
            if (!$stmt->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersForPushTaskReminder ' . $stmt->error);
                $stmt->close();
            } else {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($idUser, $firstName, $surname, $preferredLanguage, $iddevice, $pushReg, $platform, $devicenumber,$isEnabled, $idGroup);
                    while ($stmt->fetch()) {
                        $PushDevice = new Push_Device($iddevice, $idUser, $pushReg, $platform, $devicenumber, null,$isEnabled);
                        $output[] = array(User::forPush($idUser, $firstName, $surname, $preferredLanguage, $PushDevice), $idGroup);
                    }
                }
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersForPushTaskReminder ' . $stmt->error);
            $stmt->close();
        }
        $stmt->close();
        return $output;
    }

    public function GetUsersForPushTaskReminder() {
        global $settings;
        $query = 'SELECT u.iduser, u.firstname, u.surname, u.preferred_language, d.iddevice, d.push_reg, d.platform, d.devicenumber, d.is_enabled, ug.idgroup FROM ' . $settings['db_user_group_table'] . ' as ug
        INNER JOIN ' . $settings['db_device_table'] . ' as d ON ug.iduser = d.iduser JOIN ' . $settings['db_user_table'] . ' as u ON d.iduser = u.iduser WHERE u.is_deleted = 0 AND d.is_enabled = 1 AND ug.is_deleted = 0'; // TODO: check of user niet is gedelete!
        //var_dump($query);
        $output = array();
        if ($stmt = $this->mysqli->prepare($query)) {
            if (!$stmt->execute()) {
                error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersForPushTaskReminder ' . $stmt->error);
                $stmt->close();
            } else {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($idUser, $firstName, $surname, $preferredLanguage, $iddevice, $pushReg, $platform, $devicenumber, $isEnabled, $idGroup);
                    while ($stmt->fetch()) {
                        $PushDevice = new Push_Device($iddevice, $idUser, $pushReg, $platform, $devicenumber, null, $isEnabled);
                        $output[] = array(User::forPush($idUser, $firstName, $surname, $preferredLanguage, $PushDevice), $idGroup);
                    }
                }
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersForPushTaskReminder ' . $stmt->error);
            $stmt->close();
        }
        $stmt->close();
        return $output;
    }

    public function getTaskIcal($gid, $uid) {
        global $settings;
        $query = "SELECT day_of_week, uniq_key FROM " . $settings['db_user_task_ical_table'] . " WHERE idgroup = ? AND iduser = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('ii', $gid, $uid);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($dayOfWeek, $unicKey);
                $stmt->fetch();
                $output[] = $dayOfWeek;
                $output[] = $unicKey;
                $stmt->Close();
                return $output;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: getTaskIcal ' . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function setTaskIcal($gid, $uid, $dayOfWeek, $uniqKey) {
        global $settings;
        $stmt = $this->mysqli->prepare("INSERT INTO " . $settings['db_user_task_ical_table'] . " VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE day_of_week = ?, uniq_key = ?");
        $stmt->bind_param('iiisis', $uid, $gid, $dayOfWeek, $uniqKey, $dayOfWeek, $uniqKey);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: setTaskIcal ' . $stmt->error);
        }
        $stmt->close();
        return false;
    }

    public function getIcalInfoByKey($key) {
        global $settings;
        $query = "SELECT iduser, idgroup, day_of_week FROM " . $settings['db_user_task_ical_table'] . " WHERE uniq_key = ?";

        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $key);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($iduser, $idgroup, $day_of_week);
                $stmt->fetch();
                $output[] = $iduser;
                $output[] = $idgroup;
                $output[] = $day_of_week;
                $stmt->Close();
                return $output;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: getIcalInfoByKey ' . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function GetChangeSets($lang) {
        global $settings;
        $query = "SELECT version, DATE_FORMAT(date, '%d-%m-%Y') as date, description FROM " . $settings['db_changesets_table'] . " WHERE lang = ? ORDER BY version DESC";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $lang);
        $output = array();
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($version, $date, $description);
                $count = 0;
                while ($stmt->fetch()) {
                    $output[$count][0] = $version;
                    $output[$count][1] = $description;
                    $output[$count][2] = $date;
                    $count++;
                }
                $stmt->close();
                return $output;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetChangeSets ' . $stmt->error);
        }
        $stmt->close();
        return null;
    }

    public function GetFAQ($lang) {
        global $settings;
        $query = "SELECT question,answer FROM " . $settings['db_faq_table'] . " WHERE lang = ? ORDER BY id";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $lang);
        $output = array();
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($question, $answer);
                $count = 0;
                while ($stmt->fetch()) {
                    $output[$count][0] = $question;
                    $output[$count][1] = $answer;
                    $count++;
                }
                $stmt->close();
                return $output;
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetFAQ ' . $stmt->error);
        }
        $stmt->close();
        return null;
    }
	
	public function GetUsersForActivationReminder() {
        global $settings;
        $query = "SELECT firstname, email, activation FROM " . $settings['db_user_table'] . " WHERE activation IS NOT NULL AND DATE(date_created) = DATE(NOW() - INTERVAL 4 DAY) AND is_deleted = 0";
		$output = array();
        if ($stmt = $this->mysqli->prepare($query)) {
            if (!$stmt->execute()) {
                error_log('DB ERROR: GetUsersForActivationReminder ' . $stmt->error);
            } else {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($firstName, $email, $activation);
                     while ($stmt->fetch()) {						
						$u = new User();
						$u->firstName = $firstName;
						$u->email = $email;
						$u->activationcode = $activation;

						$output[] = $u;
					}
				}
            }
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUsersForActivationReminder ' . $stmt->error);
            $stmt->close();
        }
        $stmt->close();
        return $output;
    }

    public function AddCheckout($idgroup) {
        global $settings;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_check_table'] . ' (id_group)VALUES( ? )');
        $stmt->bind_param('i', $idgroup);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddCheckout ' . $stmt->error);
            $stmt->close();
        }
        else {
            $checkId = $stmt->insert_id;
            $stmt->close();
            return $checkId;
        }
    }

    public function AddCheckoutData($idgroup,$idcheck,$idpayer, $idreceiver, $amount) {
        global $settings;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_checkout_table'] . ' (id_group,id_user_payer, id_user_receiver, amount,id_check)VALUES( ?, ?, ?, ?, ?)');
        $stmt->bind_param('iiisi', $idgroup,$idpayer,$idreceiver,$amount,$idcheck);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AddCheckoutData ' . $stmt->error);
            $stmt->close();
        }
        else {
            $stmt->close();
        }
    }

    public function GetCheckOutData($idCheck) {
        global $settings;
        $query = 'SELECT id_group, id_user_payer, id_user_receiver, amount  FROM ' . $settings['db_checkout_table'] . ' WHERE id_check = ?';
        $output = array();
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('i', $idCheck);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetCheckOutData ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id_group, $id_user_payer, $id_user_receiver, $amount);
                while ($stmt->fetch()) {
                    $output[] = new Checkout($id_group, $id_user_payer, $id_user_receiver, $amount, $idCheck);
                }
            }
        }

        $stmt->close();
        return $output;
    }

    public function GetCheckOutIds($idGroup) {
        global $settings;
        $query = 'SELECT idcheck, date  FROM ' . $settings['db_check_table'] . ' WHERE id_group = ?';
        $output = array();
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('i', $idGroup);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetCheckOutIds ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($idcheck, $date);
                $count = 0;
                while ($stmt->fetch()) {
                    $output[$count][0] = $idcheck;
                    $output[$count][1] = $date;
                    $count++;
                }
            }
        }

        $stmt->close();
        return $output;
    }
	
	/*
	tabel cooking_points
	id
	date 
	id_group
	chef_points
	*/
	// insert or update de chefpoints by gettings everybody that joins dinner count guests en chefs divide for chefpoints and insert in tabel
	public function InsertOrUpdateTotalChefpoints($groupId, $date)
	{
		global $settings;
		$chefCount = 0;
		$guestCount = 0;
		$chefPoints = 0;
		// foreach id get all userId's, role, guests from today where role = 1 or 2
		$users = $this->GetGroupDinners($date, $groupId);

		// check how many chefs
		if(isset($users)){
			foreach($users as $u){
				if($u[1] == 2){
					$chefCount++;
				}
				else{
					$guestCount = $guestCount + $u[2];
				}
			}
		}

		// divide number of guests for chef
		if($guestCount != 0 && $chefCount != 0){
			$chefPoints = $guestCount / $chefCount;
		}
		
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_dinner_table'] . '(date, idgroup, chef_points) VALUES
        (?,?,?) ON DUPLICATE KEY UPDATE chef_points = VALUES(chef_points)');
        $stmt->bind_param('sii', $date, $groupId, $chefPoints);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: InsertOrUpdateTotalEaters ' . $stmt->error);
        }
        //$id_cookingpoints = $stmt->insert_id;
        $stmt->close();
        //return $id_cookingpoints;
	}
	
	// update cookingpoints in database get total chefpoints get times eaten minus and you have chefpoints
	public function UpdateCookingPoints3($chefId, $groupId)
	{
        global $settings;
		$chefPoints = $this->GetTotalChefPoints($groupId, $chefId);
		$timesEaten = $this->GetTotalTimesJoinedEating($groupId,$chefId);
		
		$points = $chefPoints - $timesEaten;

		$stmt = $this->mysqli->prepare('UPDATE ' . $settings['db_user_group_table'] . ' SET cooking_point = ? WHERE idgroup = ? AND iduser = ?');
        $stmt->bind_param('sss', $points, $groupId, $chefId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateCookingPoints3 ' . $stmt->error);
        }
        $stmt->close();
	}
	
	// Tel chefpunten van de user met chefId
	public function GetTotalChefPoints($groupId, $chefId)
	{
		global $settings;
        $stmt = $this->mysqli->prepare('SELECT SUM(cp.chef_points) FROM ' . $settings['db_dinner_table'] . ' as cp JOIN ' .
		$settings['db_dinnerplanner_table'] . ' as dp ON dp.date_of_dinner = cp.date WHERE dp.dinner_role = 2 AND dp.idUser = ? AND cp.idgroup = ? AND dp.date_of_dinner < CURDATE()');

        $stmt->bind_param('ii', $chefId,$groupId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetTotalChefPoints ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($count);
                $chefPoints = 0;
                while ($stmt->fetch()) {
                    $chefPoints = $count;
                }
                $stmt->close();
                return $chefPoints;
            }
            $stmt->close();
            return null;
        }
	}
	
	// Get total times of joining dinner by counting number of persons because can join with more than one
	public function GetTotalTimesJoinedEating($gid,$uid)
	{
		global $settings;
		$stmt = $this->mysqli->prepare('SELECT SUM(number_of_persons) FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE idgroup = ? AND iduser = ? AND dinner_role = 1 AND date_of_dinner < CURDATE();');
        $stmt->bind_param('ii', $gid, $uid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetTotalTimesJoinedEating ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($count);
                $timesJoinedEating = 0;
                while ($stmt->fetch()) {
                    $timesJoinedEating = $count;
                }
                $stmt->close();
                return $timesJoinedEating;
            }
            $stmt->close();
            return null;
        }
	}
	
	// Temp function to instal the new cookingpoint mechanism
	public function GetDinnerDates($groupid)
	{
		global $settings;
        $stmt = $this->mysqli->prepare('SELECT date_of_dinner FROM ' . $settings['db_dinnerplanner_table'] . ' WHERE idgroup = ? GROUP BY date_of_dinner');
        
		$stmt->bind_param('i', $groupid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetDinnerDates ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($date);
                $dinnerDates = array();
                while ($stmt->fetch()) {
                    $dinnerDates[] = $date;
                }
                $stmt->close();
                return $dinnerDates;
            }
            $stmt->close();
            return null;
        }
	}
	
	public function InsertCostSubGroup($userIds,$groupName,$groupId)
	{
		global $settings;
		$costId = 0;
        $stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_cost_subgroup_table'] . ' (idgroup,name)VALUES( ?, ?)');
        $stmt->bind_param('is', $groupId, $groupName);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: InsertCostSubGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $costId = $stmt->insert_id;
            $stmt->close();
        }
		
		if($costId > 0){
			foreach($userIds as $uid)
			{
				$this->AdduserToCustSubGroup($uid,$costId);
			}
		}
	}
	
	public function AdduserToCustSubGroup($userId,$sgid)
	{
		global $settings;
		$stmt = $this->mysqli->prepare('INSERT INTO ' . $settings['db_cost_subgroup_user_table'] . ' (idsubgroup,iduser)VALUES( ?, ?)');
        $stmt->bind_param('ii', $sgid, $userId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: AdduserToCustSubGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->close();
        }
	}
	
	public function GetCostSubGroups($groupId)
	{
		global $settings;
		$subgoups = array();
        $query = "SELECT id,name FROM " . $settings['db_cost_subgroup_table'] . " WHERE idgroup = ? AND is_deleted = 0";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('i', $groupId);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetCostSubGroups ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $name);
                while ($stmt->fetch()) {
                    $subgoups[] = new Cost_Subgroup($id,$groupId,$name);
                }
                $stmt->Close();
            }
        }
		
		if(COUNT($subgoups) > 0)
		{
			foreach($subgoups as $sg)
			{
				$userIds = $this->GetUserIdsCostSubGroup($sg->id);
				$sg->userIds = $userIds;
			}
		}
		return $subgoups;
	}
	
	public function GetUserIdsCostSubGroup($sgid)
	{
		global $settings;
		$subgoups = array();
		$query = "SELECT iduser FROM " . $settings['db_cost_subgroup_user_table'] . " WHERE idsubgroup = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('i', $sgid);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: GetUserIdsCostSubGroup ' . $stmt->error);
            $stmt->close();
        } else {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id);
                while ($stmt->fetch()) {
                    $subgoups[] = $id;
                }
                $stmt->Close();
				
				return $subgoups;
            }
        }
	}
	
	public function DeleteCostSubGroup($idSubgroup)
	{
		global $settings;
        $stmt = $this->mysqli->prepare("UPDATE " . $settings['db_cost_subgroup_table'] . " SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param('i', $idSubgroup);
        if ($stmt->execute()) {
            $stmt->close();
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: DeleteCostSubGroup ' . $stmt->error);
			$stmt->close();
        }
	}
	
	public function UpdateCostSubGroup($idSubgroup, $idgroup, $userIds, $name)
	{
		global $settings;
        $stmt = $this->mysqli->prepare("UPDATE " . $settings['db_cost_subgroup_table'] . " SET name = ? WHERE id = ?");
        $stmt->bind_param('si', $name, $idSubgroup);
        if ($stmt->execute()) {
            $stmt->close();
        } else {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: UpdateCostSubGroup ' . $stmt->error);
			$stmt->close();
        }
		
		$this->RemoveUsersFromCostGroup($idSubgroup);
		
		foreach($userIds as $uid)
		{
			$this->AdduserToCustSubGroup($uid,$idSubgroup);
		}
	}
	
	public function RemoveUsersFromCostGroup($idSubgroup)
	{
		global $settings;
        $stmt = $this->mysqli->prepare('DELETE FROM ' . $settings['db_cost_subgroup_user_table'] . ' WHERE idsubgroup = ?');
        $stmt->bind_param('i', $idSubgroup);
        if (!$stmt->execute()) {
            error_log($_SERVER['REQUEST_URI'] . ' DB ERROR: RemoveUsersFromCostGroup ' . $stmt->error);
        } 
        $stmt->close();
	}
}
