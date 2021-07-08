<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\helper\Tools;
use App\Stu;
use App\StudySum;
use DB;

class reportApiController extends Controller

{
    public function report2weekly(Request $request)
    {

        $token = $request->token;
        $weekId1 = $request->weekId1;
        $weekId2 = $request->weekId2;

        $checkLogin = Tools::checkTokenMosh($token);

        if (!$checkLogin['success'])  // check Login user 
            return response()->json($this->loginErrorArr);

        $teacher = $checkLogin['user'];

        $students = Stu::where('mosh_id', $teacher->code)
        ->select('id','name','base_id','r_id','img')
        ->get();

        $studentsIds = [];

        foreach ($students as $value) { array_push($studentsIds,$value->id); }

        $resultWeek1 = Tools::weeklyResultSum($weekId1, $studentsIds);

        $resultWeek2 = Tools::weeklyResultSum($weekId2, $studentsIds);


        foreach ($students as $i => $stu) {
            $students[$i]->sumStudy_S1 = 0;
            $students[$i]->sumStudy_S2 = 0;

            foreach ($resultWeek1  as $j => $value) { // Survey and get sum second time first week

                if (
                    $value->stu_id ==  $stu->id &&
                    $value->h_sum &&
                    explode(':', $value->h_sum)[0]
                ) {
                    $students[$i]->sumStudy_S1 += explode(':', $value->h_sum)[0] * 3600;

                    if (explode(':', $value->h_sum)[1])
                        $students[$i]->sumStudy_S1 += explode(':', $value->h_sum)[1] * 60;
                }
            }

            foreach ($resultWeek2  as $j => $value) {// Survey and get sum second time latter week

                if (
                    $value->stu_id ==  $stu->id &&
                    $value->h_sum &&
                    explode(':', $value->h_sum)[0]
                ) {
                    $students[$i]->sumStudy_S2 += explode(':', $value->h_sum)[0] * 3600;

                    if (explode(':', $value->h_sum)[1])
                        $students[$i]->sumStudy_S2 += explode(':', $value->h_sum)[1] * 60;
                }
            }

            $students[$i]->Progress = (($students[$i]->sumStudy_S2 / $students[$i]->sumStudy_S1)-1) * 100;
            $students[$i]->Progress = round($students[$i]->Progress,2);

            $students[$i]->sumStudy1 = Tools::convertSecond_2_hours($students[$i]->sumStudy_S1);
            $students[$i]->sumStudy2 = Tools::convertSecond_2_hours($students[$i]->sumStudy_S2);
            
        }


        return response()->json([
            
            'students' => $students,

        ]);
    }
}
