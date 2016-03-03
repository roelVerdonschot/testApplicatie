<?php
class MCrypt
{
    private $iv = '0123456789abcdef'; #Same as in JAVA
    private $key = '0123456789abcdef'; #Same as in JAVA

    function encrypt($str) {

        //$key = $this->hex2bin($key);
        $iv = $this->iv;

        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

        mcrypt_generic_init($td, $this->key, $iv);
        $encrypted = mcrypt_generic($td, $str);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return bin2hex($encrypted);
    }

    function decrypt($code) {
        // $key = $this->hex2bin($key);
        $code = $this->hex2bin($code);
        $iv = $this->iv;

        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

        mcrypt_generic_init($td, $this->key, $iv);
        $decrypted = mdecrypt_generic($td, $code);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return utf8_encode(trim($decrypted));
    }

    protected function hex2bin($hexdata) {
        $bindata = '';

        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }

        return $bindata;
    }

    public function decryptWP($code)
    {
        $password = $this->key;
        $salt = 'HeavyPanther';

        $iv = $this->iv;

        $key = $this->pbkdf2($password, $salt, 1000, 32);
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key,base64_decode($code),MCRYPT_MODE_CBC,$iv);
    }


    private function pbkdf2($p, $s, $c, $dk_len, $algo = 'sha1') {

        // experimentally determine h_len for the algorithm in question
        static $lengths;
        if (!isset($lengths[$algo])) { $lengths[$algo] = strlen(hash($algo, null, true)); }
        $h_len = $lengths[$algo];

        if ($dk_len > (pow(2, 32) - 1) * $h_len) {
            return false; // derived key is too long
        } else {
            $l = ceil($dk_len / $h_len); // number of derived key blocks to compute
            $t = null;
            for ($i = 1; $i <= $l; $i++) {
                $f = $u = hash_hmac($algo, $s . pack('N', $i), $p, true); // first iterate
                for ($j = 1; $j < $c; $j++) {
                    $f ^= ($u = hash_hmac($algo, $u, $p, true)); // xor each iterate
                }
                $t .= $f; // concatenate blocks of the derived key
            }
            return substr($t, 0, $dk_len); // return the derived key of correct length
        }
    }

    function decryptIOS($base64Encrypted)
    {		
		$cryptor = new \RNCryptor\Decryptor();
		$str = $cryptor->decrypt($base64Encrypted, $this->key);
        return $str;
    }

}