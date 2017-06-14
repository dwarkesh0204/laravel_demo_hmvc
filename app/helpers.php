<?php

    function getToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
        }

        return $token;
    }

    function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }
function auditTrackRecord($data)
{
    $user_id = $data['user_id'];
    if ($data['user_id'] == '' || $data['section'] == '' || $data['action'] == '' || $data['time_stamp'] == '' || $data['device_type'] == '')
    {
        return false;
    }
    else
    {
        $data_str = "(".$data['user_id'].",'".$data['section']."','".$data['action']."','".$data['time_stamp']."','".$data['device_id']."','".$data['device_type']."')";

        // Insert contacts For Audit Track report
        //$insert_data = DB::connection('odbc')->statement("INSERT INTO TABLE indolytics.audit_track_report VALUES $data_str");

        if (!empty($insert_data)) {
            return true;
            /*return $this->response->array([
                'message'     => "Your Data sent to Audit Track Record.",
                'status_code' => 200
            ]);*/
        }else{
            return false;
            /*return $this->response->array([
                'message'     => "Please send proper data.",
                'status_code' => 400
            ]);*/
        }
    }
}
