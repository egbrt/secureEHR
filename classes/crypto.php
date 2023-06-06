<?php

// from https://dev.to/manuthecoder/really-simple-encryption-in-php-3kk9

define("hash_method", "sha256");
define("encryption_method", "AES-128-CBC");

class Crypto {
    public $valid = false;
    public $id;
    private $key;
    public $label;
    
    function __construct($key)
    {
        $i = stripos($key, ':');
        if ($i) {
            $this->valid = true;
            $this->id = substr($key, 0, $i);
            $pw = substr($key, $i+1);
            $this->key = hash_hmac(hash_method, $pw, $this->id);
            $this->label = hash_hmac(hash_method, $this->id, $this->key);
            /*
            echo "id= " . $this->id . "</br>";
            echo "pw= " . $pw . "</br>";
            echo "key= " . $this->key . "</br>";
            echo "label= " . $this->label . "</br>";
            */
        }
    }
    
    function encrypt($data)
    {
        $plaintext = $data;
        $ivlen = openssl_cipher_iv_length($cipher = encryption_method);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $this->key, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac(hash_method, $ciphertext_raw, $this->key, $as_binary = true);
        $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
        return $ciphertext;
    }
    
    function decrypt($data)
    {
        $c = base64_decode($data);
        $ivlen = openssl_cipher_iv_length($cipher = encryption_method);
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $this->key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac(hash_method, $ciphertext_raw, $this->key, $as_binary = true);
        if (hash_equals($hmac, $calcmac))
        {
            return $original_plaintext;
        }
    }
}
?>
