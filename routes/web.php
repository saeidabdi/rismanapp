<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'AdminController@login');
Route::post('/login', 'AdminController@login_admin');
Route::get('/dashbord', 'AdminController@dashbord');
Route::get('/getuser', 'AdminController@getuser');
Route::get('/slider', 'AdminController@slider');
Route::get('/exit_admin', 'AdminController@exit_admin');
Route::post('/formSubmit', 'AdminController@formSubmit');
Route::post('/formimgplan', 'AdminController@formimgplan');
Route::get('/get_imag_slide', 'AdminController@get_imag_slide');
Route::post('slider_img', 'AdminController@slider_img');
Route::get('/stu', 'AdminController@stu');
Route::post('/get_stu', 'AdminController@get_stu');
Route::post('/search_stu', 'AdminController@search_stu');
Route::get('/lesson', 'AdminController@lesson');
Route::post('/add_lesson', 'AdminController@add_lesson');
Route::post('/get_lesson', 'AdminController@get_lesson');
Route::get('/mosh', 'AdminController@mosh');
Route::get('/get_mosh', 'AdminController@get_mosh');
Route::post('/search_mosh', 'AdminController@search_mosh');
Route::post('/unactive_mosh', 'AdminController@unactive_mosh');
Route::get('/plan', 'AdminController@plan');
Route::get('/get_message', 'AdminController@get_message');
Route::post('/edit_message', 'AdminController@edit_message');
Route::post('/add_plan', 'AdminController@add_plan');
Route::get('/get_plan', 'AdminController@get_plan');
Route::post('/delete_plan', 'AdminController@delete_plan');
Route::post('/get_paln_stu', 'AdminController@get_paln_stu');
Route::get('/weeks', 'AdminController@weeks');
Route::post('/add_week', 'AdminController@add_week');

Route::group(['prefix' => 'api'], function () {
    Route::post('/mobile', 'ApiController@mobile');
    Route::post('/ok_code', 'ApiController@ok_code');
    Route::post('/register', 'ApiController@register');
    Route::post('/get_home', 'ApiController@get_home');
    Route::post('/edu_plan', 'ApiController@edu_plan');
    Route::post('/send_edu', 'ApiController@send_edu');
    Route::post('/get_edu', 'ApiController@get_edu');
    Route::post('/get_planing', 'ApiController@get_planing');
    Route::post('/get_plan_stu', 'ApiController@get_plan_stu');
    Route::post('/check_pass', 'ApiController@check_pass');
    Route::get('/test', 'ApiController@test');
    Route::post('/all_week', 'ApiController@all_week');
    Route::post('/get_day_week', 'ApiController@get_day_week');
    Route::post('/get_status', 'ApiController@get_status');
    Route::post('/send_plan_stu', 'ApiController@send_plan_stu');
    Route::post('/get_profile', 'ApiController@get_profile');
    Route::post('/show_planing', 'ApiController@show_planing');
    Route::post('/report_time_study', 'ApiController@report_time_study');
    Route::post('/all_day_between', 'ApiController@all_day_between');
    Route::post('/show_price', 'ApiController@show_price');
    Route::post('/get_chat', 'ApiController@get_chat');
    Route::post('/send_chat', 'ApiController@send_chat');
    Route::post('/transaction', 'ApiController@transaction');
    Route::get('/pay/{token}/{price}', 'ApiController@pay');
    // Route::get('/pay/{token}/{price}','AdminController@pay');
    Route::get('/buyback','ApiController@buyback');
    Route::get('/backapp','ApiController@backapp');
    Route::post('/get_plannig_list','ApiController@get_plannig_list');
    // -------------------------------------- moshaver ----------------
    Route::post('/get_stu_mosh','ApiController@get_stu_mosh');
    Route::post('/show_all_message','ApiController@show_all_message');
    Route::post('/show_pv','ApiController@show_pv');
    Route::post('/send_pv','ApiController@send_pv');
    Route::post('/request_planning','ApiController@request_planning');
    Route::post('/reject_request','ApiController@reject_request');
    Route::post('/accept_request','ApiController@accept_request');
    Route::post('/get_data_planing','ApiController@get_data_planing');
    Route::post('/student_mosh','ApiController@student_mosh');
    Route::post('/add_groups','ApiController@add_groups');
    Route::post('/show_groups','ApiController@show_groups');
    Route::post('/stu_group','ApiController@stu_group');
    Route::post('/del_group','ApiController@del_group');
    Route::post('/edit_group','ApiController@edit_group');
    Route::post('/edit_stu_group','ApiController@edit_stu_group');
    Route::get('/VersionStu','ApiController@VersionStu');
    Route::get('/VersionMOsh','ApiController@VersionMOsh');
    Route::post('/sendPushNotification','ApiController@sendPushNotification');
    Route::post('/spn','ApiController@spn');
    Route::post('/GetNumChatStu','ApiController@GetNumChatStu');
    Route::post('/GetNumChatMosh','ApiController@GetNumChatMosh');
    Route::post('/detail_report','ApiController@detail_report');
    // ----------MoshController
    Route::post('/get_ms_mosh','MoshController@get_ms_mosh');
    Route::post('/get_automati_message','MoshController@get_automati_message');
    Route::post('/send_automati_message','MoshController@send_automati_message');
    Route::post('/switch_auto','MoshController@switch_auto');
    Route::post('/mobile_mosh','MoshController@mobile_mosh');
    Route::post('/ok_code_mosh','MoshController@ok_code_mosh');
    Route::post('/register_mosh','MoshController@register_mosh');
    Route::post('/check_pass_mosh','MoshController@check_pass_mosh');
    Route::post('/all_users_mosh','MoshController@all_users_mosh');
    Route::post('/ms_edit','MoshController@ms_edit');
    Route::post('/upload_image_slider','MoshController@UploadImageSlider');
    Route::post('/RemoveImageSlider','MoshController@RemoveImageSlider');
    Route::post('/moshTransaction','MoshController@moshTransaction');
    Route::post('/getlesson','MoshController@getlesson');
    // ----------CodeController
    Route::get('/payCode/{mobile}/{PriceSymbol}','CodeController@payCode');
    Route::get('/payback','CodeController@payback');
    Route::get('/paybit/{mobile}/{PriceSymbol}','CodeController@payCode');
    // Route::get('/paybit/{mobile}/{PriceSymbol}','AdminController@paybit');
    Route::get('/backbit','CodeController@payback');
    // ----------FirebaseController
    Route::post('/SetTokenFirebaseStu','FirebaseController@SetTokenFirebaseStu');
    Route::post('/SetTokenFirebaseMosh','FirebaseController@SetTokenFirebaseMosh');
    // ----------ChatController
    Route::post('/GroupChat','ChatController@GroupChat');


    // reportApiController
    Route::post('/report2weekly','reportApiController@report2weekly');
});
