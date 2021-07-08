<?php

namespace App\Http\Controllers;

use App\Http\Controllers\FirebaseController;

use App\Chat;
use App\Edu;
use App\group_stu;
use App\Lesson;
use App\Mosh;
use App\Sms;
use App\Stu;
use App\Options;
use App\Planing;
use App\Transaction;
use App\Weekly;
use App\Week;
use Illuminate\Http\Request;
use App\Lib\zarinpal;
use App\Lib\bitpay;
use nusoap_client;
use Verta;
use DB;
use PDF;
use PhpParser\Node\Expr\Cast\Double;
use App\Groups;
use App\history_planing;
use App\MoshSlider;
use Session;
use App\helper\study;
use App\helper\Tools;
use App\StudySum;

class ApiController extends Controller
{
    public $status_pay = 0;

    public function mobile(Request $request)
    {
        $mobile = $request->mobile;
        $stu = Stu::where('mobile', $mobile)->first();
        // شماره وجود داشته
        if ($stu) {
            return response()->json(['status' => $stu->status, 'stu_id' => $stu->id]);
        }
        // شماره وجود نداشته یعنی ثبت نام
        else {
            $new_stu = new Stu;
            $new_stu->mobile = $mobile;
            $new_stu->status = 0;
            $new_stu->time_added = time();
            if ($new_stu->save()) {
                return response()->json(['status' => 0, 'stu_id' => $new_stu->id]);
            }
        }
    }
    public function check_pass(Request $request)
    {
        // return response()->json($request->mobile);
        $mobile = $request->mobile;
        $stu = Stu::where('mobile', $mobile)->where('pass', $request->pass)->first();
        if ($stu) {
            return response()->json(['type' => 1, 'stu_id' => $stu->id]);
        } else {
            return response()->json(['type' => 2]);
        }
    }

    public function ok_code(Request $request)
    {
        $random = $request->random;
        $id = $request->stu_id;

        $sms = Sms::where('stu_id', $id)->where('code', $random)->first();
        if ($sms) {
            $stu = Stu::find($id);
            $stu->status = 1;
            if ($stu->update()) {
                return response()->json(['type' => 1, 'status' => 1]);
            }
        } else {
            $student = Sms::where('stu_id', $id)->first();
            if ($student->err < 10) {
                $student->err = $student->err + 1;
                if ($student->update()) {
                    return response()->json(['type' => 0]);
                }
            } else {
                return response()->json(['type' => -2]);
            }
        }
    }

    public function register(Request $request)
    {
        $stu = Stu::where('id', $request->stu_id)->first();

        $stu->name = $request->name;
        $stu->base_id = $request->base;
        if ($request->major == 3) {
            $stu->r_id = null;
        } else {
            $stu->r_id = json_decode($request->major);
        }
        $stu->pass = $request->pass;
        $stu->status = 2;

        if ($stu->update()) {
            return response()->json(['status' => 1, 'token' => $stu->mobile . ';' . $stu->id]);
        }
    }

    public function get_home(Request $request)
    {
        $stu = DB::table('stu')
            ->where('stu.id', explode(';', $request->token)[1])
            ->leftJoin('mosh', 'stu.mosh_id', '=', 'mosh.code')
            ->select('stu.*', 'mosh.name as mosh_name')
            ->first();
        // شرط وجود مشاور برای دانش آموز
        $all_img = Options::where('type', 0)->get();
        if ($stu->status == 2) {
            $message = Options::where('type', 2)->first();
            $logo = '/images/p4.png';
        } else {
            $mosh = DB::table('mosh')->where('code', $stu->mosh_id)->first();
            $message = (object)[
                "vlaue" => $mosh->message,
            ];
            $mosh_slider = MoshSlider::where('mosh_id', $stu->mosh_id)->get();
            if (count($mosh_slider) >= 1) {
                $all_img = $mosh_slider;
            }
            $logo = $mosh->logo;
        }

        // دریافت آیدی هفته ی جاری
        $all_week = DB::table('week')->get();
        foreach ($all_week as $key => $value) {
            if ($all_week[$key]->start < time() && $all_week[$key]->end > time()) {
                $week_id = $all_week[$key]->id;
            }
        }
        // تعداد چت های جدید برای دانش آموز
        $new_chat = Chat::where('view', 0)
            ->where('stu_id', explode(';', $request->token)[1])
            ->where('mosh_id', $stu->mosh_id)
            ->where('current', 0)
            ->count();

        return response()->json([
            'slider' => $all_img,
            'message' => $message,
            'logo' => $logo,
            'week_id' => $week_id,
            'student_status' => $stu->status,
            'stu' => $stu,
            'new_chat' => $new_chat,
            'appname' => $this->appname,
            'all_week' => $all_week,
        ]);
    }

    // دفتر برنامه ریزی
    public function edu_plan(Request $request)
    {
        $n = Verta::createTimestamp(time());
        return response()->json(['day' => $n->formatWord('l'), 'date' => $n->formatJalaliDate()]);
    }

    public function send_edu(Request $request)
    {
        $date_time = time();
        $stu_id = explode(';', $request->token)[1];

        // $d = str_replace(":", '', $request->data);
        $d = $request->data;
        $data = json_decode($d, true);
        // error_log('========================================\n');
        // error_log($data);
        // error_log($data);

        $today = $request->toDay;
        $clickday = $request->clickDay;
        $week_id = $request->week_id;

        date_default_timezone_set("Asia/Tehran");
        $h = date('H', time());
        $i = date('i', time());
        $s = date('s', time());
        $bamdad = time() - (($h * 3600) + ($i * 60) + $s);

        $h_sum = 0;
        if ($today - $clickday == 0) {

            Edu::where('stu_id', $stu_id)->where('date_time', '>', $bamdad)->delete();
            foreach ($data as $key => $value) {
                $edu = Edu::insert([
                    'date_time' => $date_time,
                    'l_id' => $data[$key][0],
                    'stu_id' => $stu_id,
                    'study_time' => $data[$key][1],
                    'test_time' => $data[$key][2],
                    'test_count' => $data[$key][3],
                    'week_id' => $week_id,
                    'day' => $clickday,
                    'Pre_reading' => $data[$key][4],
                    'exercise' => $data[$key][5],
                    'Summarizing' => $data[$key][6],
                    'passage' => $data[$key][7],
                    'Repeat_test' => $data[$key][8],
                    'c_r_test' => $data[$key][9],
                ]);
                if ($data[$key][1] && strpos($data[$key][1], ':')) {
                    $h_sum += explode(':', $data[$key][1])[0] * 3600;
                    $h_sum += explode(':', $data[$key][1])[1] * 60;
                } else {
                    $h_sum += $data[$key][1] * 3600;
                }
                if ($data[$key][2] && strpos($data[$key][2], ':')) {
                    $h_sum += explode(':', $data[$key][2])[0] * 3600;
                    // isset($data[$key][2])[1])?
                    $h_sum += explode(':', $data[$key][2])[1] * 60;
                } else {
                    $h_sum += $data[$key][2] * 3600;
                }
                if ($data[$key][4] && strpos($data[$key][4], ':')) {
                    $h_sum += explode(':', $data[$key][4])[0] * 3600;
                    $h_sum += explode(':', $data[$key][4])[1] * 60;
                } else {
                    $h_sum += $data[$key][4] * 3600;
                }
                if ($data[$key][5] && strpos($data[$key][5], ':')) {
                    $h_sum += explode(':', $data[$key][5])[0] * 3600;
                    $h_sum += explode(':', $data[$key][5])[1] * 60;
                } else {
                    $h_sum += $data[$key][5] * 3600;
                }
                if ($data[$key][6] && strpos($data[$key][6], ':')) {
                    $h_sum += explode(':', $data[$key][6])[0] * 3600;
                    $h_sum += explode(':', $data[$key][6])[1] * 60;
                } else {
                    $h_sum += $data[$key][6] * 3600;
                }
                if ($data[$key][7] && strpos($data[$key][7], ':')) {
                    $h_sum += explode(':', $data[$key][7])[0] * 3600;
                    $h_sum += explode(':', $data[$key][7])[1] * 60;
                } else {
                    $h_sum += $data[$key][7] * 3600;
                }
                if ($data[$key][8] && strpos($data[$key][8], ':')) {
                    $h_sum += explode(':', $data[$key][8])[0] * 3600;
                    $h_sum += explode(':', $data[$key][8])[1] * 60;
                } else {
                    $h_sum += $data[$key][8] * 3600;
                }
            }
            $total_minutes = floor($h_sum / 60);
            $hours = floor($total_minutes / 60);
            $minutes = $total_minutes % 60;
            $h_sum = $hours . ':' . $minutes;

            $study = new study;
            $study->SaveSumStudy($stu_id, $week_id, $clickday, $h_sum, $request->normal, $request->mes);
            $this->send_auto_mes($stu_id, $h_sum);
            return response()->json(['data' => $data[0], 'mes' => 'امروز']);
        } else {

            Edu::where('stu_id', $stu_id)->where('date_time', '<', $bamdad - (86400 * ($today - $clickday - 1)))->where('date_time', '>', $bamdad - (86400 * ($today - $clickday)))->delete();

            foreach ($data as $key => $value) {
                $edu = Edu::insert([
                    'date_time' => $date_time - (86400 * ($today - $clickday)),
                    'l_id' => $data[$key][0],
                    'stu_id' => $stu_id,
                    'study_time' => $data[$key][1],
                    'test_time' => $data[$key][2],
                    'test_count' => $data[$key][3],
                    'week_id' => $week_id,
                    'day' => $clickday,
                    'Pre_reading' => $data[$key][4],
                    'exercise' => $data[$key][5],
                    'Summarizing' => $data[$key][6],
                    'passage' => $data[$key][7],
                    'Repeat_test' => $data[$key][8],
                    'c_r_test' => $data[$key][9],
                ]);
                if ($data[$key][1] && strpos($data[$key][1], ':')) {
                    $h_sum += explode(':', $data[$key][1])[0] * 3600;
                    $h_sum += explode(':', $data[$key][1])[1] * 60;
                } else {
                    $h_sum += $data[$key][1] * 3600;
                }
                if ($data[$key][2] && strpos($data[$key][2], ':')) {
                    $h_sum += explode(':', $data[$key][2])[0] * 3600;
                    $h_sum += explode(':', $data[$key][2])[1] * 60;
                } else {
                    $h_sum += $data[$key][2] * 3600;
                }
                if ($data[$key][4] && strpos($data[$key][4], ':')) {
                    $h_sum += explode(':', $data[$key][4])[0] * 3600;
                    $h_sum += explode(':', $data[$key][4])[1] * 60;
                } else {
                    $h_sum += $data[$key][4] * 3600;
                }
                if ($data[$key][5] && strpos($data[$key][5], ':')) {
                    $h_sum += explode(':', $data[$key][5])[0] * 3600;
                    $h_sum += explode(':', $data[$key][5])[1] * 60;
                } else {
                    $h_sum += $data[$key][5] * 3600;
                }
                if ($data[$key][6] && strpos($data[$key][6], ':')) {
                    $h_sum += explode(':', $data[$key][6])[0] * 3600;
                    $h_sum += explode(':', $data[$key][6])[1] * 60;
                } else {
                    $h_sum += $data[$key][6] * 3600;
                }
                if ($data[$key][7] && strpos($data[$key][7], ':')) {
                    $h_sum += explode(':', $data[$key][7])[0] * 3600;
                    $h_sum += explode(':', $data[$key][7])[1] * 60;
                } else {
                    $h_sum += $data[$key][7] * 3600;
                }
                if ($data[$key][8] && strpos($data[$key][8], ':')) {
                    $h_sum += explode(':', $data[$key][8])[0] * 3600;
                    $h_sum += explode(':', $data[$key][8])[1] * 60;
                } else {
                    $h_sum += $data[$key][8] * 3600;
                }

                // $h_sum += $data[$key][1];
                // $h_sum += $data[$key][2];
            }
            $total_minutes = floor($h_sum / 60);
            $hours = floor($total_minutes / 60);
            $minutes = $total_minutes % 60;
            $h_sum = $hours . ':' . $minutes;

            $study = new study;
            $study->SaveSumStudy($stu_id, $week_id, $clickday, $h_sum, $request->normal, $request->mes);

            $this->send_auto_mes($stu_id, $h_sum);
            return response()->json(['data' => $data[0], 'mes' => 'روز قبل']);
        }
    }
    // ارسال پیام اتوماتیک
    protected function send_auto_mes($stu_id, $h_sum)
    {
        $mosh = Mosh::where('code', function ($query) use ($stu_id) {
            $query->select('mosh_id')
                ->from('stu')
                ->where('id', $stu_id);
        })->first();
        // error_log('============================================');


        // چک کردن حالت خودکار بودن مشاور
        if ($mosh) {
            if ($mosh->auto == 1) {
                $AllMesGrade = DB::table('automati_messaging')
                    ->where('mosh_id', $mosh->code)
                    ->get();
                $h_sum = explode(':', $h_sum)[0];
                foreach ($AllMesGrade as $key => $value) {
                    $grade = explode(';', $value->grade);
                    if (($grade[1] >= $h_sum) && ($grade[0] <= $h_sum)) {
                        // sleep(2 * 60);
                        Chat::insert([
                            'stu_id' => $stu_id,
                            'mosh_id' => $mosh->code,
                            'type'   => 1,
                            'text'   => $value->message,
                            'date_time' => time(),
                            'current' => 0,
                            'view'   => 0,
                        ]);
                        $stu = Stu::where('id', $stu_id)->first();
                        $title = "پیام از طرف مشاور ";
                        $text = $value->message;
                        $notification = new FirebaseController;
                        $notification->spn($stu->FirebaseToken, $title, $text);
                        break;
                    }
                }
            }
        }
    }

    public function get_edu(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $h = date('H', time());
        $i = date('i', time());
        $s = date('s', time());
        $bamdad = time() - (($h * 3600) + ($i * 60) + $s);

        $today = $request->toDay;
        $week_id = $request->week_id;
        $clickday = $request->clickDay;
        $stu_id = explode(';', $request->token)[1];
        $stu = Stu::where('id', explode(';', $request->token)[1])->first();
        $normal = StudySum::where('stu_id', $stu_id)->where('day', $clickday)->where('week_id', $week_id)->first();
        if ($normal == null) {
            $normal = 3;
        }

        if ($today - $clickday == 0) {
            $edu = DB::table('edu_plan')
                ->where('edu_plan.stu_id', $stu_id)
                ->where('edu_plan.date_time', '>', $bamdad)
                ->leftJoin('lesson', 'edu_plan.l_id', '=', 'lesson.id')
                ->select('edu_plan.l_id as lesson_id', 'edu_plan.stu_id', 'edu_plan.study_time', 'edu_plan.test_time', 'edu_plan.test_count as test_num', 'lesson.title as lessonName', 'edu_plan.Pre_reading', 'edu_plan.exercise', 'edu_plan.Summarizing', 'edu_plan.passage', 'edu_plan.Repeat_test', 'edu_plan.c_r_test')
                ->get();
            if (empty($edu[0])) {
                $lesson = Lesson::where('base_id', $stu->base_id)->where('r_id', $stu->r_id)->get();
                foreach ($lesson as $key => $value) {
                    $edu[$key] = (object)[
                        'lessonName' => $lesson[$key]->title,
                        'stu_id' => $stu_id,
                        'lesson_id' => $lesson[$key]->id,
                        'study_time' => null,
                        'test_time' => null,
                        'test_num' => null,
                        'Pre_reading' => null,
                        'exercise' => null,
                        'Summarizing' => null,
                        'passage' => null,
                        'Repeat_test' => null,
                        'c_r_test' => null,
                    ];
                }
            }
            if ($today != 0) {
                $mes = DB::table('sum_study')->where('stu_id', $stu_id)->where('day', $today - 1)->where('week_id', $week_id)->first();
            } else {
                $week_num = Week::where('id', $week_id)->first();
                $mes = DB::table('sum_study')->where('stu_id', $stu_id)->where('day', 6)->where('week_id', $week_num->num - 1)->first();
            }
            if ($mes) {
                return response()->json(['mes' => $mes->tomorrow_mes, 'edu' => $edu, 'normal' => $normal]);
            } else {
                return response()->json(['mes' => '', 'edu' => $edu, 'normal' => $normal]);
            }
        } else {
            $edu = DB::table('edu_plan')
                ->where('edu_plan.stu_id', $stu_id)
                ->where('edu_plan.date_time', '<', $bamdad - (86400 * ($today - $clickday - 1)))
                ->where('edu_plan.date_time', '>', $bamdad - (86400 * ($today - $clickday)))
                ->leftJoin('lesson', 'edu_plan.l_id', '=', 'lesson.id')
                ->select('edu_plan.l_id as lesson_id', 'edu_plan.stu_id', 'edu_plan.study_time', 'edu_plan.test_time', 'edu_plan.test_count as test_num', 'lesson.title as lessonName', 'edu_plan.Pre_reading', 'edu_plan.exercise', 'edu_plan.Summarizing', 'edu_plan.passage', 'edu_plan.Repeat_test', 'edu_plan.c_r_test')
                ->get();
            if (empty($edu[0])) {
                $lesson = Lesson::where('base_id', $stu->base_id)->where('r_id', $stu->r_id)->get();
                foreach ($lesson as $key => $value) {
                    $edu[$key] = (object)[
                        'lessonName' => $lesson[$key]->title,
                        'stu_id' => $stu_id,
                        'lesson_id' => $lesson[$key]->id,
                        'study_time' => null,
                        'test_time' => null,
                        'test_num' => null,
                        'Pre_reading' => null,
                        'exercise' => null,
                        'Summarizing' => null,
                        'passage' => null,
                        'Repeat_test' => null,
                        'c_r_test' => null,
                    ];
                }
            }
            if ($clickday != 0) {
                $mes = DB::table('sum_study')->where('stu_id', $stu_id)->where('day', $clickday - 1)->where('week_id', $week_id)->first();
            } else {
                $week_num = Week::where('id', $week_id)->first();
                $mes = DB::table('sum_study')->where('stu_id', $stu_id)->where('day', 6)->where('week_id', $week_num->num - 1)->first();
            }
            if ($edu) {
                if ($mes) {
                    return response()->json(['mes' => $mes->tomorrow_mes, 'edu' => $edu, 'normal' => $normal]);
                } else {
                    return response()->json(['mes' => '', 'edu' => $edu, 'normal' => $normal]);
                }
            }
        }
    }

    // برنامه مدرسه دانش آموز
    public function get_plan_stu(Request $request)
    {
        $stu = Stu::where('id', explode(';', $request->token)[1])->first();
        $lesson = Lesson::where('base_id', $stu->base_id)->where('r_id', $stu->r_id)->get();
        $weekly = DB::table('weekly')
            ->where('weekly.stu_id', explode(';', $request->token)[1])
            ->select('weekly.*')
            ->get();

        $plans = array(
            (object)['day' => 0, 'part1' => null, 'part2' => null, 'part3' => null, 'part4' => null, 'part5' => null],
            (object)['day' => 1, 'part1' => null, 'part2' => null, 'part3' => null, 'part4' => null, 'part5' => null],
            (object)['day' => 2, 'part1' => null, 'part2' => null, 'part3' => null, 'part4' => null, 'part5' => null],
            (object)['day' => 3, 'part1' => null, 'part2' => null, 'part3' => null, 'part4' => null, 'part5' => null],
            (object)['day' => 4, 'part1' => null, 'part2' => null, 'part3' => null, 'part4' => null, 'part5' => null],
            (object)['day' => 5, 'part1' => null, 'part2' => null, 'part3' => null, 'part4' => null, 'part5' => null]
        );
        if (!empty($weekly[0])) {
            foreach ($weekly as $key => $value) {
                if ($weekly[$key]->day == 0) {
                    $plan = 'part';
                    $b = $plan . $weekly[$key]->part;
                    $plans[0]->day = $weekly[$key]->day;
                    $plans[0]->$b = $weekly[$key]->l_id;
                }
                if ($weekly[$key]->day == 1) {
                    $plan = 'part';
                    $b = $plan . $weekly[$key]->part;
                    $plans[1]->day = $weekly[$key]->day;
                    $plans[1]->$b = $weekly[$key]->l_id;
                }
                if ($weekly[$key]->day == 2) {
                    $plan = 'part';
                    $b = $plan . $weekly[$key]->part;
                    $plans[2]->day = $weekly[$key]->day;
                    $plans[2]->$b = $weekly[$key]->l_id;
                }
                if ($weekly[$key]->day == 3) {
                    $plan = 'part';
                    $b = $plan . $weekly[$key]->part;
                    $plans[3]->day = $weekly[$key]->day;
                    $plans[3]->$b = $weekly[$key]->l_id;
                }
                if ($weekly[$key]->day == 4) {
                    $plan = 'part';
                    $b = $plan . $weekly[$key]->part;
                    $plans[4]->day = $weekly[$key]->day;
                    $plans[4]->$b = $weekly[$key]->l_id;
                }
                if ($weekly[$key]->day == 5) {
                    $plan = 'part';
                    $b = $plan . $weekly[$key]->part;
                    $plans[5]->day = $weekly[$key]->day;
                    $plans[5]->$b = $weekly[$key]->l_id;
                }
            }
        }


        return response()->json(['lesson' => $lesson, 'plan' => $weekly, 'plans' => $plans]);
    }

    public function send_plan_stu(Request $request)
    {
        $mosh = Mosh::where('code', $request->mosh_id)->first();
        if ($mosh) {
            $data = json_decode($request->data, true);
            $stu_id = explode(';', $request->token)[1];
            $stu = Stu::where('id', $stu_id)->first();
            $stu->nation_code = $request->nation_code;
            $stu->name = $request->name;
            $stu->mosh_id = $request->mosh_id;
            $stu->status = 3;
            $stu->update();
            DB::table('weekly')->where('stu_id', $stu_id)->delete();
            foreach ($data as $key => $value) {
                for ($i = 1; $i < 6; $i++) {
                    $week = DB::table('weekly')->insert([
                        'stu_id' => $stu_id,
                        'day' => $data[$key][0],
                        'part' => $i,
                        'l_id' => $data[$key][$i],
                    ]);
                }
            }
            return response()->json(['data' => $data[0], 'mes' => 'امروز', 'success' => 'yes']);
        }
        return response()->json(['success' => 'no']);
    }

    // هفته ها
    public function all_week(Request $request)
    {
        $weeks = DB::table('edu_plan')->where('stu_id', explode(';', $request->token)[1])
            ->groupBy('week_id')->select('week_id', DB::raw('count(*) as total'))->get();
        $week_ids = array();
        foreach ($weeks as $key => $value) {
            array_push($week_ids, $value->week_id);
        }

        $stu_week = DB::table('week')->whereIn('id', $week_ids)->orderBy('id', 'desc')->get();
        $week_ids = array_reverse($week_ids);
        foreach ($stu_week as $key => $value) {
            $stu_week[$key]->h = DB::table('edu_plan')->where('stu_id', explode(';', $request->token)[1])->where('week_id', $week_ids[$key])
                ->select('test_time', 'study_time', 'test_count', 'Pre_reading', 'exercise', 'Summarizing', 'passage', 'Repeat_test')->get();
            $stu_week[$key]->h_org = 0;
            $stu_week[$key]->test_count = 0;
            foreach ($stu_week[$key]->h as $key2 => $value2) {
                $stu_week[$key]->test_count += $value2->test_count;
                if ($value2->test_time && strpos($value2->test_time, ':')) {
                    $stu_week[$key]->h_org += explode(':', $value2->test_time)[0] * 3600;
                    $stu_week[$key]->h_org += explode(':', $value2->test_time)[1] * 60;
                } else {
                    $stu_week[$key]->h_org += $value2->test_time * 3600;
                }
                if ($value2->study_time && strpos($value2->study_time, ':')) {
                    $stu_week[$key]->h_org += explode(':', $value2->study_time)[0] * 3600;
                    $stu_week[$key]->h_org += explode(':', $value2->study_time)[1] * 60;
                } else {
                    $stu_week[$key]->h_org += $value2->study_time * 3600;
                }
                if ($value2->Pre_reading && strpos($value2->Pre_reading, ':')) {
                    $stu_week[$key]->h_org += explode(':', $value2->Pre_reading)[0] * 3600;
                    $stu_week[$key]->h_org += explode(':', $value2->Pre_reading)[1] * 60;
                } else {
                    $stu_week[$key]->h_org += $value2->Pre_reading * 3600;
                }
                if ($value2->exercise && strpos($value2->exercise, ':')) {
                    $stu_week[$key]->h_org += explode(':', $value2->exercise)[0] * 3600;
                    $stu_week[$key]->h_org += explode(':', $value2->exercise)[1] * 60;
                } else {
                    $stu_week[$key]->h_org += $value2->exercise * 3600;
                }
                if ($value2->Summarizing && strpos($value2->Summarizing, ':')) {
                    $stu_week[$key]->h_org += explode(':', $value2->Summarizing)[0] * 3600;
                    $stu_week[$key]->h_org += explode(':', $value2->Summarizing)[1] * 60;
                } else {
                    $stu_week[$key]->h_org += $value2->Summarizing * 3600;
                }
                if ($value2->passage && strpos($value2->passage, ':')) {
                    $stu_week[$key]->h_org += explode(':', $value2->passage)[0] * 3600;
                    $stu_week[$key]->h_org += explode(':', $value2->passage)[1] * 60;
                } else {
                    $stu_week[$key]->h_org += $value2->passage * 3600;
                }
                if ($value2->Repeat_test && strpos($value2->Repeat_test, ':')) {
                    $stu_week[$key]->h_org += explode(':', $value2->Repeat_test)[0] * 3600;
                    $stu_week[$key]->h_org += explode(':', $value2->Repeat_test)[1] * 60;
                } else {
                    $stu_week[$key]->h_org += $value2->Repeat_test * 3600;
                }
            }
            $total_minutes = floor($stu_week[$key]->h_org / 60);
            $hours = floor($total_minutes / 60);
            $minutes = $total_minutes % 60;
            $stu_week[$key]->h_org = $hours . ' : ' . $minutes;

            $start = Verta::createTimestamp((int) $value->start);
            $end = Verta::createTimestamp((int) $value->end);
            $stu_week[$key]->start = $start->formatJalaliDate();
            $stu_week[$key]->end = $end->formatJalaliDate();
        }
        return response()->json(['all_week' => $stu_week]);
    }

    public function get_day_week(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];
        $stu = Stu::where('id', explode(';', $request->token)[1])->first();
        $edu = DB::table('edu_plan')
            ->where('edu_plan.stu_id', $stu_id)
            ->where('edu_plan.day', $request->clickDay)
            ->where('edu_plan.week_id', $request->week_id)
            ->leftJoin('lesson', 'edu_plan.l_id', '=', 'lesson.id')
            ->select('edu_plan.l_id as lesson_id', 'edu_plan.stu_id', 'edu_plan.study_time', 'edu_plan.test_time', 'edu_plan.test_count as test_num', 'lesson.title as lessonName', 'edu_plan.Pre_reading', 'edu_plan.exercise', 'edu_plan.Summarizing', 'edu_plan.passage', 'edu_plan.Repeat_test', 'edu_plan.c_r_test')
            ->get();
        $normal = DB::table('sum_study')
            ->where('stu_id', $stu_id)->where('day', $request->clickDay)
            ->where('week_id', $request->week_id)->first();

        // به دست آوردن جمع روزانه ساعت و تعداد تست 
        $SumTestNum = 0;
        $SumTestRepeat = 0;
        foreach ($edu as $key => $value) {
            $SumTestNum += $value->test_num;
            $SumTestRepeat += $value->c_r_test;
        }

        if (empty($edu[0])) {
            $lesson = Lesson::where('base_id', $stu->base_id)->where('r_id', $stu->r_id)->get();
            foreach ($lesson as $key => $value) {
                $edu[$key] = (object)[
                    'lessonName' => $lesson[$key]->title,
                    'stu_id' => $stu_id,
                    'lesson_id' => $lesson[$key]->id,
                    'study_time' => null,
                    'test_time' => null,
                    'test_num' => null,
                    'Pre_reading' => null,
                    'exercise' => null,
                    'Summarizing' => null,
                    'passage' => null,
                    'Repeat_test' => null,
                    'c_r_test' => null,
                ];
            }
        }

        $n = Verta::createTimestamp(time());
        if ($edu) {
            if ($normal) {
                return response()->json(['mes' => '', 'edu' => $edu, 'h_org' => $normal->h_sum, 'SumTestNum' => $SumTestNum, 'SumTestRepeat' => $SumTestRepeat, 'normal' => $normal->normal]);
            }
            return response()->json(['mes' => '', 'edu' => $edu, 'h_org' => 0, 'SumTestNum' => $SumTestNum, 'SumTestRepeat' => $SumTestRepeat, 'normal' => 1]);
        }
    }

    // پروفایل
    public function get_profile(Request $request)
    {
        $stu = DB::table('stu')
            ->where('stu.id', explode(';', $request->token)[1])
            ->leftJoin('mosh', 'stu.mosh_id', '=', 'mosh.code')
            ->select('stu.*', 'mosh.name as mosh_name')
            ->first();
        return response()->json(['stu' => $stu]);
    }

    // نموادر هفتگی ساعت مطالعه ی دانش آموز
    public function report_time_study(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];

        $weeks = DB::table('edu_plan')->where('stu_id', $stu_id)
            ->groupBy('week_id')
            ->select(
                'week_id'
            )
            ->limit(10)
            ->orderBy('week_id', 'desc')
            ->get();


        foreach ($weeks as $key => $value) {
            $weeks[$key]->sum = 0;
            $days = StudySum::where('stu_id', $stu_id)
                ->where('week_id', $value->week_id)->get();

            foreach ($days as $key2 => $value2) {
                if ($value2->h_sum && strpos($value2->h_sum, ':')) {
                    $weeks[$key]->sum += explode(':', $value2->h_sum)[0] * 3600;
                    $weeks[$key]->sum += explode(':', $value2->h_sum)[1] * 60;
                } else {
                    $weeks[$key]->sum += $value2->h_sum * 3600;
                }
            }
            $total_minutes = floor($weeks[$key]->sum / 60);
            $hours = floor($total_minutes / 60);
            $minutes = $total_minutes % 60;
            $weeks[$key]->sum = $hours . ':' . $minutes;
            $num = week::where('id', $value->week_id)->first();
            $weeks[$key]->num = $num->num;
        }

        return response()->json(['improvement_chart' => $weeks]);
    }

    public function detail_report(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];
        $stu = Stu::where('id', $stu_id)->first();
        $week_id = $request->week_id;

        $days_part = DB::table('edu_plan')
            ->where('stu_id', $stu_id)->where('week_id', $week_id)
            ->select('test_time', 'study_time', 'l_id', 'Pre_reading', 'exercise', 'Summarizing', 'passage', 'Repeat_test')->get();
        $lessons = Lesson::where('base_id', $stu->base_id)->where('r_id', $stu->r_id)->get();
        $sum = 0;
        $SumExclusive = 0;
        $SumGeneral = 0;
        $arrayGeneral = [];
        $arrayExclusive = array();
        $lessonGids = [];
        $lessonEids = [];

        foreach ($days_part as $key2 => $value2) {
            foreach ($lessons as $key => $value) {
                if ($value->id == $value2->l_id) {
                    if ($value2->study_time && strpos($value2->study_time, ':')) {
                        $lessons[$key]['study_time'] +=  explode(':', $value2->study_time)[0] * 3600;
                        $lessons[$key]['study_time'] +=  explode(':', $value2->study_time)[1] * 60;
                    } else {
                        $lessons[$key]['study_time'] +=  $value2->study_time * 3600;
                    }
                    if ($value2->test_time && strpos($value2->test_time, ':')) {
                        $lessons[$key]['test_time'] += explode(':', $value2->test_time)[0] * 3600;
                        $lessons[$key]['test_time'] += explode(':', $value2->test_time)[1] * 60;
                    } else {
                        $lessons[$key]['test_time'] += $value2->test_time * 3600;
                    }
                    if ($value2->Pre_reading && strpos($value2->Pre_reading, ':')) {
                        $lessons[$key]['Pre_reading'] += explode(':', $value2->Pre_reading)[0] * 3600;
                        $lessons[$key]['Pre_reading'] += explode(':', $value2->Pre_reading)[1] * 60;
                    } else {
                        $lessons[$key]['Pre_reading'] += $value2->Pre_reading * 3600;
                    }
                    if ($value2->exercise && strpos($value2->exercise, ':')) {
                        $lessons[$key]['exercise'] += explode(':', $value2->exercise)[0] * 3600;
                        $lessons[$key]['exercise'] += explode(':', $value2->exercise)[1] * 60;
                    } else {
                        $lessons[$key]['exercise'] += $value2->exercise * 3600;
                    }
                    if ($value2->Summarizing && strpos($value2->Summarizing, ':')) {
                        $lessons[$key]['Summarizing'] += explode(':', $value2->Summarizing)[0] * 3600;
                        $lessons[$key]['Summarizing'] += explode(':', $value2->Summarizing)[1] * 60;
                    } else {
                        $lessons[$key]['Summarizing'] += $value2->Summarizing * 3600;
                    }
                    if ($value2->passage && strpos($value2->passage, ':')) {
                        $lessons[$key]['passage'] += explode(':', $value2->passage)[0] * 3600;
                        $lessons[$key]['passage'] += explode(':', $value2->passage)[1] * 60;
                    } else {
                        $lessons[$key]['passage'] += $value2->passage * 3600;
                    }
                    if ($value2->Repeat_test && strpos($value2->Repeat_test, ':')) {
                        $lessons[$key]['Repeat_test'] += explode(':', $value2->Repeat_test)[0] * 3600;
                        $lessons[$key]['Repeat_test'] += explode(':', $value2->Repeat_test)[1] * 60;
                    } else {
                        $lessons[$key]['Repeat_test'] += $value2->Repeat_test * 3600;
                    }
                }
            }
            $lesson = Lesson::where('id', $value2->l_id)->first();
            if ($lesson->status == 1) {
                if (!in_array($lesson->title, $lessonEids)) {
                    $arrayExclusive[$lesson->title] = 0;
                    array_push($lessonEids, $lesson->title);
                }
                if ($value2->study_time && strpos($value2->study_time, ':')) {
                    $SumExclusive +=  explode(':', $value2->study_time)[0] * 3600;
                    $SumExclusive +=  explode(':', $value2->study_time)[1] * 60;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->study_time)[0] * 3600;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->study_time)[1] * 60;
                } else {
                    $SumExclusive +=  $value2->study_time * 3600;
                    $arrayExclusive[$lesson->title] += $value2->study_time * 3600;
                }
                if ($value2->test_time && strpos($value2->test_time, ':')) {
                    $SumExclusive +=  explode(':', $value2->test_time)[0] * 3600;
                    $SumExclusive +=  explode(':', $value2->test_time)[1] * 60;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->test_time)[0] * 3600;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->test_time)[1] * 60;
                } else {
                    $SumExclusive +=  $value2->test_time * 3600;
                    $arrayExclusive[$lesson->title] += $value2->test_time * 3600;
                }
                if ($value2->Pre_reading && strpos($value2->Pre_reading, ':')) {
                    $SumExclusive += explode(':', $value2->Pre_reading)[0] * 3600;
                    $SumExclusive += explode(':', $value2->Pre_reading)[1] * 60;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->Pre_reading)[0] * 3600;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->Pre_reading)[1] * 60;
                } else {
                    $SumExclusive += $value2->Pre_reading * 3600;
                    $arrayExclusive[$lesson->title] += $value2->Pre_reading * 3600;
                }
                if ($value2->exercise && strpos($value2->exercise, ':')) {
                    $SumExclusive += explode(':', $value2->exercise)[0] * 3600;
                    $SumExclusive += explode(':', $value2->exercise)[1] * 60;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->exercise)[0] * 3600;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->exercise)[1] * 60;
                } else {
                    $SumExclusive += $value2->exercise * 3600;
                    $arrayExclusive[$lesson->title] += $value2->exercise * 3600;
                }
                if ($value2->Summarizing && strpos($value2->Summarizing, ':')) {
                    $SumExclusive += explode(':', $value2->Summarizing)[0] * 3600;
                    $SumExclusive += explode(':', $value2->Summarizing)[1] * 60;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->Summarizing)[0] * 3600;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->Summarizing)[1] * 60;
                } else {
                    $SumExclusive += $value2->Summarizing * 3600;
                    $arrayExclusive[$lesson->title] += $value2->Summarizing * 3600;
                }
                if ($value2->passage && strpos($value2->passage, ':')) {
                    $SumExclusive += explode(':', $value2->passage)[0] * 3600;
                    $SumExclusive += explode(':', $value2->passage)[1] * 60;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->passage)[0] * 3600;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->passage)[1] * 60;
                } else {
                    $SumExclusive += $value2->passage * 3600;
                    $arrayExclusive[$lesson->title] += $value2->passage * 3600;
                }
                if ($value2->Repeat_test && strpos($value2->Repeat_test, ':')) {
                    $SumExclusive += explode(':', $value2->Repeat_test)[0] * 3600;
                    $SumExclusive += explode(':', $value2->Repeat_test)[1] * 60;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->Repeat_test)[0] * 3600;
                    $arrayExclusive[$lesson->title] += explode(':', $value2->Repeat_test)[1] * 60;
                } else {
                    $SumExclusive += $value2->Repeat_test * 3600;
                    $arrayExclusive[$lesson->title] += $value2->Repeat_test * 3600;
                }
            } else {
                if (!in_array($lesson->title, $lessonGids)) {
                    $arrayGeneral[$lesson->title][0] = 0;
                    array_push($lessonGids, $lesson->title);
                }
                if ($value2->test_time && strpos($value2->test_time, ':')) {
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->test_time)[0] * 3600;
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->test_time)[1] * 60;
                } else {
                    $arrayGeneral[$lesson->title][0] += $value2->test_time * 3600;
                }
                if ($value2->study_time && strpos($value2->study_time, ':')) {
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->study_time)[0] * 3600;
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->study_time)[1] * 60;
                } else {
                    $arrayGeneral[$lesson->title][0] += $value2->study_time * 3600;
                }
                if ($value2->Pre_reading && strpos($value2->Pre_reading, ':')) {
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->Pre_reading)[0] * 3600;
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->Pre_reading)[1] * 60;
                } else {
                    $arrayGeneral[$lesson->title][0] += $value2->Pre_reading * 3600;
                }
                if ($value2->exercise && strpos($value2->exercise, ':')) {
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->exercise)[0] * 3600;
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->exercise)[1] * 60;
                } else {
                    $arrayGeneral[$lesson->title][0] += $value2->exercise * 3600;
                }
                if ($value2->Summarizing && strpos($value2->Summarizing, ':')) {
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->Summarizing)[0] * 3600;
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->Summarizing)[1] * 60;
                } else {
                    $arrayGeneral[$lesson->title][0] += $value2->Summarizing * 3600;
                }
                if ($value2->passage && strpos($value2->passage, ':')) {
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->passage)[0] * 3600;
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->passage)[1] * 60;
                } else {
                    $arrayGeneral[$lesson->title][0] += $value2->passage * 3600;
                }
                if ($value2->Repeat_test && strpos($value2->Repeat_test, ':')) {
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->Repeat_test)[0] * 3600;
                    $arrayGeneral[$lesson->title][0] += explode(':', $value2->Repeat_test)[1] * 60;
                } else {
                    $arrayGeneral[$lesson->title][0] += $value2->Repeat_test * 3600;
                }
            }
            if ($value2->test_time && strpos($value2->test_time, ':')) {
                $sum += explode(':', $value2->test_time)[0] * 3600;
                $sum += explode(':', $value2->test_time)[1] * 60;
            } else {
                $sum += $value2->test_time * 3600;
            }
            if ($value2->study_time && strpos($value2->study_time, ':')) {
                $sum += explode(':', $value2->study_time)[0] * 3600;
                $sum += explode(':', $value2->study_time)[1] * 60;
            } else {
                $sum += $value2->study_time * 3600;
            }
            if ($value2->Pre_reading && strpos($value2->Pre_reading, ':')) {
                $sum += explode(':', $value2->Pre_reading)[0] * 3600;
                $sum += explode(':', $value2->Pre_reading)[1] * 60;
            } else {
                $sum += $value2->Pre_reading * 3600;
            }
            if ($value2->exercise && strpos($value2->exercise, ':')) {
                $sum += explode(':', $value2->exercise)[0] * 3600;
                $sum += explode(':', $value2->exercise)[1] * 60;
            } else {
                $sum += $value2->exercise * 3600;
            }
            if ($value2->Summarizing && strpos($value2->Summarizing, ':')) {
                $sum += explode(':', $value2->Summarizing)[0] * 3600;
                $sum += explode(':', $value2->Summarizing)[1] * 60;
            } else {
                $sum += $value2->Summarizing * 3600;
            }
            if ($value2->passage && strpos($value2->passage, ':')) {
                $sum += explode(':', $value2->passage)[0] * 3600;
                $sum += explode(':', $value2->passage)[1] * 60;
            } else {
                $sum += $value2->passage * 3600;
            }
            if ($value2->Repeat_test && strpos($value2->Repeat_test, ':')) {
                $sum += explode(':', $value2->Repeat_test)[0] * 3600;
                $sum += explode(':', $value2->Repeat_test)[1] * 60;
            } else {
                $sum += $value2->Repeat_test * 3600;
            }
        }
        // جمع عمومی ها
        $SumGeneral = $sum - $SumExclusive;
        $total_minutes3 = floor($SumGeneral / 60);
        $hours3 = floor($total_minutes3 / 60);
        $minutes3 = $total_minutes3 % 60;
        $SumGeneral = $hours3 . '.' . $minutes3;
        // sum all
        $total_minutes = floor($sum / 60);
        $hours = floor($total_minutes / 60);
        $minutes = $total_minutes % 60;
        $sum = $hours . '.' . $minutes;
        // جمع تخصصی ها
        $total_minutes2 = floor($SumExclusive / 60);
        $hours2 = floor($total_minutes2 / 60);
        $minutes2 = $total_minutes2 % 60;
        $SumExclusive = $hours2 . '.' . $minutes2;

        $num_week = Week::where('id', $week_id)->first();
        $num = $num_week->num;

        return response()->json(['sum' => $sum, 'SumExclusive' => $SumExclusive, 'SumGeneral' => $SumGeneral, 'arrayGeneral' => $arrayGeneral, 'arrayExclusive' => $arrayExclusive, 'lessons' => $lessons]);
    }

    // گزارش بین دو روز
    public function all_day_between(Request $request)
    {
        $date1 = Verta::parse($request->date1 . ' ' . '00:00:01');
        $date2 = Verta::parse($request->date2 . ' ' . '23:59:00');

        $date1 = $date1->timestamp;
        $date2 = $date2->timestamp;

        $lesson_id = $request->lessonId;

        $stuId = explode(';', $request->token)[1];

        $days = DB::table('edu_plan')
            ->where('stu_id', $stuId)
            ->where('date_time', '>=', $date1)
            ->where('date_time', '<=', $date2)
            ->groupBy('date_time', 'day', 'week_id')
            ->select(
                'date_time',
                'day',
                'week_id',
                DB::raw('count(*) as total')
            )
            ->get();

        if ($lesson_id) {
            foreach ($days as $key => $value) {

                $days[$key]->h = DB::table('edu_plan')
                    ->where('stu_id', $stuId)
                    ->where('week_id', $value->week_id)
                    ->where('day', $value->day)
                    ->where('l_id', $lesson_id)
                    ->select('test_time', 'study_time', 'test_count', 'Pre_reading', 'exercise', 'Summarizing', 'passage', 'Repeat_test')
                    ->get();

                $days[$key]->h_org = 0;
                $days[$key]->test_count = 0;

                $days[$key] = Tools::sumStudyTime_2_secound_in_2day_between($days[$key]);

                $days[$key]->sumStudyTime = Tools::convertSecond_2_hours($days[$key]->h_org);

                $n = Verta::createTimestamp((int)$days[$key]->date_time);
                $days[$key]->date_time = $n->formatDatetime();

                $days[$key]->h = null;
            }
        } else {
            foreach ($days as $key => $value) {
                $sum = StudySum::where('stu_id', explode(';', $request->token)[1])
                    ->where('week_id', $value->week_id)
                    ->where('day', $value->day)
                    ->select('h_sum', 'normal')
                    ->first();

                $days[$key]->sumStudyTime = $sum->h_sum;
                $days[$key]->normal = $sum->normal;

                $n = Verta::createTimestamp((int)$days[$key]->date_time);
                $days[$key]->date_time = $n->formatDatetime();
            }
        }

        return response()->json(['days' => $days]);
    }

    public function test()
    {
        $file = public_path('csv/mosh.csv');

        $customerArr = $this->csvToArray($file);
        for ($i = 0; $i < count($customerArr); $i++) {
            $stu = new Stu;
            $stu->name = $customerArr[$i]['name'];
            $stu->mobile = $customerArr[$i]['mobile'];
            $stu->nation_code = $customerArr[$i]['nation_code'];
            $stu->mosh_id = $customerArr[$i]['mosh_id'];
            $stu->base_id = $customerArr[$i]['base_id'];
            $stu->r_id = $customerArr[$i]['r_id'];
            $stu->rest = $customerArr[$i]['rest'];
            $stu->pass = $customerArr[$i]['pass'];
            if ($customerArr[$i]['status'] == 1 || $customerArr[$i]['status'] == 2 || $customerArr[$i]['status'] == 3) {
                $stu->status = 1;
            } else {
                $stu->status = $customerArr[$i]['status'];
            }
            $stu->time_added = $customerArr[$i]['time_added'];
            $stu->FirebaseToken = null;
            $stu->save();
        }

        return 'ok';
    }
    function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
    // get planing


    public function get_planing(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];
        $stu = Stu::where('id', $stu_id)->first();
        $present = 60;
        $ending = Planing::where('id', $request->id)->first();
        if ($request->id == 3) {
            if ($stu->rest >= $ending->price) {
                $result_planing = array();
                for ($i = 0; $i <= 6; $i++) {
                    $result_planing[$i] = array();
                    $lesson_ids = array();
                    for ($j = 0; $j < 5; $j++) {
                        // شرط فهمیدن ساعت اول
                        if ($j >= 1) {
                            // تشخیص اینکه دو درس عمومی و تخصصی کنا هم نباشند
                            if (!empty($result_planing[$i][$j - 1])) {
                                if ($result_planing[$i][$j - 1]->status == 1) {
                                    // lesson Tomorrow
                                    if ($i == 6) {
                                        $weekly = DB::table('weekly')
                                            ->where('weekly.stu_id', $stu->id)
                                            ->where('weekly.day', 0)
                                            ->where('weekly.l_id', '!=', null)
                                            ->leftJoin('lesson', 'weekly.l_id', '=', 'lesson.id')
                                            ->whereNotIn('lesson.id', $lesson_ids)
                                            ->where('lesson.status', 0)
                                            ->select('lesson.*', 'weekly.day')
                                            ->get();
                                    } else {
                                        $weekly = DB::table('weekly')
                                            ->where('weekly.stu_id', $stu->id)
                                            ->where('weekly.day', $i + 1)
                                            ->where('weekly.l_id', '!=', null)
                                            ->leftJoin('lesson', 'weekly.l_id', '=', 'lesson.id')
                                            ->whereNotIn('lesson.id', $lesson_ids)
                                            ->where('lesson.status', 0)
                                            ->select('lesson.*', 'weekly.day')
                                            ->get();
                                    }
                                    if (!empty($weekly[0])) {
                                        $index = rand(0, count($weekly) - 1);
                                        array_push($lesson_ids, $weekly[$index]->id);
                                        $result_planing[$i][$j] = $weekly[$index];
                                    } else {
                                        // lesson today
                                        $today_l = DB::table('weekly')
                                            ->where('weekly.stu_id', $stu->id)
                                            ->where('weekly.day', $i)
                                            ->where('weekly.l_id', '!=', null)
                                            ->leftJoin('lesson', 'weekly.l_id', '=', 'lesson.id')
                                            ->whereNotIn('lesson.id', $lesson_ids)
                                            ->where('lesson.status', 0)
                                            ->select('lesson.*', 'weekly.day')
                                            ->get();
                                        if (!empty($today_l[0])) {
                                            $index = rand(0, count($today_l) - 1);
                                            array_push($lesson_ids, $today_l[$index]->id);
                                            $result_planing[$i][$j] = $today_l[$index];
                                        } else {
                                            // important lesson
                                            $l_important = DB::table('lesson')
                                                ->where('lesson.base_id', $stu->base_id)
                                                ->where('lesson.r_id', $stu->r_id)
                                                ->where('lesson.l_important', 1)
                                                ->whereNotIn('lesson.id', $lesson_ids)
                                                // ->where('lesson.status', 0)
                                                ->get();
                                            if (!empty($l_important[0])) {
                                                $index = rand(0, count($l_important) - 1);
                                                array_push($lesson_ids, $l_important[$index]->id);
                                                $result_planing[$i][$j] = $l_important[$index];
                                            } else {
                                                // if this part is empty we full it
                                                $result_planing[$i][$j] = (object)[
                                                    'title' => '',
                                                    'status' => 0,
                                                ];
                                            }
                                        }
                                    }
                                } else {
                                    // lesson Tomorrow
                                    if ($i == 6) {
                                        $weekly = DB::table('weekly')
                                            ->where('weekly.stu_id', $stu->id)
                                            ->where('weekly.day', 0)
                                            ->leftJoin('lesson', 'weekly.l_id', '=', 'lesson.id')
                                            ->whereNotIn('lesson.id', $lesson_ids)
                                            ->where('lesson.status', 1)
                                            ->select('lesson.*', 'weekly.day')
                                            ->get();
                                    } else {
                                        $weekly = DB::table('weekly')
                                            ->where('weekly.stu_id', $stu->id)
                                            ->where('weekly.day', $i + 1)
                                            ->leftJoin('lesson', 'weekly.l_id', '=', 'lesson.id')
                                            ->whereNotIn('lesson.id', $lesson_ids)
                                            ->where('lesson.status', 1)
                                            ->select('lesson.*', 'weekly.day')
                                            ->get();
                                    }
                                    if (!empty($weekly[0])) {
                                        $index = rand(0, count($weekly) - 1);
                                        array_push($lesson_ids, $weekly[$index]->id);
                                        $result_planing[$i][$j] = $weekly[$index];
                                    } else {
                                        // lesson today
                                        $today_l = DB::table('weekly')
                                            ->where('weekly.stu_id', $stu->id)
                                            ->where('weekly.day', $i)
                                            ->leftJoin('lesson', 'weekly.l_id', '=', 'lesson.id')
                                            ->whereNotIn('lesson.id', $lesson_ids)
                                            ->where('lesson.status', 1)
                                            ->select('lesson.*', 'weekly.day')
                                            ->get();
                                        if (!empty($today_l[0])) {
                                            $index = rand(0, count($today_l) - 1);
                                            array_push($lesson_ids, $today_l[$index]->id);
                                            $result_planing[$i][$j] = $today_l[$index];
                                        } else {
                                            // important lesson
                                            $l_important = DB::table('lesson')
                                                ->where('lesson.base_id', $stu->base_id)
                                                ->where('lesson.r_id', $stu->r_id)
                                                ->where('lesson.l_important', 1)
                                                ->whereNotIn('lesson.id', $lesson_ids)
                                                // ->where('lesson.status', 1)
                                                ->get();
                                            if (!empty($l_important[0])) {
                                                $index = rand(0, count($l_important) - 1);
                                                array_push($lesson_ids, $l_important[$index]->id);
                                                $result_planing[$i][$j] = $l_important[$index];
                                            } else {
                                                // if this part is empty we full it
                                                $result_planing[$i][$j] = (object)[
                                                    'title' => '',
                                                    'status' => 1,
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($i == 6) {
                                $weekly = DB::table('weekly')
                                    ->where('weekly.stu_id', $stu->id)
                                    ->where('weekly.day', 0)
                                    ->where('weekly.l_id', '!=', null)
                                    ->leftJoin('lesson', 'weekly.l_id', '=', 'lesson.id')
                                    ->select('lesson.*', 'weekly.day')
                                    ->get();
                            } else {
                                $weekly = DB::table('weekly')
                                    ->where('weekly.stu_id', $stu->id)
                                    ->where('weekly.day', $i + 1)
                                    ->where('weekly.l_id', '!=', null)
                                    ->leftJoin('lesson', 'weekly.l_id', '=', 'lesson.id')
                                    ->select('lesson.*', 'weekly.day')
                                    ->get();
                            }
                            if (!empty($weekly[0])) {
                                $index = rand(0, count($weekly) - 1);
                                array_push($lesson_ids, $weekly[$index]->id);
                                $result_planing[$i][$j] = $weekly[$index];
                            } else {
                                // lesson today
                                $today_l = DB::table('weekly')
                                    ->where('weekly.stu_id', $stu->id)
                                    ->where('weekly.day', $i)
                                    ->where('weekly.l_id', '!=', null)
                                    ->leftJoin('lesson', 'weekly.l_id', '=', 'lesson.id')
                                    ->select('lesson.*', 'weekly.day')
                                    ->get();
                                if (!empty($today_l[0])) {
                                    $index = rand(0, count($today_l) - 1);
                                    array_push($lesson_ids, $today_l[$index]->id);
                                    $result_planing[$i][$j] = $today_l[$index];
                                } else {
                                    // important lesson
                                    $l_important = DB::table('lesson')
                                        ->where('lesson.base_id', $stu->base_id)
                                        ->where('lesson.r_id', $stu->r_id)
                                        ->where('lesson.l_important', 1)
                                        ->get();
                                    if (!empty($l_important[0])) {
                                        $index = rand(0, count($l_important) - 1);
                                        array_push($lesson_ids, $l_important[$index]->id);
                                        $result_planing[$i][$j] = $l_important[$index];
                                    } else {
                                        // if this part is empty we full it
                                        $result_planing[$i][$j] = (object)[
                                            'title' => '',
                                            'status' => 0,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }

                // save pdf
                $date = Verta::createTimestamp(time());
                $date = $date->formatJalaliDate();
                $data = [
                    'data' => $result_planing, 'date' => $date, 'name' => $stu->name,
                    'appname' => $this->appname,
                ];
                $pdf = PDF::loadView('myPDF', $data);
                $filename = time() . 'm' . rand(0, 9) . '.pdf';
                $pdf->save(public_path('/planfiles/' . $filename));

                $stu->rest = $stu->rest - $ending->price;
                if ($stu->update()) {
                    $mosh = Mosh::where('code', $stu->mosh_id)->first();
                    $mosh->rest = $mosh->rest + ($ending->price * ($present / 100));
                    if ($mosh->update()) {
                        DB::table('history_planing')->insert([
                            'stu_id' => $stu_id,
                            'planing_id' => $ending->id,
                            'mosh_id' => $stu->mosh_id,
                            'addr' => $filename,
                            'time_added' => time(),
                        ]);
                    }
                    $title = "خرید بسته ی برنامه " . $ending->title . " آماده توسط " . $stu->name;
                    $text = "مبلغ " . $ending->price * ($present / 100) . " تومان به اعتبار شما افزوده شد";
                    $notification = new FirebaseController;
                    $notification->spn($mosh->FirebaseToken, $title, $text);
                }

                return response()->json(["link" => '/planfiles/' . $filename, 'status' => 1]);
            } else {
                return response()->json(["link" => '', 'status' => 0]);
            }
        } else if ($request->id == 9) {
            if ($stu->rest >= $ending->price) {
                $stu->rest = $stu->rest - $ending->price;
                if ($stu->update()) {
                    DB::table('history_planing')->insert([
                        'stu_id' => $stu_id,
                        'planing_id' => $ending->id,
                        'mosh_id' => $stu->mosh_id,
                        'time_added' => time(),
                        'ready' => 0,
                        'status' => 0,
                    ]);
                    $mosh = Mosh::where('code', $stu->mosh_id)->first();
                    $title = 'درخواست برنامه ' . $ending->title;
                    $text = "درخواست برنامه از طرف دانش آموز " . $stu->name;
                    $notification = new FirebaseController;
                    $notification->spn($mosh->FirebaseToken, $title, $text);
                    return response()->json(['status' => 1]);
                }
            } else {
                return response()->json(['status' => 0]);
            }
        } else {
            if ($stu->rest >= $ending->price) {
                $plan_exam = DB::table('plan_exam')->where('planing_id', $ending->id)->first();
                $stu->rest = $stu->rest - $ending->price;
                if ($stu->update()) {
                    if ($request->type == '1') {
                        $mosh = Mosh::where('code', $stu->mosh_id)->first();
                        $mosh->rest = $mosh->rest + ($ending->price * ($present / 100));

                        if ($mosh->update()) {
                            DB::table('history_planing')->insert([
                                'stu_id' => $stu_id,
                                'planing_id' => $ending->id,
                                'mosh_id' => $stu->mosh_id,
                                'addr' => $plan_exam->file,
                                'time_added' => time(),
                            ]);
                            $title = "خرید بسته ی برنامه " . $ending->title . " آماده توسط " . $stu->name;
                            $text = "مبلغ " . $ending->price * ($present / 100) . " تومان به اعتبار شما افزوده شد";
                            $notification = new FirebaseController;
                            $notification->spn($mosh->FirebaseToken, $title, $text);
                            return response()->json(["link" => '/planfiles/' . $plan_exam->file, 'status' => 1]);
                        }
                    } else {
                        DB::table('history_planing')->insert([
                            'stu_id' => $stu_id,
                            'planing_id' => $ending->id,
                            'mosh_id' => $stu->mosh_id,
                            'time_added' => time(),
                            'ready' => 0,
                            'status' => 0,
                        ]);
                        $mosh = Mosh::where('code', $stu->mosh_id)->first();
                        $title = 'درخواست برنامه ' . $ending->title;
                        $text = "درخواست برنامه از طرف دانش آموز " . $stu->name;
                        $notification = new FirebaseController;
                        $notification->spn($mosh->FirebaseToken, $title, $text);
                        return response()->json(['status' => 1]);
                    }
                }
            } else {
                return response()->json(["link" => '', 'status' => 0]);
            }
        }
    }

    public function show_planing(Request $request)
    {
        $ending = Planing::where('id', $request->id)->first();
        // if (!empty($ending[0])) {
        if ($ending->is_end) {
            return response()->json($ending);
        } else {
            $planings = DB::table('planing')
                ->where('parent', $request->id)
                ->where('is_ready', $request->is_ready)
                ->get();

            return response()->json($planings);
        }
        // }
    }

    public function show_price(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];
        $stu = Stu::where('id', $stu_id)->first();
        if ($request->id == 3 || $request->id == 9) {
            $ending = Planing::where('id', $request->id)->first();
            if ($stu->rest >= $ending->price) {
                return response()->json(
                    ['status' => 1, 'title' => $ending->title, 'price' => $ending->price]
                );
            } else {
                return response()->json(
                    ['status' => 0, 'title' => $ending->title, 'price' => $ending->price]
                );
            }
        } else {
            $ending = Planing::where('id', $request->id)->first();

            if ($ending->is_end) {
                if ($stu->rest >= $ending->price) {
                    return response()->json(
                        ['status' => 1, 'title' => $ending->title, 'price' => $ending->price]
                    );
                } else {
                    return response()->json(
                        ['status' => 0, 'title' => $ending->title, 'price' => $ending->price]
                    );
                }
            } else {
                $planings = DB::table('planing')
                    ->where('parent', $request->id)
                    // ->where('is_ready', $request->is_ready)
                    ->get();

                return response()->json(
                    ['planings' => $planings]
                );
            }
        }
    }

    // chat 
    public function get_chat(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];
        $stu = Stu::where('id', $stu_id)->first();
        $mosh = Mosh::where('code', $stu->mosh_id)->first();

        $chats = Chat::where('stu_id', $stu_id)
            ->where('mosh_id', $mosh->code)
            ->where('type', 1)
            ->orderBy('id', 'desc')
            ->get();

        Chat::where('stu_id', $stu_id)
            ->where('mosh_id', $mosh->code)
            ->where('current', 0)
            ->orderBy('id', 'desc')
            ->limit('20')
            ->update([
                'view' => 1
            ]);

        foreach ($chats as $key => $value) {
            $n = Verta::createTimestamp((int) $chats[$key]->date_time);
            $chats[$key]->date_time = $n->formatGregorian('H:i');
        }

        return response()->json(['mosh' => $mosh, 'all_chat' => $chats]);
    }

    public function send_chat(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];
        $stu = Stu::where('id', $stu_id)->first();
        $mosh = Mosh::where('code', $stu->mosh_id)->first();
        $typeFile = $request->typeFile; // image==1 voice==2 document==3 

        $chat = new Chat;
        if ($request->file) {
            $folderName = 'img';
            switch ($typeFile) {
                case 1:
                    break;
                case 2:
                    $folderName = 'voice';
                    $chat->duration = $request->duration;
                    break;
                case 2:
                    $folderName = 'document';
                    break;

                default:
                    break;
            }

            $fileName = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move(public_path('/chat/' . $folderName), $fileName);

            $fileAddr = '/chat/' . $folderName . '/' . $fileName;  //$request->root() . 
            $chat->text = $fileAddr;
            $chat->isimg = $typeFile;
        } else {
            $chat->text = $request->text;
            $chat->isimg = 0;
        }
        $chat->stu_id = $stu_id;
        $chat->mosh_id = $stu->mosh_id;
        if (!$request->private) {
            $chat->type = 1;
        } else {
            $chat->type = 0;
        }
        $chat->date_time = time();
        $chat->current = 1;
        if ($chat->save()) {
            if ($request->file) {
                $title = "عکس از طرف دانش آموز " . $stu->name;
                $text = '';
            } else {
                $title = "پیام از طرف دانش آموز " . $stu->name;
                $text = $request->text;
            }
            $notification = new FirebaseController;
            $notification->spn($mosh->FirebaseToken, $title, $text);
            return response()->json(['send_status' => 'ok']);
        }
    }

    public function GetNumChatStu(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];
        $stu = Stu::where('id', $stu_id)->first();

        // تعداد چت های جدید برای دانش آموز
        $new_chat = Chat::where('view', 0)
            ->where('stu_id', $stu_id)
            ->where('mosh_id', $stu->mosh_id)
            ->where('current', 0)
            ->count();

        return response()->json([
            'new_chat' => $new_chat
        ]);
    }

    // cash

    public function transaction(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];
        $transaction = Transaction::where('stu_id', $stu_id)->get();

        foreach ($transaction as $key => $value) {
            $n = Verta::createTimestamp((int) $transaction[$key]->date_time);
            $transaction[$key]->date_time = $n->formatJalaliDate();
        }

        return response()->json(['transaction' => $transaction]);
    }

    public function pay($token, $price)
    {
        $stu_id = explode(';', $token)[1];
        $stu = Stu::where('id', $stu_id)->first();
        $mail = 'www.dasaeid.a123@gmail.com';

        $zaring = new zarinpal();
        $res = $zaring->pay($price, $mail, $stu->mobile);
        Session::put('stu_id', $stu_id . ';' . $price);
        return redirect('https://www.zarinpal.com/pg/StartPay/' . $res);
    }

    public function buyback(Request $request)
    {
        // $MerchantID = '5e682ada-3b69-11e8-aaf3-005056a205be';
        $MerchantID = '90671c3c-59b9-4b8f-984d-7849c52eb5dc';
        $Authority = $request->get('Authority');

        // ما در اینجا مبلغ مورد نظر را بصورت دستی نوشتیم اما در پروژه های واقعی باید از دیتابیس بخوانیم
        $stu_id = explode(';', Session::get('stu_id'))[0];
        $price = explode(';', Session::get('stu_id'))[1];
        $Amount = $price;
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
                $stu = Stu::where('id', $stu_id)->first();
                $stu->rest = $stu->rest + $price;
                $this->status_pay = 1;
                $this->transaction_send($stu->mobile, $result['RefID'], $price, 1);
                if ($stu->update()) {
                    Session::forget('stu_id');
                    return view('pay', ['mes' => 'در صورت وجود هرگونه مشکل با شماره ی 09100045125 در پیامرسان واتساپ در ارتباط باشید', 'result' => $result, 'mes2' => 'حساب شما به مبلغ ' . $price . ' تومان شارژ شد.']);
                }
            } else {
                return view('pay', ['mes' => 'در صورت وجود هرگونه مشکل با شماره ی 09100045125 در پیامرسان واتساپ در ارتباط باشید', 'result' => $result, 'mes2' => 'خطا در انجام عملیات']);
            }
        } else {
            $result = array("Status" => 121);
            return view('pay', ['mes' => 'در صورت وجود هرگونه مشکل با شماره ی 09100045125 در پیامرسان واتساپ در ارتباط باشید', 'result' => $result, 'mes2' => 'پرداخت لغو شده است']);
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

    public function backapp()
    {
        // $url = env('ANDROID_URL');
        $url = 'momtazapp://open';
        return redirect($url);
    }
    // برنامه های دانش آموز
    public function get_plannig_list(Request $request)
    {
        $stu_id = explode(';', $request->token)[1];
        // $stu = Stu::where('id', $stu_id)->first();

        $all = DB::table('history_planing')
            ->where('history_planing.stu_id', $stu_id)
            ->leftJoin('planing', 'history_planing.planing_id', '=', 'planing.id')
            ->select('history_planing.*', 'planing.title', 'planing.is_exam', 'planing.id as plan_id')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($all as $key => $value) {
            // گرفتن تمام دسته های مربوط به آزمونی ها
            if ($value->is_exam) {
                $parent = 1;
                $plan_id = $value->plan_id;
                $title = array();
                while ($parent != 0) {
                    $planing_exam = Planing::where('id', $plan_id)->first();
                    $parent = $planing_exam->parent;
                    $plan_id = $planing_exam->parent;
                    array_push($title, $planing_exam->title);
                }
                $all[$key]->title = implode(' - ', array_reverse($title));
            }
            // تبدیل زمان به فارسی
            $n = Verta::createTimestamp((int) $value->time_added);
            $all[$key]->time_added = $n->format('Y-n-j H:i');
            if ($value->time_update) {
                $u = Verta::createTimestamp((int) $value->time_update);
                $all[$key]->time_update = $u->format('Y-n-j H:i');
            }
        }

        return $all;
    }

    // ========================================================== مشاور ----------------دانش آموزانی که دیروز برنامه نفرستادن---------------------------
    public function get_stu_mosh(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $stu_id = Stu::where('mosh_id', $mosh_id)->select('id')->get();
        $mosh = Mosh::where('code', $mosh_id)->first();

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

        // گرفتن دانش آموزانی که دیروز برنامه نفرستادن
        $stu = DB::table('stu')
            ->where('stu.mosh_id', $mosh_id)
            ->whereNotIn('stu.id', $edus)
            ->get();
        // گرفتن دانش آموزانی که دیروز برنامه فرستادن
        $stuSend = DB::table('stu')
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

        // تعداد چت های جدید برای مشاور
        $new_chat = Chat::where('view', 0)
            ->where('mosh_id', $mosh_id)
            ->where('current', 1)
            ->count();

        // تعداد درخواست های جدید برنامه برای مشاور
        $new_planing = DB::table('history_planing')
            ->where('mosh_id', $mosh_id)
            ->where('ready', 0)
            ->where('status', 0)
            ->count();

        return response()->json(['all' => [
            'stu' => $stu,
            'new_chat' => $new_chat,
            'new_planning' => $new_planing,
            'mosh' => $mosh,
            'appname' => $this->appname,
            'all_week' => $all_week,
        ]]);
    }
    // ----------------- چت
    public function show_all_message(Request $request)
    {

        $checkLogin = Tools::checkTokenMosh($request->token);

        if (!$checkLogin['success'])  // check Login user 
            return response()->json($this->loginErrorArr);

        $mosh = $checkLogin['user'];

        $mosh_id = $mosh->code;

        $new_chats = DB::table('chat')->where('view', 0)
            ->where('mosh_id', $mosh_id)
            ->where('current', 1)
            ->groupBy('stu_id')->select('stu_id', DB::raw('count(*) as total'))->get();

        $stuarrids = array();
        foreach ($new_chats as $key => $value) {
            $stuarrids += [$value->total => $value->stu_id];
        }

        $stu = DB::table('stu')->where('mosh_id', $mosh_id)
            ->get();

        $arrall = array();
        foreach ($stu as $key => $value) {
            if (in_array($value->id, $stuarrids)) {
                $stu[$key]->new = 1;
                $stu[$key]->count = array_search($value->id, $stuarrids);
                array_push($arrall, $value);
                unset($stu[$key]);
            } else {
                $stu[$key]->new = 0;
                $stu[$key]->count = 0;
            }
        }

        $result = $stu->merge($arrall);
        $result = json_decode($result, true);
        $result = array_reverse($result);

        return response()->json(['stu' => $result]);
    }

    public function show_pv(Request $request)
    {

        $checkLogin = Tools::checkTokenMosh($request->token);

        if (!$checkLogin['success'])  // check Login user 
            return response()->json($this->loginErrorArr);

        $mosh = $checkLogin['user'];

        $mosh_id = $mosh->code;

        $stu_id = $request->stu_id;

        $all_message = Chat::where('stu_id', $stu_id)
            ->where('mosh_id', $mosh_id)
            // get with lasy load
            ->orderBy('id', 'desc')->get();

        foreach ($all_message as $key => $value) {
            $n = Verta::createTimestamp((int) $all_message[$key]->date_time);
            $all_message[$key]->date_time = $n->formatGregorian('H:i');
        }

        if ($request->isnew) {
            Chat::where('stu_id', $stu_id)
                ->where('mosh_id', $mosh_id)
                ->where('current', 1)
                ->limit(20)->orderBy('id', 'desc')->update([
                    'view' => 1
                ]);
        }

        return response()->json(['all_message' => $all_message]);
    }

    public function send_pv(Request $request)
    {

        $stu_id = $request->stu_id;
        $checkLogin = Tools::checkTokenMosh($request->token);

        if (!$checkLogin['success'])  // check Login user 
            return response()->json($this->loginErrorArr);

        $mosh = $checkLogin['user'];

        $mosh_id = $mosh->code;

        $stu = Tools::getStuWithId($stu_id);
        
        $typeFile = $request->typeFile; // image==1 voice==2 document==3 

        $chat = new Chat;

        if ($request->file) {
            $folderName = 'img';
            switch ($typeFile) {
                case 1:
                    break;
                case 2:
                    $folderName = 'voice';
                    $chat->duration = $request->duration;
                    break;
                case 2:
                    $folderName = 'document';
                    break;

                default:
                    break;
            }

            $fileName = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move(public_path('/chat/' . $folderName), $fileName);

            $fileAddr = '/chat/' . $folderName . '/' . $fileName;  //$request->root() . 
            $chat->text = $fileAddr;
            $chat->isimg = $typeFile;
        } else {
            $chat->text = $request->text;
            $chat->isimg = 0;
        }

        $chat->stu_id = $stu_id;
        $chat->mosh_id = $mosh_id;
        $chat->type = 1;
        $chat->date_time = time();
        $chat->current = 0;

        if ($chat->save()) {
            if ($request->file) {
                $title = "فایلی از طرف مشاور ";
                $text = '';
            } else {
                $title = "پیام از طرف مشاور ";
                $text = $request->text;
            }
            $notification = new FirebaseController;

            $notification->spn($stu->FirebaseToken, $title, $text);

            return response()->json(['send_status' => 'ok']);
        }

        return response()->json(['send_status' => false]);
    }

    public function GetNumChatMosh(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        // $mosh = Stu::where('code', $mosh_id)->first();

        // تعداد چت های جدید برای مشاور
        $new_chat = Chat::where('view', 0)
            ->where('mosh_id', $mosh_id)
            ->where('current', 1)
            ->count();

        // تعداد درخواست های جدید برنامه برای مشاور
        $new_planing = DB::table('history_planing')
            ->where('mosh_id', $mosh_id)
            ->where('ready', 0)
            ->where('status', 0)
            ->count();


        return response()->json([
            'new_chat' => $new_chat,
            'new_planning' => $new_planing,
        ]);
    }

    // ------------------ درخواست برنامه
    public function request_planning(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $present = 60;

        $all_request = DB::table('history_planing')
            ->where('history_planing.mosh_id', $mosh_id)
            ->where('history_planing.ready', 0)
            ->where('history_planing.status', 0)
            ->leftJoin('stu', 'history_planing.stu_id', '=', 'stu.id')
            ->leftJoin('planing', 'history_planing.planing_id', '=', 'planing.id')
            ->select('history_planing.id', 'history_planing.stu_id', 'history_planing.time_added', 'stu.name', 'stu.img', 'planing.is_exam', 'planing.title', 'planing.id as plan_id', 'planing.parent', 'planing.price')
            ->get();

        foreach ($all_request as $key => $value) {
            if ($value->is_exam) {
                $parent = 1;
                $plan_id = $value->plan_id;
                $title = array();
                while ($parent != 0) {
                    $planing_exam = Planing::where('id', $plan_id)->first();
                    $parent = $planing_exam->parent;
                    $plan_id = $planing_exam->parent;
                    array_push($title, $planing_exam->title);
                }
                $all_request[$key]->title = implode(' - ', array_reverse($title));
            }
            // jalali date
            $n = Verta::createTimestamp((int)$all_request[$key]->time_added);
            $all_request[$key]->time_added = $n->formatDatetime();
            // money get moshaver
            $all_request[$key]->price = $value->price * ($present / 100);
        }

        return response()->json($all_request);
    }

    public function reject_request(Request $request)
    {
        $planing_history = DB::table('history_planing')->where('id', $request->id)->update([
            'status' => -1
        ]);
        $planing_history = DB::table('history_planing')->where('id', $request->id)->first();
        $stu = Stu::where('id', $planing_history->stu_id)->first();
        $planning = Planing::where('id', $planing_history->planing_id)->first();
        $title = "درخواست برنامه رد شد";
        $text = "درخواست برنامه " . $planning->title . ' از طرف مشاور شما رد شد.';
        $notification = new FirebaseController;
        $notification->spn($stu->FirebaseToken, $title, $text);

        return 'ok';
    }

    public function accept_request(Request $request)
    {
        $stu_id = $request->stu_id;
        $stu = Stu::where('id', $stu_id)->first();

        if ($stu->base_id == 3) {
            $lesson = Lesson::where('r_id', $stu->r_id)->orderBy('base_id', 'desc')->get();
            foreach ($lesson as $key => $value) {
                if ($lesson[$key]->base_id == 3) {
                    $lesson[$key]->title = $lesson[$key]->title . ' دوازدهم';
                } elseif ($lesson[$key]->base_id == 2) {
                    $lesson[$key]->title = $lesson[$key]->title . ' یازدهم';
                } elseif ($lesson[$key]->base_id == 1) {
                    $lesson[$key]->title = $lesson[$key]->title . ' دهم';
                }
            }
        } else {
            $lesson = Lesson::where('base_id', $stu->base_id)->where('r_id', $stu->r_id)->get();
        }
        return response()->json($lesson);
    }

    public function get_data_planing(Request $request)
    {
        $data = json_decode($request->data);
        $desc = json_decode($request->desc);
        $planning_id = $request->planning_id;
        $request_id = $request->id;
        $stu_id = $request->stu_id;
        $mosh_id = explode(';', $request->token)[1];
        $present = 60;

        $planning = Planing::where('id', $planning_id)->first();
        $history_planning = history_planing::where('id', $request_id)->first();
        $stu = Stu::where('id', $stu_id)->first();
        $mosh = Mosh::where('code', $mosh_id)->first();

        if ($data) {
            // return 'data';
            $date = Verta::createTimestamp(time());
            $date = $date->formatJalaliDate();
            $data = [
                'data' => $data,
                'desc' => $desc,
                'date' => $date,
                'name' => $stu->name,
                'mosh' => $mosh->name,
                'isexam' => $planning->is_exam,
                'notready' => 1,
                'appname' => $this->appname,
            ];
            $pdf = PDF::loadView('myPDF', $data);
            $filename = time() . 'm' . rand(0, 9) . '.pdf';
            $pdf->save(public_path('/planfiles/' . $filename));
        } else {

            $image = $request->image;
            $type = $request->type;
            $type = str_replace("'", "", $type);
            $filename = time() . 'm' . rand(1, 99) . '.' . $type;
            \File::put(public_path() . '/planfiles/' . $filename, base64_decode($image));
        }

        // update history planning
        $history_planning->status = 1;
        $history_planning->addr = $filename;
        $history_planning->time_update = time();
        $history_planning->update();

        // transfer money to moshaver
        $mosh->rest = $mosh->rest + $planning->price * ($present / 100);
        $mosh->update();

        $title = "برنامت آمادس!!";
        $text = "برای دریافت برنامه به بخش برنامه های من برو!!";
        $notification = new FirebaseController;
        $notification->spn($stu->FirebaseToken, $title, $text);

        // return response()->json(['dd'=>$data[0]]);
    }

    // ---------------- گروه
    public function add_groups(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $stu_ids = json_decode($request->stu_ids);
        $group = new Groups;
        $group->title = $request->title;
        $group->mosh_id = $mosh_id;

        if ($group->save()) {
            foreach ($stu_ids as $key => $value) {
                $group_stu = new group_stu;
                $group_stu->group_id = $group->id;
                $group_stu->stu_id = $value;
                $group_stu->save();
            }
            return response()->json('ok');
        }
    }

    public function show_groups(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $groups = Groups::where('mosh_id', $mosh_id)->get();

        foreach ($groups as $key => $value) {
            $groups[$key]->count = group_stu::where('group_id', $value->id)->count();
        }

        return response()->json(['groups' => $groups]);
    }

    public function student_mosh(Request $request)
    {
        $mosh_id = explode(';', $request->token)[1];
        $students = Stu::where('mosh_id', $mosh_id)->get();

        return $students;
    }

    public function stu_group(Request $request)
    {
        $group_id = $request->group_id;
        $ids_stu = group_stu::where('group_id', $group_id)->select('stu_id')->get();
        $stu = Stu::whereIn('id', $ids_stu)->get();
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

    public function del_group(Request $request)
    {
        $GroupId = $request->group_id;
        if (Groups::where('id', $GroupId)->delete()) {
            return 'ok delete';
        }
    }

    public function edit_group(Request $request)
    {
        $GroupId = $request->group_id;
        $MoshId = explode(';', $request->token)[1];

        $stu_ids = group_stu::where('group_id', $GroupId)->select('stu_id')->get();
        $ids = array();
        foreach ($stu_ids as $key => $value) {
            array_push($ids, $value->stu_id);
        }
        $students = Stu::where('mosh_id', $MoshId)->get();
        foreach ($students as $key => $value) {
            if (in_array($value->id, $ids)) {
                $students[$key]->membership = 1;
            } else {
                $students[$key]->membership = 0;
            }
        }
        return $students;
    }

    public function edit_stu_group(Request $request)
    {
        $stu_ids = json_decode($request->stu_ids);
        $GroupId = $request->group_id;
        group_stu::where('group_id', $GroupId)->delete();
        foreach ($stu_ids as $key => $value) {
            $group_stu = new group_stu;
            $group_stu->group_id = $GroupId;
            $group_stu->stu_id = $value;
            $group_stu->save();
        }
        return response()->json('ok');
    }
    // گرفتن نسخه اپلیکیشن
    public function VersionStu()
    {
        return response()->json([
            'version' => $this->VersionStu,
            'url' => $this->UrlVersionStu
        ]);
    }
    public function VersionMOsh()
    {
        return response()->json([
            'version' => $this->VersionMosh,
            'url' => $this->UrlVersionMosh
        ]);
    }

    public function spn(Request $request)
    {
        define('API_ACCESS_KEY', 'AAAA5A9yUBA:APA91bFk-CpbGen9myhgF5OC70LlGFKl0E627vECR9wP3sE9fXiqtl-vVQIqzwpEBEfIFSL2gnpwDx7sX757Xg9AVEBlvJF0DF5X0h_D-NzOYSG1MMnmxteaTjRWBkRp_E7gXJnXuCeG');

        $fcmUrl = "https://fcm.googleapis.com/fcm/send";

        $token = $request->token;


        $notification = [
            'title' => 'پیام از طرف دانش آموز علی اصغری : ',
            'body' => 'سلام آقای حسنی ببخشید بابت دیروز نتونستم به 9 ساعت مطالعه برسونم ولی فردا حتما سعی میکنم انجامش بدم',
            'text' => 'تکست تستی',
            'icon' => 'ic_notification',
            'sound' => 'mySound'
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


        return $result;
    }
}
