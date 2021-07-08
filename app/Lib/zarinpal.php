<?php

namespace App\lib;

use DB;
/*require_once 'nusoap.php';*/
use nusoap_client;

class zarinpal
{
    public $MerchantID;
    public function __construct()
    {
        // $this->MerchantID = "5e682ada-3b69-11e8-aaf3-005056a205be";
        $this->MerchantID = "90671c3c-59b9-4b8f-984d-7849c52eb5dc";
    }
    public function pay($Amount, $Email, $Mobile, $s = "")
    {
        $Description = 'فروش دفتر برنامه ریزی';  // Required
        if ($s == 1) {
            $CallbackURL = url('/api/payback'); // Required
        } else {
            $CallbackURL = url('/api/buyback'); // Required
        }


        $client = new nusoap_client('https://www.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call('PaymentRequest', [
            [
                'MerchantID'     => $this->MerchantID,
                'Amount'         => $Amount,
                'Description'    => $Description,
                'Email'          => $Email,
                'Mobile'         => $Mobile,
                'CallbackURL'    => $CallbackURL,
            ],
        ]);

        //Redirect to URL You can do it also by creating a form
        if ($result['Status'] == 100) {
            // $result['stu_id'] = $stu_id;
            return $result['Authority'];
        } else {
            // $result['stu_id'] = $stu_id;
            return false;
        }
    }
}
