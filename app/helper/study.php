<?php

namespace App\helper;

use DB;

class study
{
    public function __construct()
    {
        
    }
    public function SaveSumStudy($stu_id,$week_id,$day,$h_sum,$normal,$mes)
    {
        DB::table('sum_study')->where('stu_id',$stu_id)->where('day',$day)->where('week_id',$week_id)->delete();
        DB::table('sum_study')->insert([
            'stu_id' => $stu_id,
            'day' => $day,
            'week_id' => $week_id,
            'h_sum' => $h_sum,
            'normal' => $normal,
            'tomorrow_mes' => $mes,
        ]);
    }
}
