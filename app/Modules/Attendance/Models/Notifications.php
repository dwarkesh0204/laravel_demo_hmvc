<?php
namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Projects;
use Config;

class Notifications extends Model
{
    protected $fillable = ['subject', 'notification'];
    protected $table = 'notifications';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_attendance_database_conn');
    }

    /**
     * iphone push notification
     *
     * @param   [type]  $options  contains the value of options
     *
     * @return  it will return a value
     */
    public function sendIphonePushNotification($options){
        //$server = 'ssl://gateway.push.apple.com:2195';
        $server = 'ssl://gateway.sandbox.push.apple.com:2195';

        $keyCertFilePath = public_path('certificates/');
        $keyCertFilePath .= 'apns-dev.pem';

        // Construct the notification payload
        $body = array();
        $body['aps']          = $options['aps'];
        $body['aps']['badge'] = (isset($options['aps']['badge']) && !empty($options['aps']['badge'])) ? $options['aps']['badge'] : 1;
        $body['aps']['sound'] = (isset($options['aps']['sound']) && !empty($options['aps']['sound'])) ? $options['aps']['sound'] : 'default';
        $payload              = json_encode($body);

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $keyCertFilePath);
        $fp  = stream_socket_client($server, $error, $errorString, 60, STREAM_CLIENT_CONNECT, $ctx);


        if (!$fp){
            print "Failed to connect " . $error . " " . $errorString;
            return;
        }

        $msg = chr(0) . pack("n", 32) . pack('H*', str_replace(' ', '', $options['device_token'])) . pack("n", strlen($payload)) . $payload;
        fwrite($fp, $msg);
        fclose($fp);
    }

    /**
     * android push notification
     *
     * @param   [type]  $options  contains the value of options
     *
     * @return  void
     */
    public function sendAndroidPushNotification($options){
        $API_KEY='AIzaSyAvhcp-UW9IZN7_8cAD3sKDGLnnZxVEHBs';
        $options['data']['badge'] = (isset($options['data']['badge']) && !empty($options['data']['badge'])) ? $options['data']['badge'] : 1;
        $url    = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'to'           => $options['registration_ids'],
            'notification' => array('title' => 'Test', 'body' => $options['data']['message']),
            'data'         => $options['data']
        );


        $headers = array('Authorization: key='.$API_KEY,'Content-Type: application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === false){
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);// Close connection
    }
}
