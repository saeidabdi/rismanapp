<?php

namespace App\Http\Controllers;

use App\Chat;
use App\group_stu;
use App\Http\Controllers\FirebaseController;

use App\Stu;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function GroupChat(Request $request)
    {

        $stuids = json_decode($request->StuIds);


        $mosh_id = explode(';', $request->token)[1];

        foreach ($stuids as $key => $value) {
            $chat = new Chat;
            if ($request->image) {
                $image = $request->text;
                $type = $request->type;
                $type = str_replace("'", "", $type);
                $imageName = time() . 'ch' . rand(1, 9) . '.' . $type;
                if (\File::put(public_path() . '/imgchat/' . $imageName, base64_decode($image))) {
                    $chat->text = $imageName;
                    $chat->isimg = 1;
                    $imgaddr = $request->root() . '/imgchat/' . $imageName;
                }
            } else {
                $chat->text = $request->text;
                $chat->isimg = 0;
            }
            $chat->stu_id = $value;
            $chat->mosh_id = $mosh_id;
            $chat->type = 1;
            // $chat->text = $request->text;
            $chat->date_time = time();
            $chat->current = 0;
            if ($chat->save()) {
                $stu = Stu::where('id', $value)->first();
                if ($request->image) {
                    $title = "عکس از طرف مشاور";
                    $text = '';
                } else {
                    $title = "پیام از طرف مشاور";
                    $text = $request->text;
                }
                $notification = new FirebaseController;
                if ($request->image) {
                    if ($stu->FirebaseToken) {
                        $notification->spn($stu->FirebaseToken, $title, $text, $imgaddr);
                    }
                } else {
                    if ($stu->FirebaseToken) {
                        $notification->spn($stu->FirebaseToken, $title, $text);
                    }
                }
                // $notification->spn($stu->FirebaseToken, $title, $text);
                // return response()->json(['send_status' => 'ok']);
            }
        }

        return 'ss';
    }
}
