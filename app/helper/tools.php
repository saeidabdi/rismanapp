<?php

namespace App\helper;

use App\StudySum;
use DB;

class Tools
{
    public function __construct()
    {
    }


    public static function checkTokenStudent($token)
    {

        $mobile = explode(';', $token)[0];
        $stu_id = explode(';', $token)[1];

        $student = DB::table('stu')
            ->where('id', $stu_id)
            ->where('mobile', $mobile)
            ->first();

        if ($student) {
            return [
                'success' => true,
                'user' => $student,
                'type' => 'student',
            ];
        }

        return ['success' => false];
    }

    public static function checkTokenMosh($token)
    {

        $mobile = explode(';', $token)[0];
        $mosh_code = explode(';', $token)[1];

        $mosh = DB::table('mosh')
            ->where('code', $mosh_code)
            ->where('mobile', $mobile)
            ->first();

        if ($mosh) {
            return [
                'success' => true,
                'user' => $mosh,
                'type' => 'moshaver'
            ];
        }

        return ['success' => false];
    }

    public static function weeklyResultSum($weekId, $studentsIds)
    {


        $resultWeek = StudySum::whereIn('stu_id', $studentsIds)
            ->where('week_id', $weekId)
            ->get();

        return $resultWeek;
    }

    public static function convertSecond_2_hours($time_base_seconds)
    {

        $total_minutes = floor($time_base_seconds / 60);
        $hours = floor($total_minutes / 60);
        $minutes = $total_minutes % 60;
        $result = $hours . ':' . $minutes;

        return $result;
    }

    public static function sumStudyTime_2_secound_in_2day_between($eduArr)
    {

       
        foreach ($eduArr->h as $key2 => $value2) {

            $eduArr->test_count += $value2->test_count;
            
            if ($value2->test_time && strpos($value2->test_time, ':')) {
                $eduArr->h_org += explode(':', $value2->test_time)[0] * 3600;
                $eduArr->h_org += explode(':', $value2->test_time)[1] * 60;
            } else {
                $eduArr->h_org += $value2->test_time * 3600;
            }
            if ($value2->study_time && strpos($value2->study_time, ':')) {
                $eduArr->h_org += explode(':', $value2->study_time)[0] * 3600;
                $eduArr->h_org += explode(':', $value2->study_time)[1] * 60;
            } else {
                $eduArr->h_org += $value2->study_time * 3600;
            }
            if ($value2->Pre_reading && strpos($value2->Pre_reading, ':')) {
                $eduArr->h_org += explode(':', $value2->Pre_reading)[0] * 3600;
                $eduArr->h_org += explode(':', $value2->Pre_reading)[1] * 60;
            } else {
                $eduArr->h_org += $value2->Pre_reading * 3600;
            }
            if ($value2->exercise && strpos($value2->exercise, ':')) {
                $eduArr->h_org += explode(':', $value2->exercise)[0] * 3600;
                $eduArr->h_org += explode(':', $value2->exercise)[1] * 60;
            } else {
                $eduArr->h_org += $value2->exercise * 3600;
            }
            if ($value2->Summarizing && strpos($value2->Summarizing, ':')) {
                $eduArr->h_org += explode(':', $value2->Summarizing)[0] * 3600;
                $eduArr->h_org += explode(':', $value2->Summarizing)[1] * 60;
            } else {
                $eduArr->h_org += $value2->Summarizing * 3600;
            }
            if ($value2->passage && strpos($value2->passage, ':')) {
                $eduArr->h_org += explode(':', $value2->passage)[0] * 3600;
                $eduArr->h_org += explode(':', $value2->passage)[1] * 60;
            } else {
                $eduArr->h_org += $value2->passage * 3600;
            }
            if ($value2->Repeat_test && strpos($value2->Repeat_test, ':')) {
                $eduArr->h_org += explode(':', $value2->Repeat_test)[0] * 3600;
                $eduArr->h_org += explode(':', $value2->Repeat_test)[1] * 60;
            } else {
                $eduArr->h_org += $value2->Repeat_test * 3600;
            }
        }

        return $eduArr;
    }

    public static function getStuWithId($stuId)
    {

        $student = DB::table('stu')
            ->where('id', $stuId)
            ->first();

        // if ($student) {
        //     return [
        //         'success' => true,
        //         'student' => $student
        //     ];
        // }

        return $student;
        
    }

}
