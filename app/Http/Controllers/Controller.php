<?php

namespace App\Http\Controllers;

use App\Options;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    public $appname;
    public $VersionStu;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    function __construct()
    {
        // نام اپ
        $option = Options::where('type',5)->first();
        $this->appname = $option->vlaue;
        // نسخه اپ دانش آموز
        $this->VersionStu = 3;
        $this->UrlVersionStu = 'https://rismanapp.ir/app-student.apk';
        // نسخه اپ مشاور
        $this->VersionMosh = 3;
        $this->UrlVersionMosh = 'https://rismanapp.ir/app-adviser.apk';


        $this->loginError = 'عدم سطح دسترسی';

        $this->loginErrorArr = ['success' => false , 'errorMsg' => $this->loginError ];
    }
}
