<?php

namespace App\Services\Api;
//use Illuminate\Support\Facades\Crypt;

use Exception;

class EncryptService
{
    public function encryptParameter(): void
    {
        try {
            $crypt = new \Illuminate\Encryption\Encrypter('o91d4d7y03mxuechyp2273ckits9rff5', 'aes-256-cbc');

            /* $post = $request->all();
             $encrypted = Crypt::encryptString(json_encode($post));
             $dcrypted = Crypt::decryptString($encrypted);
             return json_decode($dcrypted);*/
            // echo json_decode("U2FsdGVkX1+gtCl8cmBaGmLcb7XdXb3ahTF22cT6SV0=");
            //  echo  $st = $crypt->encryptString('testbac',false);
            //  echo $crypt->decryptString($st,false);
            // echo $this->cryptoJsAesDecrypt('
            //eyJjdCI6IlhHbnNiNlFsNGZpUlhFemRMUVJiY2c9PSIsIml2IjoiMjlhZWUxMWRiZTM2MGE4OThjZGEyYmJjMjBiZmUxOGEiLCJzIjoiNzIzNDI3ZTZmMWIzMWYxNCJ9','U2FsdGVkX1/6qt7GYEFuf1NN930FEyUPzAatgoldcGs=');
            echo $string = $this->encrypt_parameter('TEST STRING');
            echo '</br>';
            echo 'AA=' . $this->decryptParameter($string);
            echo '</br>';
            echo $string = $this->encrypt_parameter('TE');
            // echo 'AES='.AES_ENCRYPT('TEST STRING', 'U2FsdGVkX1/6qt7GYEFuf1NN930FEyUPzAatgoldcGs=');

            $key = "ABCDEF0123456789";

            $plaintext = "This string was AES-128 / EBC / ZeroBytePadding encrypted.";
// Optionally UTF-8 encode
            $plaintext_utf8 = utf8_encode($plaintext);
// Find out what's your padding
            echo $pad_len = 16 - (strlen($plaintext_utf8) % 16);
// Padd your text
            echo '++=' . $plaintext_utf8 = str_pad($plaintext_utf8, (16 * (floor(strlen($plaintext_utf8) / 16) + 1)), chr($pad_len));

// Encryption
            /*mt_srand();
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
            mcrypt_generic_init($td, $key, false);
            // Generates a warning about empty IV but it's Ok
            $ciphertext = mcrypt_generic($td, $plaintext_utf8);
            mcrypt_generic_deinit($td);
           echo $ciphertext = mysql_real_escape_string($ciphertext);*/

        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }

    }

    function cryptoJsAesEncrypt($passphrase, $value)
    {
        try {
            /* $salt = openssl_random_pseudo_bytes(8);
              $salted = '';
              $dx = '';
              while (strlen($salted) < 48) {
                  $dx = md5($dx.$passphrase.$salt, true);
                  $salted .= $dx;
              }
              $key = substr($salted, 0, 32);
              $iv  = substr($salted, 32,16);*/
            /*$encrypted_data = openssl_encrypt(json_encode($value), 'aes-256-cbc', $key, true, $iv);
            $data = array("ct" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "s" => bin2hex($salt));
            return json_encode($data);
            */
            return $encryption = openssl_encrypt($value, 'aes-256-cbc', $passphrase, 0, 'WEz5scvzLdN4tzil');
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    function cryptoJsAesDecrypt($jsonString, $passphrase)
    {
        try {
            $jsonString = base64_decode($jsonString);
            $jsondata = json_decode($jsonString, true);
            //   echo '<pre>'; print_r($jsondata );
            $salt = hex2bin($jsondata["s"]);
            $ct = base64_decode($jsondata["ct"]);
            $iv = hex2bin($jsondata["iv"]);
            $concatedPassphrase = $passphrase . $salt;
            $md5 = array();
            $md5[0] = md5($concatedPassphrase, true);
            $result = $md5[0];
            for ($i = 1; $i < 3; $i++) {
                $md5[$i] = md5($md5[$i - 1] . $concatedPassphrase, true);
                $result .= $md5[$i];
            }
            $key = substr($result, 0, 32);
            $data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
            return json_decode($data, true);
            //  return $decryption=openssl_decrypt ($jsonString, 'aes-256-cbc', $passphrase, 0, 'WEz5scvzLdN4tzil');
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function encrypt_parameter($string)
    {
        try {
            return $this->cryptoJsAesEncrypt('U2FsdGVkX1/6qt7GYEFuf1NN930FEyUPzAatgoldcGs=', $string);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function decryptParameter($string)
    {
        try {
            return $this->cryptoJsAesDecrypt($string, 'U2FsdGVkX1/6qt7GYEFuf1NN930FEyUPzAatgoldcGs=');
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }

    }
}
