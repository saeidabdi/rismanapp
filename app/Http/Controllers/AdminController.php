<?php

namespace App\Http\Controllers;

use App\Admin;
use App\Lesson;
use App\Mosh;
use App\Options;
use App\Planing;
use App\Stu;
use Illuminate\Http\Request;
use Verta;
use DB;
use Session;

class AdminController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function login_admin(Request $request)
    {
        $username = $request->username;
        $pass = $request->pass;

        $logined = Admin::where('username', $username)->where('pass', $pass)->first();

        if ($logined) {
            Session::put('username', $username);
            return $logined;
        }
    }

    public function dashbord()
    {
        return view('admin.dashbord');
    }

    public function getuser()
    {
        $username = Session::get('username');
        $user = Admin::where('username', $username)->first();
        return $user;
    }

    public function slider()
    {
        return view('admin.slider');
    }

    public function exit_admin()
    {
        $exit_user = Session::forget('username');
        if ($exit_user) {
            return true;
        }
    }

    // option
    public function formSubmit(Request $request)
    {
        // ini_set("memory_limit", "500000");
        // ini_set('post_max_size', '500000');
        // ini_set('upload_max_filesize', '500000');
        // $validator = Validator::make($request->all(), [
        //     'file' => 'max:500000', //5MB 
        // ]);
        $imageName = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move(public_path('images'), $imageName);

        $options = new Options;
        $options->type = 0;
        $options->vlaue = $imageName;

        if ($options->save()) {
            return response()->json($imageName);
        }
    }

    public function get_imag_slide()
    {
        $all_img = Options::where('type', 0)->get();

        return response()->json($all_img);
    }

    public function slider_img(Request $request)
    {
        if (Options::where('id', $request->id)->delete()) {
            return response()->json(['mes' => 'عکس اسلایدر حذف شد']);
        }
    }

    public function get_message()
    {
        $all_img = Options::where('type', 2)->first();

        return response()->json($all_img);
    }

    public function edit_message(Request $request)
    {
        Options::where('type', 2)->update([
            'vlaue' => $request->message
        ]);
    }

    // student
    public function stu()
    {
        return view('admin.stu');
    }

    public function get_stu(Request $request)
    {
        if ($request->ar) {
            $stu = DB::table('stu')
                ->where('mosh_id', $request->mosh_id)
                ->orderby('id', 'desc')
                ->get();
        } else {
            $stu = DB::table('stu')
                ->orderby('id', 'desc')
                ->limit(100)
                ->get();
        }
        return $stu;
    }

    public function search_stu(Request $request)
    {
        $stu = DB::table('stu')
            ->where('name', 'like', '%' . $request->name . '%')
            ->get();
        return $stu;
    }

    // lesson
    public function lesson()
    {
        return view('admin.lesson');
    }

    public function add_lesson(Request $request)
    {
        $id = $request->id;
        if ($id) {
            // $new_lesson = Lesson::where('id', $id)->first();
            // $new_lesson->name = $request->name;
            // if ($request->status) {
            //     $new_lesson->status = $request->status;
            // } else {
            //     $new_lesson->status = 0;
            // }
            // $new_lesson->p_id = $request->paye_id;
            // $new_lesson->r_id = $request->reshte_id;

            // if ($new_lesson->update()) {
            //     return response()->json(['mes' => 'درس بروزرسانی شد']);
            // }
        } else {
            $new_lesson = new Lesson;
            $new_lesson->title = $request->name;
            $new_lesson->base_id = $request->paye_id;
            $new_lesson->r_id = $request->reshte_id;
            $new_lesson->status = $request->lesson_status;
            $new_lesson->l_important = $request->lesson_important;

            if ($new_lesson->save()) {
                return response()->json(['mes' => 'درس جدید ایجاد شد']);
            }
        }
    }

    public function get_lesson(Request $request)
    {
        $all_lesson = Lesson::where('base_id', $request->p_id)->where('r_id', $request->r_id)->orderBy('priority')->get();

        return response()->json($all_lesson);
    }

    // mosh
    public function mosh()
    {
        return view('admin.mosh');
    }

    public function get_mosh()
    {
        $mosh = DB::table('mosh')
            ->orderby('id', 'desc')
            ->limit(100)
            ->get();
        return $mosh;
    }

    public function search_mosh(Request $request)
    {
        $stu = DB::table('mosh')
            ->where('name', 'like', '%' . $request->name . '%')
            ->get();
        return $stu;
    }

    public function unactive_mosh(Request $request)
    {
        $mosh = Mosh::where('id', $request->id)->update([
            'status' => -1
        ]);
        if ($mosh) {
            return $mosh;
        }
    }

    // plan
    public function plan()
    {
        return view('admin.plan');
    }

    public function formimgplan(Request $request)
    {
        // ini_set("memory_limit", "500000");
        // ini_set('post_max_size', '500000');
        // ini_set('upload_max_filesize', '500000');
        // $validator = Validator::make($request->all(), [
        //     'file' => 'max:500000', //5MB 
        // ]);
        $imageName = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move(public_path('images'), $imageName);

        return response()->json($imageName);
    }

    public function add_plan(Request $request)
    {
        if ($request->id) {
            $new_plan = Planing::where('id', $request->id)->first();
            $new_plan->title = $request->title;
            $new_plan->parent = $request->parent;
            $new_plan->is_ready = $request->is_ready;
            $new_plan->price = $request->price;
            $new_plan->img = $request->img;
            $new_plan->is_end = $request->is_end;
            $new_plan->is_exam = $request->plan_isexam;

            if ($new_plan->update()) {
                // DB::table('plan_exam')->where('planing_id', $request->id)->update([
                //     'file' => $request->file_addr,
                // ]);
                return response()->json(['id' => $new_plan->id, 'mes' => 'برنامه بروزرسانی شد']);
            }
        } else {
            $new_plan = new Planing;
            $new_plan->title = $request->title;
            $new_plan->parent = $request->parent;
            $new_plan->is_ready = $request->is_ready;
            $new_plan->price = $request->price;
            $new_plan->img = $request->img;
            $new_plan->is_end = $request->is_end;
            $new_plan->is_exam = $request->plan_isexam;

            if ($new_plan->save()) {
                // DB::table('plan_exam')->insert([
                //     'planing' => $new_plan->id,
                //     'file' => $request->file_addr,
                // ]);
                return response()->json(['id' => $new_plan->id, 'mes' => 'برنامه ایجاد شد']);
            }
        }
    }

    public function get_plan()
    {
        // $all_img = Planing::all();
        $all_planing =
            DB::table('planing')
            ->leftJoin('plan_exam', 'planing.id', '=', 'plan_exam.planing_id')
            ->select('planing.*', 'plan_exam.file')
            ->get();

        return response()->json($all_planing);
    }

    public function delete_plan(Request $request)
    {
        if (Planing::where('id', $request->id)->delete()) {
            return response()->json('با موفقیت حذف شد');
        }
    }

    public function get_paln_stu(Request $request)
    {
        $weekly = DB::table('weekly')
            ->where('weekly.stu_id', $request->stu_id)
            ->leftJoin('lesson', 'weekly.l_id', '=', 'lesson.id')
            ->select('weekly.*', 'lesson.title as l_title')
            ->get();
        return $weekly;
    }

    public function weeks()
    {
        $week = DB::table('week')->get();
        foreach ($week as $key => $value) {
            $week[$key]->start = Verta::createTimestamp((int) $value->start);
            $week[$key]->end = Verta::createTimestamp((int) $value->end);
        }
        return view('admin.weeks', ['week' => $week]);
    }

    public function add_week(Request $request)
    {
        $time_start = Verta::parse($request->starttime)->timestamp;
        $endtime = Verta::parse($request->endtime)->timestamp;
        $num_week = $request->num_week;
        $year = Verta::parse($request->starttime)->format('Y');

        DB::table('week')->insert([
            'num' => $num_week,
            'start' => $time_start,
            'end' => $endtime,
            'year' => $year,
        ]);

        return 'ok';
    }
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
            ->where('date_time', '>=', $bamdad + 86400)
            ->select('stu_id')
            ->get();

        // پیدا کردن آیدی هفته کنونی
        $all_week = DB::table('week')->get();
        foreach ($all_week as $key => $value) {
            if ($all_week[$key]->start < time() && $all_week[$key]->end > time()) {
                $week_id = $all_week[$key]->id;
            }
        }

        // گرفتن دانش آموزایی که جز کسایی که دیروز فرستادن نباشند
        $stu = DB::table('stu')
            ->where('stu.mosh_id', $mosh_id)
            ->whereNotIn('stu.id', $edus)
            ->get();

        foreach ($stu as $key => $value) {
            $sum_details = DB::table('edu_plan')
                ->where('week_id', $week_id)
                ->where('stu_id', $value->id)
                ->select('test_time', 'study_time', 'test_count')->get();
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
                    if ($value2->test_time && strpos($value2->test_time, '.')) {
                        $stu[$key]->h_test += explode('.', $value2->test_time)[0] * 3600;
                        $stu[$key]->h_test += explode('.', $value2->test_time)[1] * 60;
                    } else {
                        $stu[$key]->h_test += $value2->test_time * 3600;
                    }
                    if ($value2->study_time && strpos($value2->study_time, '.')) {
                        $stu[$key]->h_study += explode('.', $value2->study_time)[0] * 3600;
                        $stu[$key]->h_study += explode('.', $value2->study_time)[1] * 60;
                    } else {
                        $stu[$key]->h_study += $value2->study_time * 3600;
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
        ]]);
    }

    public function pay()
    {
        return view('pay',['mes'=>'در حال حاضر درگاه پرداخت دردسترس نمیباشد برای افزایش اعتبار با شماره ی 09100045125 در پیامرسان واتساپ در ارتباط باشید']);
    }

    public function paybit()
    {
        return view('pay',['mes'=>'در حال حاضر درگاه پرداخت دردسترس نمیباشد برای خرید کد ورود با شماره ی 09100045125 در پیامرسان واتساپ در ارتباط باشید']);
    }
}
