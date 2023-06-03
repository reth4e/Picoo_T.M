<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Picture;
use App\Models\User;

class UserController extends Controller
{
    public function userPage ($user_id) {
        //ユーザーページ(idがログインユーザーと一致したらマイページ)
        $login_user = Auth::user();
        $user = User::find($user_id);
        $pictures = $user -> pictures() -> paginate(20);
        
        if(!$login_user){
            $param = [
                'user' => $user,
                'pictures' => $pictures,
                'search_tags' => NULL,
                'notifications' => NULL,
            ];
            return view('user_page',$param);
        }

        $param = [
            'user' => $user,
            'pictures' => $pictures,
            'search_tags' => NULL,
            'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
        ];
        return view('user_page',$param);
    }

    public function deleteMyPicture ($picture_id) {
        //画像削除
        $picture = Picture::find($picture_id) -> delete();
        session()->flash('status', '画像を削除しました');
        return back();
    }

    public function addFollow ($user_id) {
        //フォローする
        $login_user = Auth::user();

        $login_user -> follows() -> syncWithoutDetaching($user_id);

        $user = User::find($user_id);
        $user -> followers_count = count($user -> followers);
        $user -> save();
        session()->flash('status', 'フォローしました');
        return back();
    }

    public function deleteFollow ($user_id) {
        //フォロー解除
        $login_user = Auth::user();

        $login_user -> follows() -> detach($user_id);

        $user = User::find($user_id);
        $user -> followers_count = count($user -> followers);
        $user -> save();
        session()->flash('status', 'フォロー解除しました');
        return back();
    }

    public function favorites () {
        // いいねした画像表示
        $login_user = Auth::user();
        $favorites = $login_user -> favorites() -> paginate(20);

        $param =[
            'favorites' => $favorites,
            'search_tags' => NULL,
            'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
        ];
        return view('favorites',$param);
    }

    public function follows () {
        // フォローしたユーザー表示
        $login_user = Auth::user();
        $follows = $login_user -> follows() -> paginate(20);
        
        $param =[
            'follows' => $follows,
            'search_tags' => NULL,
            'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
        ];
        return view('follows',$param);
    }

    public function addNg ($user_id) {
        // NG設定する
        $login_user = Auth::user();
        $login_user -> ngUsers() -> syncWithoutDetaching($user_id);

        session()->flash('status', 'NG設定しました');
        return back();
    }

    public function deleteNg ($user_id) {
        // NG解除する
        $login_user = Auth::user();
        $login_user -> ngUsers() -> detach($user_id);

        session()->flash('status', 'NG解除しました');
        return back();
    }

    public function changeIcon (Request $request) {
        // ユーザーアイコンの変更
        $login_user = Auth::user();
        $icon_name = $request -> file('image') -> getClientOriginalName();
        $request -> file('image') -> storeAs('public/icons' , $icon_name);

        $login_user -> icon_path = 'storage/icons/' . $icon_name;
        unset($login_user['_token']);
        $login_user -> save();
        session()->flash('status', 'アイコン変更しました');
        return back();
    }

    public function read (Request $request) {
        // 1件の未読通知を既読化
        $login_user = Auth::user();
        $notification = $login_user -> notifications() -> find($request -> notification_id);
        $notification -> markAsRead();
        session()->flash('status', '既読化しました');

        return back();
    }

    public function readAll () {
        // すべての未読通知を既読化
        auth() -> user() -> unreadNotifications -> markAsRead();

        session()->flash('status', '全件既読化しました');
        return back();
    }

    public function notifications () {
        // 未読通知表示ページ
        $login_user = Auth::user();

        $param =[
            'search_tags' => NULL,
            'notifications' => $login_user -> unreadNotifications() -> paginate(10),
        ];
        return view('notifications',$param);
    }
}
