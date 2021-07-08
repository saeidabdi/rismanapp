<?php

namespace App\Http\Controllers;

use App\Mosh;
use App\Stu;
use Illuminate\Http\Request;

class FirebaseController extends Controller
{
    public function SetTokenFirebaseStu(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];
        $stu = Stu::where('id', $stu_id)->first();
        // $FirebaseToken = $stu->FirebaseToken;
        $FirebaseToken = $request->FirebaseToken;
        $stu->FirebaseToken = $FirebaseToken;
        $stu->update();
        // $this->spn($FirebaseToken);
        return 'ok';
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
                define('API_ACCESS_KEY', 'keyyyyyyyyyyyy');
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
}
