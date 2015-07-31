<?php

class exabis_special_id_generator {
    /*
    generates a 30 digit id
    25 digits = unique id (base 36 = a-z0-9)
    5 digits = checksum (crc32 of id in base 36)
    */
    
    const ID_LENGTH = 25;
    const CHECK_LENGTH = 5;

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
                $s = base_convert($r, 10, $tobase) . $s; 
                $q = bcdiv($q, $tobase, 0); 
            } 
        } 
        else $s = $q; 

        return $s; 
    }

    // make a string longer/shorter but cutting, or adding zeros to the left
    static private function make_length($str, $len) {
        return str_pad(substr($str, -$len), $len, "0" , STR_PAD_LEFT);
    }

    static private function generate_check($id) {
        $check = base_convert(abs(crc32($id)), 10, 36);
        $check = self::make_length($check, self::CHECK_LENGTH);
        
        return $check;
    }
    static public function generate_id() {
        $md5 = md5('some random string '.microtime(false));
        $id = self::make_length(self::str_baseconvert($md5, 16, 36), self::ID_LENGTH);

        // return whole id (id + checksum)
        return $id.self::generate_check($id);
    }

    static public function check_id($id) {
        // is correct length?
        if (strlen($id) !== self::ID_LENGTH+self::CHECK_LENGTH) return false;

        // explode parts
        $check = substr($id, self::ID_LENGTH);
        $id = substr($id, 0, self::ID_LENGTH);
        
        // check it
        return (self::generate_check($id) === $check);
    }
}

/*
echo "<pre>";

for ($i = 0; $i< 10000; $i++) {
    $id = exabis_special_code_generator::generate_id();
    
    // if (!check_id($id))
    echo 'id: '.$i.' '.$id.' '.(my_code::check_id($id)?'':' bad no ').'<br />';
}
*/
