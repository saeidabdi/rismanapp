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
        $this->VersionStu = 1.12;
        $this->UrlVersionStu = 'http://app-web.ir';
        // نسخه اپ مشاور
        $this->VersionMosh = 1.12;
        $this->UrlVersionMosh = 'http://app-web.ir';


        $this->loginError = 'عدم سطح دسترسی';

        $this->loginErrorArr = ['success' => false , 'errorMsg' => $this->loginError ];
    }
}
