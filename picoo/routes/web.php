<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\UserController;

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
Route::group(['middleware' => 'verified'],function() {
    Route::get('/', [PictureController::class, 'index']);
    Route::post('/pictures', [PictureController::class, 'postPicture']);

    Route::post('/pictures/{picture_id}/tag',[PictureController::class, 'insertTag'])->name('inserttag');
    Route::delete('/pictures/{picture_id}/tag/{tag_id}',[PictureController::class, 'deleteTag'])->name('deletetag');

    Route::put('/pictures/{picture_id}/title',[PictureController::class, 'changeTitle'])->name('changetitle');
    Route::put('/pictures/{picture_id}/post_comment',[PictureController::class, 'changePostComment'])->name('changepostcomment');

    Route::post('/pictures/{picture_id}/comment',[PictureController::class, 'addComment'])->name('addcomment');
    Route::put('/pictures/update_comment/{comment_id}',[PictureController::class, 'updateComment'])->name('updatecomment');
    Route::delete('/pictures/delete_comment/{comment_id}',[PictureController::class, 'deleteComment'])->name('deletecomment');

    Route::get('/pictures/{picture_id}/add_like', [PictureController::class, 'addLike'])->name('addlike');
    Route::get('/pictures/{picture_id}/delete_like', [PictureController::class, 'deleteLike'])->name('deletelike');
    

    Route::put('/user/{user_id}',[UserController::class, 'changeIcon'])->name('changeicon');

    Route::delete('/user/picture/{picture_id}',[UserController::class, 'deleteMyPicture'])->name('deletemypicture');

    Route::get('/user/{user_id}/add_follow',[UserController::class, 'addFollow'])->name('addfollow');
    Route::get('/user/{user_id}/delete_follow',[UserController::class, 'deleteFollow'])->name('deletefollow');

    Route::get('/user/favorites',[UserController::class, 'favorites']);

    Route::get('/user/follows',[UserController::class, 'follows']);

    Route::get('/user/{user_id}/add_ng',[UserController::class, 'addNg'])->name('addng');
    Route::get('/user/{user_id}/delete_ng',[UserController::class, 'deleteNg'])->name('deleteng');

    Route::get('/user/notifications',[UserController::class, 'notifications']);
    Route::get('/user/readall',[UserController::class, 'readAll']);
    Route::get('/user/read/{notification_id}',[UserController::class, 'read'])->name('read');
});


Route::get('/pictures',[PictureController::class, 'searchPictures']);

Route::get('/pictures/{picture_id}',[PictureController::class, 'picturePage'])->name('picturepage');

Route::get('/popular',[PictureController::class, 'popularPage']);


Route::get('/user/{user_id}',[UserController::class, 'userPage'])->name('userpage');

require __DIR__.'/auth.php';
