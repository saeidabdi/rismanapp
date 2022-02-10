<?php

namespace App\Http\Controllers;

use App\Mosh;
use App\Stu;
use Illuminate\Http\Request;

class FirebaseController extends Controller
{

    function __construct()
    {
    }

    public function SetTokenFirebaseStu(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];

        $stu = Stu::where('id', $stu_id)->first();

        $FirebaseToken = $request->FirebaseToken;

        $stu->FirebaseToken = $FirebaseToken;

        if ($stu->update())
            return $stu;
        else
            return 'no';
    }
    public function SetTokenFirebaseMosh(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $mosh = Mosh::where('code', $mosh_id)->first();
        // $FirebaseToken = $stu->FirebaseToken;
        $FirebaseToken = $request->FirebaseToken;
        $mosh->FirebaseToken = $FirebaseToken;
        $mosh->update();
        // $this->spn($FirebaseToken);
        return 'ok';
    }

    public function spn($token, $title, $text, $img = '')
    {
        if ($token != '') {
            if (!defined('API_ACCESS_KEY')) {
                define('API_ACCESS_KEY', 'AAAAyBWhesU:APA91bGTo3-CVaJ_Bt3I8dNWDQka8kLmSeKNsTV6xpBA9i8TXpUMnNRCmfcFrf3uOmoP0dRKD31QKWIb-dUgsQJLs_r6A5KoBbJyyhe72sMp-cF3L8hb4wiNgy2v6GAUmbMKKORzpcev');
                // define('API_ACCESS_KEY', 'AAAAlvR7Lf0:APA91bHV0iggrMW4BTkTyte1JptaH_dQ9G4aiJ8oax9a9XihqPwKPtzJIwduztoUh94UgiTcMDAdeAOcRNyQS3iS-U1ccYHljn2wUWePXMEEFyzufGEROQpREB7wNV9bg2uclXnYGyR-');
            }

            $fcmUrl = "https://fcm.googleapis.com/fcm/send";

            $token = $token;


            $notification = [
                'title' => '💬 ' . $title,
                'body' => $text,
                'icon' => 'ic_notification',
                "color" => "#166ee7",
                'sound' => 'default',
                // 'click_action' => 'chatPage'
            ];

            $extraNotificationData = ["message" => $notification, "moredata" => 'dd'];

            $fcmNotification = [
                //'registration_ids' => $tokenList, //multple token array
                'to'        => $token, //single token
                'notification' => $notification,
                'data' => $extraNotificationData
            ];

            $headers = [
                'Authorization: key=' . API_ACCESS_KEY,
                'Content-Type: application/json'
            ];


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fcmUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        // return $result;
    }

    public function spnMosh($token, $title, $text, $img = '')
    {
        if ($token != '') {
            if (!defined('API_ACCESS_KEY_mosh')) {

                define('API_ACCESS_KEY_mosh', 'AAAAyBWhesU:APA91bGTo3-CVaJ_Bt3I8dNWDQka8kLmSeKNsTV6xpBA9i8TXpUMnNRCmfcFrf3uOmoP0dRKD31QKWIb-dUgsQJLs_r6A5KoBbJyyhe72sMp-cF3L8hb4wiNgy2v6GAUmbMKKORzpcev');
                // define('API_ACCESS_KEY', 'AAAATotxe80:APA91bF6ZVynaLOLTyvFLpA1VrpYDpq1VY2O1jGhwzsQBAJDrKy_xO65iRlYFjujV5pBFYklm0oY2eBk01SZ7vSOwpu_POxgSIWcPIuSG8L9hPIu9xmOddjS1z_ADu7B0Un_Om0Ndhw0');
            }

            $fcmUrl = "https://fcm.googleapis.com/fcm/send";

            $token = $token;


            $notification = [
                'title' => '💬 ' . $title,
                'body' => $text,
                'icon' => 'ic_notification',
                "color" => "#166ee7",
                'sound' => 'default',
                // 'click_action' => 'chatPage'
            ];

            $extraNotificationData = ["message" => $notification, "moredata" => 'dd'];

            $fcmNotification = [
                //'registration_ids' => $tokenList, //multple token array
                'to'        => $token, //single token
                'notification' => $notification,
                'data' => $extraNotificationData
            ];

            $headers = [
                'Authorization: key=' . API_ACCESS_KEY_mosh,
                'Content-Type: application/json'
            ];


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fcmUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        // return $result;
    }
}
