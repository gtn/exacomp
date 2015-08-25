<?php

class exabis_special_id_generator {
    /*
    generates a 25 digit id
    21 digits = unique id (base 64 = A-Za-z0-9_-)
    4 digits = checksum (crc32 of id in base 64)
    */
    
    const ID_LENGTH = 21;
    const CHECK_LENGTH = 4;
    const BASE = 64;
    private static $BASE64 = array(
                "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
                "a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
                "0","1","2","3","4","5","6","7","8","9","_","-");
    
    /* from http://php.net/manual/de/function.base-convert.php */
    static private function str_baseconvert($str, $frombase=10, $tobase=36) { 
        $str = trim($str); 
        if (intval($frombase) != 10) { 
            $len = strlen($str); 
            $q = 0; 
            for ($i=0; $i<$len; $i++) { 
                $r = base_convert($str[$i], $frombase, 10); 
                $q = bcadd(bcmul($q, $frombase), $r); 
            } 
        } 
        else $q = $str; 

        if (intval($tobase) != 10) { 
            $s = ''; 
            while (bccomp($q, '0', 0) > 0) { 
                $r = intval(bcmod($q, $tobase)); 
                if ($tobase == 64) {
                    $s = self::$BASE64[$r].$s;
                } else {
                    $s = base_convert($r, 10, $tobase) . $s; 
                }
                $q = bcdiv($q, $tobase, 0); 
            } 
        } 
        else $s = $q; 

        return $s; 
    }
    
    // make a string longer/shorter but cutting, or adding zeros to the left
    static private function make_length($str, $len) {
        return str_pad(substr($str, -$len), $len, self::BASE == 64 ? self::$BASE64[0] : "0" , STR_PAD_LEFT);
    }

    static private function generate_checksum($id) {
        $check = self::str_baseconvert(abs(crc32($id)), 10, self::BASE);
        $check = self::make_length($check, self::CHECK_LENGTH);
        
        return $check;
    }
    static public function generate_random_id() {
        $md5 = md5(microtime(false));
        $id = self::make_length(self::str_baseconvert($md5, 16, self::BASE), self::ID_LENGTH);

        return $id.self::generate_checksum($id);
    }

    static public function validate_id($id) {
        if (strlen($id) !== self::ID_LENGTH+self::CHECK_LENGTH) return false;

        $check = substr($id, self::ID_LENGTH);
        $id = substr($id, 0, self::ID_LENGTH);
        
        return self::generate_checksum($id) === $check;
    }
}

/*
echo "<pre>";

for ($i = 0; $i< 10000; $i++) {
    $id = exabis_special_id_generator::generate_random_id();
    
    echo 'id: '.$i.' '.$id.' '.(exabis_special_id_generator::validate_id($id)?'':' bad no ').'<br />';
}
*/
