<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Picture;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\User;
use App\Http\Requests\PictureRequest;
use App\Http\Requests\CommentRequest;
use App\Notifications\PictureNotification;
use Illuminate\Support\Facades\Notification;

class PictureController extends Controller
{
    
    public function index(){
        $login_user = Auth::user();
        $param = [
            'search_tags' => NULL,
            'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
        ];

        return view('index',$param);
    }

    public function postPicture(PictureRequest $request)
    {
        $login_user = Auth::user();

        $picture = new Picture;

        $file_name = $request -> file('image') -> getClientOriginalName();
        $request -> file('image') -> storeAs('public/pictures' , $file_name);
        
        unset($picture['_token']);


        $input_tag = $request -> tags;

        $input_tag_to_array = Tag::getTagToArray($input_tag);
        if(count($input_tag_to_array) > 10){
            session()->flash('status', 'タグの数は10こ以内にしてください');
            return back();
        }

        $tag_ids = [];
        foreach($input_tag_to_array as $tag){
            if(strlen($tag) > 20){
                session()->flash('status', 'タグの字数は20文字以内にしてください');
                return back();
            }
            $tag = Tag::firstOrCreate([
                'name' => $tag,
            ]);
            array_push($tag_ids, $tag -> id);
        }


        $picture -> user_id = $request -> user() -> id;
        $picture -> file_name = $file_name;
        $picture -> file_path = 'storage/pictures/' . $file_name;
        $picture -> title = $request -> title;
        $picture -> tag_count = count($input_tag_to_array);
        $picture -> post_comment = $request -> post_comment;
        $picture -> favorites_count = 0;
        $picture -> save();
        $request->session()->regenerateToken();

        $picture -> tags() -> syncWithoutDetaching($tag_ids);


        $followers = $login_user -> followers;
        Notification::send($followers, new PictureNotification($picture));

        session()->flash('status', '画像の投稿に成功しました');

        $param = [
            'pictures' => Picture::orderBy('created_at','DESC') -> paginate(20),
            'search_tags' => NULL,
            'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
        ];
        return view('pictures',$param);
    }


    public function searchPictures(Request $request) {
        //このアクションとdefaultの改正で日付ソート（このメッセージは消去して）
        $login_user = Auth::user();

        $searched_tag = $request -> contents;
        $searched_tag_array = Tag::getTagToArray($searched_tag);

        $pictures = Picture::all();
        $picture_ids = Picture::getPictureIds($pictures, $searched_tag_array);

        if($request -> dateorder === 'new'){
            $search_result = Picture::whereIn('id',$picture_ids) -> orderBy('created_at','DESC') -> paginate(20);
        } else {
            $search_result = Picture::whereIn('id',$picture_ids) -> paginate(20);
        }

        if($login_user !== NULL && $searched_tag === NULL){
            if($request -> dateorder === 'new') {
                $param = [
                    'pictures' => Picture::orderBy('created_at','DESC') -> paginate(20),
                    'search_tags' => $searched_tag,
                    'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
                ];
                return view('pictures',$param);
            }
            $param = [
                'pictures' => Picture::paginate(20),
                'search_tags' => $searched_tag,
                'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
            ];
            return view('pictures',$param);
        }
        if(!$login_user && $searched_tag !== NULL){
            $param = [
                'pictures' => $search_result,
                'search_tags' => $searched_tag,
                'notifications' => NULL,
            ];
            return view('pictures',$param);
        }
        if(!$login_user && !$searched_tag){
            if($request -> dateorder === 'new') {
                $param = [
                    'pictures' => Picture::orderBy('created_at','DESC') -> paginate(20),
                    'search_tags' => $searched_tag,
                    'notifications' => NULL,
                ];
                return view('pictures',$param);
            }
            $param = [
                'pictures' => Picture::paginate(20),
                'search_tags' => $searched_tag,
                'notifications' => NULL,
            ];
            return view('pictures',$param);
        }

        $param = [
            'pictures' => $search_result,
            'search_tags' => $searched_tag,
            'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
        ];
        return view('pictures',$param);
    }

    public function picturePage(Request $request) {
        $login_user = Auth::user();
        $picture = Picture::where('id',$request->picture_id) -> first();
        if(!$picture && $login_user) {
            $param = [
                'picture' => NULL,
                'tags' => NULL,
                'comments' => NULL,
                'search_tags' => NULL,
                'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
            ];
            
            return view('picturepage',$param);
        }
        if(!$picture && !$login_user){
            $param = [
                'picture' => NULL,
                'tags' => NULL,
                'comments' => NULL,
                'search_tags' => NULL,
                'notifications' => NULL,
            ];
            
            return view('picturepage',$param);
        }
        

        $tags = $picture -> tags;
        $comments = Comment::where('picture_id',$request -> picture_id) -> orderBy('id','desc') -> paginate(10);

        if(!$login_user) {
            $param = [
                'picture' => $picture,
                'tags' => $tags,
                'comments' => $comments,
                'search_tags' => NULL,
                'notifications' => NULL,
            ];
            return view('picturepage',$param);
        }

        $param = [
            'picture' => $picture,
            'tags' => $tags,
            'comments' => $comments,
            'search_tags' => NULL,
            'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
        ];
        return view('picturepage',$param);
    }

    public function insertTag ($picture_id, Request $request) {
        $picture = Picture::where('id',$picture_id) -> first();
        $input_tag = $request -> tags;

        $tag_ids = [];
        $input_tag_to_array = Tag::getTagToArray($input_tag);
        if(count($input_tag_to_array) + $picture -> tag_count > 10){
            session()->flash('status', 'タグの数は10こ以内にしてください');
            return back();
        }
        foreach($input_tag_to_array as $tag){
            if(strlen($tag) > 20){
                session()->flash('status', 'タグの字数は20以内にしてください');
                return back();
            }
            $tag = Tag::firstOrCreate([
                'name' => $tag,
            ]);
            array_push($tag_ids, $tag -> id);
        }

        $picture -> tags() -> syncWithoutDetaching($tag_ids);
        $picture -> tag_count = count($picture -> tags);
        $picture -> save();
        $request->session()->regenerateToken();

        session()->flash('status', 'タグを追加しました');

        return back();
    }

    public function deleteTag ($picture_id, $tag_id) {
        $picture = Picture::where('id',$picture_id) -> first();
        $picture -> tags() -> detach($tag_id);
        $picture -> tag_count = count($picture -> tags);
        $picture -> save();

        session()->flash('status', 'タグを削除しました');

        return back();
    }

    public function changeTitle ($picture_id, PictureRequest $request) {
        $picture = Picture::where('id',$picture_id) -> first();
        $picture -> title = $request -> title;
        unset($picture['_token']);
        $picture -> save();
        session()->flash('status', 'タイトルを変更しました');
        return back();
    }

    public function changePostComment ($picture_id, PictureRequest $request) {
        $picture = Picture::where('id',$picture_id) -> first();
        $picture -> post_comment = $request -> post_comment;
        unset($picture['_token']);
        $picture -> save();
        session()->flash('status', '投稿者コメントを変更しました');
        return back();
    }

    public function addComment ($picture_id, CommentRequest $request) {

        $comment = new Comment;
        $comment -> content = $request -> comment;
        $comment -> user_id = $request -> user() -> id;
        $comment -> picture_id = $picture_id;
        $comment -> save();
        $request->session()->regenerateToken();
        session()->flash('status', '投稿者コメントを変更しました');

        return back();
    }

    public function updateComment ($comment_id ,CommentRequest $request) {
        $comment = Comment::where('id',$comment_id) -> first();
        $comment -> content = $request -> content;
        $comment -> save();
        session()->flash('status', 'コメントを変更しました');

        return back();
    }

    public function deleteComment ($comment_id) {
        $comment = Comment::find($comment_id) -> delete();
        session()->flash('status', 'コメントを削除しました');
        return back();
    }

    public function addLike ($picture_id) {
        $login_user = Auth::user();
        $login_user -> favorites() -> syncWithoutDetaching($picture_id);

        $picture = Picture::find($picture_id);
        $picture -> favorites_count = count($picture -> usersWhoLike);
        $picture -> save();
        session()->flash('status', 'いいねしました');
        return back();
    }

    public function deleteLike ($picture_id) {
        $login_user = Auth::user();
        $login_user -> favorites() -> detach($picture_id);

        $picture = Picture::find($picture_id);
        $picture -> favorites_count = count($picture -> usersWhoLike);
        $picture -> save();
        session()->flash('status', 'いいねを解除しました');
        return back();
    }

    public function popularPage () {
        $login_user = Auth::user();
        $popular_pictures = Picture::orderBy('favorites_count','DESC')->take(20)->get();
        $popular_users = User::orderBy('followers_count','DESC')->take(20)->get();

        if(!$login_user){
            $param =[
                'popular_pictures' => $popular_pictures,
                'popular_users' => $popular_users,
                'search_tags' => NULL,
                'notifications' => NULL,
            ];
            return view('popularpage',$param);
        }

        $param =[
            'popular_pictures' => $popular_pictures,
            'popular_users' => $popular_users,
            'search_tags' => NULL,
            'notifications' => $login_user -> unreadNotifications() -> orderBy('created_at','DESC') -> take(5) -> get(),
        ];
        return view('popularpage',$param);
    }
}