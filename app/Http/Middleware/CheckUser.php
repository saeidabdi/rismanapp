<?php

namespace App\Http\Middleware;

use App\helper\Tools;

use Closure;

class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    function __construct()
    {

        $this->loginError = 'عدم سطح دسترسی';

        $this->loginErrorArr = ['success' => false, 'errorMsg' => $this->loginError];
    }


    public function handle($request, Closure $next)
    {
        $token = $request->token;

        $checkLogin = Tools::checkTokenStudent($token);

        if (!$checkLogin['success'])  // check Login user 
            return response()->json($this->loginErrorArr);

        $request->user = $checkLogin['user'];

        return $next($request);

    }
}
