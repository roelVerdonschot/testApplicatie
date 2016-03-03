<?php

class User {

    // field declaration
    public $id;
    public $firstName;
    public $surname;
    public $email;
    public $zipcode;
    public $address;
    public $city;
    public $country;
    public $dateOfBirth;
    public $bankAccount;
    public $school;
    public $profilePicture;
    public $password;
    public $preferredLanguage;
    public $activationcode;
    public $dateCreated;
    public $amount;
    public $pushDevice; // push
    public $terms_accepted;

    function __construct() {
        
    }

    public static function withUser($id, $firstName, $surname, $email, $zipcode, $address, $city, $country, $dateOfBirth, $bankAccount, $preferredLanguage, $profilePicture, $school, $dateCreated,$terms_accepted) {
        $instance = new User();
        $instance->loadUser($id, $firstName, $surname, $email, $zipcode, $address, $city, $country, $dateOfBirth, $bankAccount, $preferredLanguage, $profilePicture, $school, $dateCreated,$terms_accepted);
        return $instance;
    }

    public static function forEmail($idUser, $firstName, $surname, $preferredLanguage, $email) {
        $instance = new User();
        $instance->loadEmail($idUser, $firstName, $surname, $preferredLanguage, $email);
        return $instance;
    }

    public static function forPush($idUser, $firstName, $surname, $preferredLanguage, $pushDevice) {
        $instance = new User();
        $instance->loadPush($idUser, $firstName, $surname, $preferredLanguage, $pushDevice);
        return $instance;
    }

    protected function loadPush($idUser, $firstName, $surname, $preferredLanguage, $pushDevice) {
        $this->id = $idUser;
        $this->firstName = $firstName;
        $this->surname = $surname;
        $this->preferredLanguage = $preferredLanguage;
        $this->pushDevice = $pushDevice;
    }

    public static function withNewUser($firstname, $email, $password) {
        $instance = new User();
        $instance->loadNewUser($firstname, $email, $password);
        return $instance;
    }

    public static function withNameId($id, $name) {
        $instance = new User();
        $instance->loadNameId($id, $name);
        return $instance;
    }

    public static function withFullNameId($id, $name, $surname) {
        $instance = new User();
        $instance->loadFullNameId($id, $name, $surname);
        return $instance;
    }

    protected function loadNameId($id, $name) {
        $this->id = $id;
        $this->firstName = $name;
    }

    protected function loadFullNameId($id, $name, $surname) {
        $this->id = $id;
        $this->firstName = $name;
        $this->surname = $surname;
    }

    protected function loadEmail($idUser, $firstName, $surname, $preferredLanguage, $email) {
        $this->id = $idUser;
        $this->firstName = $firstName;
        $this->surname = $surname;
        $this->preferredLanguage = $preferredLanguage;
        $this->email = $email;
    }

    protected function loadUser($id, $firstName, $surname, $email, $zipcode, $address, $city, $country, $dateOfBirth, $bankAccount, $preferredLanguage, $profilePicture, $school, $dateCreated,$terms_accepted) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->surname = $surname;
        $this->email = $email;
        $this->zipcode = $zipcode;
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->dateOfBirth = $dateOfBirth;
        $this->bankAccount = $bankAccount;
        $this->school = $school;
        $this->profilePicture = $profilePicture;
        $this->preferredLanguage = $preferredLanguage;
        $this->dateCreated = $dateCreated;
        $this->terms_accepted = $terms_accepted;
    }

    protected function loadNewUser($firstname, $email, $password) {
        $this->firstName = $firstname;
        $this->email = $email;
        $this->password = $password;
    }

    public static function withIdAmount($id, $amount) {
        $instance = new User();
        $instance->loadIdAmount($id, $amount);
        return $instance;
    }

    protected function loadIdAmount($id, $amount) {
        $this->id = $id;
        $this->amount = $amount;
    }

    public function setProfilePicture($profilePicture) {
        $this->profilePicture = $profilePicture;
    }

    public function getProfilePicture() {
        return $this->profilePicture;
    }

    // methods
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getFullName() {
        if (isset($this->surname)) {
            return $this->firstName . " " . $this->surname;
        } else {
            return $this->firstName;
        }
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setFirstname($firstname) {
        $this->firstName = $firstname;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function setSurname($name) {
        $this->surname = $name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getZipcode() {
        return $this->zipcode;
    }

    public function setZipcode($zipcode) {
        $this->zipcode = $zipcode;
    }

    public function getAddress() {
        return $this->address;
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    public function getCity() {
        return $this->city;
    }

    public function setCity($city) {
        $this->city = $city;
    }

    public function getCountry() {
        return $this->country;
    }

    public function setCountry($country) {
        $this->country = $country;
    }

    public function getDateOfBirth() {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth($date) {
        $this->dateOfBirth = $date;
    }

    public function getBankAccount() {
        return $this->bankAccount;
    }

    public function setBankAccount($number) {
        $this->bankAccount = $number;
    }

    public function getSchool() {
        return $this->school;
    }

    public function setSchool($school) {
        $this->school = $school;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setDateCreated($datecreated) {
        $this->dateCreated = $datecreated;
    }

    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function setPreferredLanguage($preferredLanguage) {
        $this->preferredLanguage = $preferredLanguage;
    }

    public function getPreferredLanguage() {
        return $this->preferredLanguage;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function editPreferredLanguage($lang) {
        global $DBObject;
        $DBObject->ChangePreferredLanguage($lang, $this->id);
    }

    public function GetUserNameById($uid) {
        global $DBObject;
        return $DBObject->GetUserNameById($uid);
    }

    public function GetUserIdByEmail($email) {
        global $DBObject;
        return $DBObject->GetUserIdByEmail($email);
    }

    public function DeleteUserFromInvite($email, $groupId, $userId) {
        global $DBObject;
        return $DBObject->DeleteUserFromInvite($email, $groupId, $userId);
    }

    public function EditUserData($user) {
        global $DBObject;
        $DBObject->EditUserData($user);
    }

    public function EditEmail($email, $uid) {
        global $DBObject;
        $DBObject->EditEmail($email, $uid);
    }

    public function EditPassword($oldpass, $newpass, $uid) {
        global $DBObject;
        return $DBObject->EditPassword($oldpass, $newpass, $uid);
    }

    public function CheckEmail($email) {
        global $DBObject;
        return $DBObject->CheckEmail($email);
    }

    public function AddUser($firstname, $email, $pass, $shaemail, $langCode, $terms_accepted = false) {
        global $DBObject;
        $DBObject->AddUser($firstname, $email, $pass, $shaemail, $langCode, $terms_accepted);
    }

    public function ResetPassword($email) {
        global $DBObject;
        return $DBObject->ResetPassword($email);
    }

    public function CheckResetCode($ac) {
        global $DBObject;
        return $DBObject->CheckResetCode($ac);
    }

    public function ChangePassword($pass, $ac) {
        global $DBObject;
        $DBObject->ChangePassword($pass, $ac);
    }

    public static function getUserByAC($activationcode) {
        global $DBObject;
        return $DBObject->GetUserByAC($activationcode);
    }

    public static function activateUser($activationcode) {
        global $DBObject;
        return $DBObject->ActivateUser($activationcode);
    }

    public function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function ShowSettingsLeftSidebar($selected) {
        global $settings;
        $instant = new Group();
        $myGroups = $instant->GetMyGroup($this->id);
        echo '  <div class="l-box left-sidebar pure-u-1-4">
            <ul>
                <li' . ($selected == "account" ? ' class="selectedUser"' : '') . '><a href="' . $settings['site_url'] . 'settings/">Account gegevens</a></li>
                <li' . ($selected == "login" ? ' class="selectedUser"' : '') . '><a href="' . $settings['site_url'] . 'settings-login/">Login gegevens</a></li>';
        if (count($myGroups) > 0) {
            echo '
                <li>Groepsinstellingen</li>';

            foreach ($myGroups as $group) {
                echo '<li' . ($selected == $group->id ? ' class="selectedUser"' : '') . '><a href="' . $settings['site_url'] . 'settings-group/' . $group->id . '/">- ' . $group->name . '</a></li>
                ';
            }
        }
        echo '
            </ul>
        </div>';
    }

}

?>
