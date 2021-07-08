<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Lib\zarinpal;
use App\Lib\bitpay;
use App\Sms;
use App\Stu;
use DB;
use nusoap_client;
use Session;

class CodeController extends Controller
{
    public $price = 50000;
    protected $status_pay = 0;

    public function index()
    {
        return 'ss';
    }
    public function payCode($mobile, $PriceSymbol)
    {
        if ($PriceSymbol == 213) {

            $price = $this->price;
            $mail = 'www.dasaeid.a123@gmail.com';
            Session::put('mobile', $mobile);
            // ارسال کاربر به درگاه
            $zaring = new zarinpal();
            $res = $zaring->pay($price, $mail, $mobile, 1);
            return redirect('https://www.zarinpal.com/pg/StartPay/' . $res);
        }
        return 'no pay';
    }

    public function payback(Request $request)
    {

        // $MerchantID = '5e682ada-3b69-11e8-aaf3-005056a205be';
        $MerchantID = '90671c3c-59b9-4b8f-984d-7849c52eb5dc';
        $Authority = $request->get('Authority');

        //ما در اینجا مبلغ مورد نظر را بصورت دستی نوشتیم اما در پروژه های واقعی باید از دیتابیس بخوانیم
        $Amount = $this->price;
        if ($request->get('Status') == 'OK') {
            $client = new nusoap_client('https://www.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
            $client->soap_defencoding = 'UTF-8';

            //در خط زیر یک درخواست به زرین پال ارسال می کنیم تا از صحت پرداخت کاربر مطمئن شویم
            $result = $client->call('PaymentVerification', [
                [
                    //این مقادیر را به سایت زرین پال برای دریافت تاییدیه نهایی ارسال می کنیم
                    'MerchantID'     => $MerchantID,
                    'Authority'      => $Authority,
                    'Amount'         => $Amount,
                ],
            ]);

            if ($result['Status'] == 100) {
                $mobile = Session::get('mobile');
                $stu = Stu::where('mobile', $mobile)->first();
                $new_random = new Sms;
                $new_random->stu_id = $stu->id;
                $new_random->mobile = $mobile;
                $new_random->code = rand(99999, 1000000);
                $new_random->err = 0;
                $new_random->time_added = time();
                if ($new_random->save()) {
                    $this->status_pay = 1;
                    $this->transaction_send($mobile, $result['RefID'], $this->price, 0);
                    Session::forget('mobile');
                    // ارسال پیامک به شماره دانش آموز
                    try {
                        $api = new \Kavenegar\KavenegarApi("7A71544551417865657250637655412F616E4D54617146454159347A59672F33");
                        $sender = "10008663";
                        $message = $new_random->code;
                        $receptor = $mobile;
                        $result2 = $api->VerifyLookup($receptor, $message, '', '', 'verify');
                        // $result = $api->Send($sender, $receptor, $message);

                    } catch (\Kavenegar\Exceptions\ApiException $e) {
                        echo $e->errorMessage();
                    } catch (\Kavenegar\Exceptions\HttpException $e) {
                        echo $e->errorMessage();
                    }
                    return view('pay',['mes'=>'در صورت وجود هرگونه مشکل با شماره ی 09100045125 در پیامرسان واتساپ در ارتباط باشید','result' => $result,'mes2' => 'پرداخت با موفقیت انجام شد']);
                    // return response(['mes' => 'پرداخت با موفقیت انجام شد', 'result' => $result]);
                }
            } else {
                // return response()->json(['mes' => 'خطا در انجام عملیات1', 'result' => $result]);
                return view('pay',['mes'=>'در صورت وجود هرگونه مشکل با شماره ی 09100045125 در پیامرسان واتساپ در ارتباط باشید','result' => $result,'mes2' => 'خطا در انجام عملیات']);
            }
        } else {
            $result = array("Status"=>121);
            return view('pay', ['mes' => 'در صورت وجود هرگونه مشکل با شماره ی 09100045125 در پیامرسان واتساپ در ارتباط باشید', 'result' => $result, 'mes2' => 'پرداخت لغو شده است']);
        }
    }

    public function paybit($mobile, $PriceSymbol)
    {
        if ($PriceSymbol == 213) {
            Session::put('mobile', $mobile);
            $url = 'https://bitpay.ir/payment-test/gateway-send';
            $api = 'adxcv-zzadq-polkjsad-opp13opoz-1sdf455aadzmck1244567';
            $amount = $this->price;
            $redirect = url('/api/backbit');
            $name = 'ورود به اپلیکیشن';
            $email = 'www.dasaeid.a123@gmail.com';
            $description = 'خرید اشتراک ورود به اپلیکیشن';
            $factorId = time() . uniqid();
            $result = bitpay::send($url, $api, $amount, $redirect, $factorId, $name, $email, $description);

            if ($result > 0 && is_numeric($result)) {
                $go = 'http://bitpay.ir/payment-test/gateway-' . $result;
                return redirect($go);
            }
            return 'no';
        }
    }

    public function backbit(Request $request)
    {
        $url = 'http://bitpay.ir/payment-test/gateway-result-second';
        $api = 'adxcv-zzadq-polkjsad-opp13opoz-1sdf455aadzmck1244567';
        $trans_id = $request->trans_id;
        $id_get = $request->id_get;

        $result = bitpay::get($url, $api, $trans_id, $id_get);

        $mobile = Session::get('mobile');
        if ($result == 1) {
            $this->status_pay = 1;
            $this->transaction_send($mobile, $id_get, $this->price, 0);
            $stu = Stu::where('mobile', $mobile)->first();
            $new_random = new Sms;
            $new_random->stu_id = $stu->id;
            $new_random->mobile = $mobile;
            $new_random->code = rand(99999, 1000000);
            $new_random->err = 0;
            $new_random->time_added = time();
            if ($new_random->save()) {
                Session::forget('mobile');
                // ارسال پیامک به شماره دانش آموز
                try {
                    $api = new \Kavenegar\KavenegarApi("7A71544551417865657250637655412F616E4D54617146454159347A59672F33");
                    $sender = "10008663";
                    $message = " کد ورود اپلیکیشن " . $this->appname . " : " .
                        $new_random->code;
                    $receptor = $mobile;
                    $result = $api->Send($sender, $receptor, $message);

                    return response()->json([
                        'success' => 'yes',
                        'trans_id' => $trans_id,
                        'id_get' => $id_get,
                    ]);
                } catch (\Kavenegar\Exceptions\ApiException $e) {
                    echo $e->errorMessage();
                } catch (\Kavenegar\Exceptions\HttpException $e) {
                    echo $e->errorMessage();
                }
            }
        } else {
            $this->status_pay = 0;
            $this->transaction_send($mobile, $id_get, $this->price, 0);
            return response()->json([
                'success' => 'no',
                'trans_id' => $trans_id,
                'id_get' => $id_get,
                'mobile' => Session::get('mobile')
            ]);
        }
    }

    public function transaction_send($mobile, $id_get, $price, $kind)
    {
        $stu = Stu::where('mobile', $mobile)->first();

        if ($stu) {
            $trans = DB::table('transaction')->insert([
                'stu_id' => $stu->id,
                'price' => $price,
                'status' => $this->status_pay,
                'code' => $id_get,
                'kind' => $kind,
                'date_time' => time(),
            ]);
        }
    }
}
