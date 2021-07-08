<?php

namespace App\Http\Controllers;

use App\AutomatiMessaging;
use App\helper\Tools;
use App\Lesson;
use App\Mosh;
use App\MoshSlider;
use App\Stu;
use Illuminate\Http\Request;
use DB;

class MoshController extends Controller
{
    public $maxerr = 15;
    // دریافت پیام و اسلایدر مشاور
    public function get_ms_mosh(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $message = $request->message;

        $mosh = Mosh::where('code', $mosh_id)->update([
            'message' => $message,
        ]);

        return 'ok';
    }

    // بخش مربوط به حالت اتوماتیک
    public function get_automati_message(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $all = AutomatiMessaging::where('mosh_id', $mosh_id)->get();

        foreach ($all as $key => $value) {
            $all[$key]->gradeF = explode(';', $value->grade)[0];
            $all[$key]->gradeT = explode(';', $value->grade)[1];
        }


        return response()->json(['all' => $all]);
    }

    public function send_automati_message(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $data = json_decode($request->data);
        $all_auto = AutomatiMessaging::where('mosh_id', $mosh_id)->delete();
        foreach ($data as $key => $value) {
            $automatic = new AutomatiMessaging;
            $automatic->mosh_id = $mosh_id;
            $automatic->message = $value[1];
            $automatic->grade = $value[0];
            $automatic->save();
        }
        return 'ok';
    }

    public function switch_auto(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $mosh = Mosh::where('code', $mosh_id)->first();
        $mosh->auto == 0 ?
            $mosh->auto = 1 :
            $mosh->auto = 0;
        $mosh->update();
    }

    // ثبت نام ورود
    public function mobile_mosh(Request $request)
    {
        $mobile = $request->mobile;
        $mosh = Mosh::where('mobile', $mobile)->first();
        // شماره وجود داشته
        if ($mosh) {
            return response()->json(['status' => $mosh->status, 'mosh_id' => $mosh->code]);
        }
        // شماره وجود نداشته یعنی ثبت نام
        else {
            $new_mosh = new Mosh;
            $new_mosh->mobile = $mobile;
            $new_mosh->code = $this->mosh_code();
            $new_mosh->rest = 0;
            $new_mosh->message = 'پیام روزانه ی مشاور';
            $new_mosh->status = 0;
            $new_mosh->auto = 0;
            $new_mosh->time_added = time();
            $new_mosh->err = 0;
            if ($new_mosh->save()) {
                // ارسال پیامک به شماره دانش آموز
                try {
                    $api = new \Kavenegar\KavenegarApi("7A71544551417865657250637655412F616E4D54617146454159347A59672F33");
                    $sender = "10008663";
                    $message =  $new_mosh->code;
                    $receptor = $mobile;
                    // $result = $api->Send($sender, $receptor, $message);
                    $result = $api->VerifyLookup($receptor, $message, '', '', 'verify');
                } catch (\Kavenegar\Exceptions\ApiException $e) {
                    $result = $e->errorMessage();
                } catch (\Kavenegar\Exceptions\HttpException $e) {
                    $result = $e->errorMessage();
                }
                return response()->json(['status' => 0, 'mosh_id' => $new_mosh->code, 'result' => $result]);
            }
        }
    }
    protected function mosh_code()
    {
        $length = 3;
        $number = rand(100, 999);
        $char   = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', ceil($length / strlen($x)))), 1, $length);
        $result = $number . $char;

        $check_code_mosh = Mosh::where('code', $result)->first();

        if ($check_code_mosh) {
            $this->mosh_code();
        }

        return $result;
    }

    public function ok_code_mosh(Request $request)
    {
        $mobile = $request->mobile;
        $id = $request->mosh_id;

        $mosh = Mosh::where('code', $id)->where('mobile', $mobile)->first();
        if ($mosh) {
            if ($mosh->err < $this->maxerr) {
                $mosh->status = 1;
                if ($mosh->update()) {
                    return response()->json(['type' => 1, 'status' => 1]);
                }
            } else {
                return response()->json(['type' => -2]);
            }
        } else {
            $mosh = Mosh::where('mobile', $mobile)->first();
            if ($mosh->err < $this->maxerr) {
                $mosh->err = $mosh->err + 1;
                if ($mosh->update()) {
                    return response()->json(['type' => 0]);
                }
            } else {
                return response()->json(['type' => -2]);
            }
        }
    }

    public function register_mosh(Request $request)
    {

        $mosh = Mosh::where('code', $request->mosh_id)->first();
        if (isset($request->edit)) {
            $mosh->name = $request->name;
            $mosh->nation_code = $request->nation;
        } else {
            $mosh->name = $request->name;
            $mosh->nation_code = $request->nation;
            $mosh->pass = $request->pass;
            $mosh->status = 2;
        }

        if ($mosh->update()) {
            return response()->json(['status' => 2, 'token' => $mosh->mobile . ';' . $mosh->code]);
        }
    }

    public function check_pass_mosh(Request $request)
    {
        $mobile = $request->mobile;
        $mosh = Mosh::where('mobile', $mobile)->where('pass', $request->pass)->first();
        if ($mosh) {
            $token = $mobile . ';' . $mosh->code;
            return response()->json(['type' => 1, 'mosh_id' => $mosh->code, 'token' => $token]);
        } else {
            return response()->json(['type' => 2]);
        }
    }

    public function all_users_mosh(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $stu = Stu::where('mosh_id', $mosh_id)->get();

        // پیدا کردن آیدی هفته کنونی
        $all_week = DB::table('week')->get();
        foreach ($all_week as $key => $value) {
            if ($all_week[$key]->start < time() && $all_week[$key]->end > time()) {
                $week_id = $all_week[$key]->id;
            }
        }

        foreach ($stu as $key => $value) {
            $sum_details = DB::table('edu_plan')
                ->where('week_id', $week_id)
                ->where('stu_id', $value->id)
                ->select('test_time', 'study_time', 'test_count', 'l_id', 'Pre_reading', 'exercise', 'Summarizing', 'passage', 'Repeat_test')->get();
            if (empty($sum_details)) {
                $sum_details = (object)[
                    "h_study" => 0,
                    "h_test" => 0,
                    "h_sum" => 0,
                ];
            } else {
                $stu[$key]->h_study = 0;
                $stu[$key]->h_test = 0;
                $stu[$key]->SumTestNum = 0;
                foreach ($sum_details as $key2 => $value2) {
                    if ($value2->test_count) {
                        $stu[$key]->SumTestNum += $value2->test_count;
                    }
                    if ($value2->test_time && strpos($value2->test_time, ':')) {
                        $stu[$key]->h_test += explode(':', $value2->test_time)[0] * 3600;
                        $stu[$key]->h_test += explode(':', $value2->test_time)[1] * 60;
                    } else {
                        $stu[$key]->h_test += $value2->test_time * 3600;
                    }
                    if ($value2->study_time && strpos($value2->study_time, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->study_time)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->study_time)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->study_time * 3600;
                    }
                    if ($value2->Pre_reading && strpos($value2->Pre_reading, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->Pre_reading)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->Pre_reading)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->Pre_reading * 3600;
                    }
                    if ($value2->exercise && strpos($value2->exercise, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->exercise)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->exercise)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->exercise * 3600;
                    }
                    if ($value2->Summarizing && strpos($value2->Summarizing, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->Summarizing)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->Summarizing)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->Summarizing * 3600;
                    }
                    if ($value2->passage && strpos($value2->passage, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->passage)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->passage)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->passage * 3600;
                    }
                    if ($value2->Repeat_test && strpos($value2->Repeat_test, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->Repeat_test)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->Repeat_test)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->Repeat_test * 3600;
                    }
                }
                $stu[$key]->h_sum = $stu[$key]->h_study + $stu[$key]->h_test;

                $total_minutes = floor($stu[$key]->h_sum / 60);
                $hours = floor($total_minutes / 60);
                $minutes = $total_minutes % 60;
                $stu[$key]->h_sum = $hours . ' : ' . $minutes;

                $total_minutes2 = floor($stu[$key]->h_study / 60);
                $hours2 = floor($total_minutes2 / 60);
                $minutes2 = $total_minutes2 % 60;
                $stu[$key]->h_study = $hours2 . ' : ' . $minutes2;

                $total_minutes3 = floor($stu[$key]->h_test / 60);
                $hours3 = floor($total_minutes3 / 60);
                $minutes3 = $total_minutes3 % 60;
                $stu[$key]->h_test = $hours3 . ' : ' . $minutes3;
            }
        }

        return $stu;
    }

    public function ms_edit(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $mosh = Mosh::where('code', $mosh_id)->first();

        $mosh_slider = MoshSlider::where('mosh_id', $mosh_id)->get();

        return response()->json([
            'message' => $mosh->message,
            'mosh_slider' => $mosh_slider,
        ]);
    }

    public function UploadImageSlider(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $image = $request->image;
        $type = $request->type;
        $type = str_replace("'", "", $type);
        $imageName = time() . 'm' . rand(1, 99) . '.' . $type;
        if (\File::put(public_path() . '/images/' . $imageName, base64_decode($image))) {
            $mosh_slider = new MoshSlider;
            $mosh_slider->mosh_id = $mosh_id;
            $mosh_slider->vlaue = $imageName;
            $mosh_slider->save();
        }
        return $type;
    }

    public function RemoveImageSlider(Request $request)
    {
        $slider_id = $request->id;
        if (MoshSlider::find($slider_id)->delete()) {
            return 'ok';
        }
    }

    public function moshTransaction(Request $request)
    {
        // $stu_id = explode(';', $request->token)[1];
        // $transaction = Transaction::where('stu_id', $stu_id)->get();

        // foreach ($transaction as $key => $value) {
        //     $n = Verta::createTimestamp((int) $transaction[$key]->date_time);
        //     $transaction[$key]->date_time = $n->formatJalaliDate();
        // }

        return response()->json([]);
    }

    // گرفتن دانش آموزایی که دیروز برنامه ارسال کردند
    public function getStuSendEdu(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $stu_id = Stu::where('mosh_id', $mosh_id)->select('id')->get();
        // $mosh = Mosh::where('code', $mosh_id)->first();

        $h = date('H', time());
        $i = date('i', time());
        $s = date('s', time());
        $bamdad = time() - (($h * 3600) + ($i * 60) + $s);

        $edus = Edu::whereIn('stu_id', $stu_id)
            ->where('date_time', '<=', $bamdad)
            ->where('date_time', '>=', $bamdad - 86400)
            ->select('stu_id')
            ->get();
        // پیدا کردن آیدی هفته کنونی
        $all_week = DB::table('week')->get();
        foreach ($all_week as $key => $value) {
            if ($all_week[$key]->start < time() && $all_week[$key]->end > time()) {
                $week_id = $all_week[$key]->id;
            }
        }
        // گرفتن دانش آموزانی که دیروز برنامه فرستادن
        $stu = DB::table('stu')
            ->where('stu.mosh_id', $mosh_id)
            ->whereIn('stu.id', $edus)
            ->get();

        foreach ($stu as $key => $value) {
            $sum_details = DB::table('edu_plan')
                ->where('week_id', $week_id)
                ->where('stu_id', $value->id)
                ->select('test_time', 'study_time', 'test_count', 'l_id', 'Pre_reading', 'exercise', 'Summarizing', 'passage', 'Repeat_test')->get();
            if (empty($sum_details)) {
                $sum_details = (object)[
                    "h_study" => 0,
                    "h_test" => 0,
                    "h_sum" => 0,
                ];
            } else {
                $stu[$key]->h_study = 0;
                $stu[$key]->h_test = 0;
                $stu[$key]->SumTestNum = 0;
                foreach ($sum_details as $key2 => $value2) {
                    if ($value2->test_count) {
                        $stu[$key]->SumTestNum += $value2->test_count;
                    }
                    if ($value2->test_time && strpos($value2->test_time, ':')) {
                        $stu[$key]->h_test += explode(':', $value2->test_time)[0] * 3600;
                        $stu[$key]->h_test += explode(':', $value2->test_time)[1] * 60;
                    } else {
                        $stu[$key]->h_test += $value2->test_time * 3600;
                    }
                    if ($value2->study_time && strpos($value2->study_time, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->study_time)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->study_time)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->study_time * 3600;
                    }
                    if ($value2->Pre_reading && strpos($value2->Pre_reading, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->Pre_reading)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->Pre_reading)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->Pre_reading * 3600;
                    }
                    if ($value2->exercise && strpos($value2->exercise, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->exercise)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->exercise)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->exercise * 3600;
                    }
                    if ($value2->Summarizing && strpos($value2->Summarizing, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->Summarizing)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->Summarizing)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->Summarizing * 3600;
                    }
                    if ($value2->passage && strpos($value2->passage, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->passage)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->passage)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->passage * 3600;
                    }
                    if ($value2->Repeat_test && strpos($value2->Repeat_test, ':')) {
                        $stu[$key]->h_study += explode(':', $value2->Repeat_test)[0] * 3600;
                        $stu[$key]->h_study += explode(':', $value2->Repeat_test)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->Repeat_test * 3600;
                    }
                }
                $stu[$key]->h_sum = $stu[$key]->h_study + $stu[$key]->h_test;

                $total_minutes = floor($stu[$key]->h_sum / 60);
                $hours = floor($total_minutes / 60);
                $minutes = $total_minutes % 60;
                $stu[$key]->h_sum = $hours . ' : ' . $minutes;

                $total_minutes2 = floor($stu[$key]->h_study / 60);
                $hours2 = floor($total_minutes2 / 60);
                $minutes2 = $total_minutes2 % 60;
                $stu[$key]->h_study = $hours2 . ' : ' . $minutes2;

                $total_minutes3 = floor($stu[$key]->h_test / 60);
                $hours3 = floor($total_minutes3 / 60);
                $minutes3 = $total_minutes3 % 60;
                $stu[$key]->h_test = $hours3 . ' : ' . $minutes3;
            }
        }

        return response()->json(['all' => [
            'stu' => $stu,
            // 'mosh' => $mosh,
            // 'appname' => $this->appname,
        ]]);
    }


    public function getlesson(Request $request)
    {
        
        $token = $request->token;
        $stuId = $request->stuId;

        $checkLogin = Tools::checkTokenMosh($token);

        if (!$checkLogin['success'])  // check Login user 
            return response()->json($this->loginErrorArr);

        $stu = Tools::getStuWithId($stuId);


        $lesson = Lesson::where('base_id', $stu->base_id)
            ->where('r_id', $stu->r_id)
            ->get();

        return response()->json([
            'success' => true,
            'lessons' => $lesson
        ]);
    }
}
