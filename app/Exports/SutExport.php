<?php

namespace App\Exports;

use App\Stu;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use App\helper\Tools;

class SutExport implements FromCollection, WithHeadings
{

    public function __construct($weekId1, $weekId2, $teacher)
    {
        $this->weekId1 = $weekId1;
        $this->weekId2 = $weekId2;
        $this->teacher = $teacher;
    }

    public function collection()
    {
        $students = Stu::where('mosh_id', $this->teacher->code)
            ->select('id', 'name', 'base_id', 'r_id')
            ->get();

        $studentsIds = [];

        foreach ($students as $value)
            array_push($studentsIds, $value->id);


        $resultWeek1 = Tools::weeklyResultSum($this->weekId1, $studentsIds);

        $resultWeek2 = Tools::weeklyResultSum($this->weekId2, $studentsIds);


        foreach ($students as $i => $stu) {
            $students[$i]->sumStudy_S1 = 0;
            $students[$i]->sumStudy_S2 = 0;

            foreach ($resultWeek1  as $j => $value) { // Survey and get sum second time first week

                if (
                    $value->stu_id ==  $stu->id &&
                    $value->h_sum &&
                    explode(':', $value->h_sum)[0]
                ) {

                    error_log('in if week 1');
                    $students[$i]->sumStudy_S1 += explode(':', $value->h_sum)[0] * 3600;

                    if (explode(':', $value->h_sum)[1])
                        $students[$i]->sumStudy_S1 += explode(':', $value->h_sum)[1] * 60;
                }
            }

            foreach ($resultWeek2  as $j => $value) { // Survey and get sum second time latter week

                if (
                    $value->stu_id ==  $stu->id &&
                    $value->h_sum &&
                    explode(':', $value->h_sum)[0]
                ) {
                    error_log('in if week 2');
                    $students[$i]->sumStudy_S2 += explode(':', $value->h_sum)[0] * 3600;

                    if (explode(':', $value->h_sum)[1])
                        $students[$i]->sumStudy_S2 += explode(':', $value->h_sum)[1] * 60;
                }
            }

            $students[$i]->Progress = $students[$i]->sumStudy_S2 - $students[$i]->sumStudy_S1;

            $growth = '+';

            if ($students[$i]->sumStudy_S2 < $students[$i]->sumStudy_S1)
                $growth = '-';

            $students[$i]->Progress = $growth . '' . Tools::convertSecond_2_hours(abs($students[$i]->Progress));

            $students[$i]->sumStudy_S1 = Tools::convertSecond_2_hours($students[$i]->sumStudy_S1);
            $students[$i]->sumStudy_S2 = Tools::convertSecond_2_hours($students[$i]->sumStudy_S2);

            switch ($students[$i]->base_id) {
                    // case null:
                    //     $students[$i]->base_id = 'بدون پایه';
                    //     break;
                case 0:
                    $students[$i]->base_id = 'نهم';
                    break;
                case 1:
                    $students[$i]->base_id = 'دهم';
                    break;
                case 2:
                    $students[$i]->base_id = 'یازدهم';
                    break;
                case 3:
                    $students[$i]->base_id = 'دوازدهم';
                    break;
                case 4:
                    $students[$i]->base_id = 'هفتم';
                    break;
                case 5:
                    $students[$i]->base_id = 'هشتم';
                    break;
            }

            switch ($students[$i]->r_id) {
                    // case null:
                    //     $students[$i]->r_id = 'بدون رشته';
                    //     break;
                case 0:
                    $students[$i]->r_id = 'ریاضی فیزیک';
                    break;
                case 1:
                    $students[$i]->r_id = 'تجربی';
                    break;
                case 2:
                    $students[$i]->r_id = 'انسانی';
                    break;
            }
        }

        return $students;
    }

    public function headings(): array
    {
        return [
            'کد',
            'نام',
            'پایه',
            'رشته',
            'ساعت هفته اول',
            'ساعت هفته دوم',
            'رشد',
        ];
    }
}
