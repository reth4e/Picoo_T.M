<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Picture;
use App\Models\User;
use App\Models\Tag;
use App\Models\Comment;
use Illuminate\Http\UploadedFile;
use App\Notifications\PictureNotification;
use Illuminate\Support\Facades\Notification;


class PictureControllerTest extends TestCase
{   //PictureControllerの各アクションについてのテスト
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test($user)
    {
        //共通処理、画像投稿処理、picturesのレコードを返す、テスト項目は無し
        //テスト時に、このアクションにテスト項目がないことが原因でエラーが出ますが仕様です
        $login_user = $user;

        $tags = 'tag1 tag2 tag3';
        $image = UploadedFile::fake() -> image('item.jpg');
        $data = [
            'image' => $image,
            'title' => 'title',
            'post_comment' => 'post_comment',
            'tags' => $tags,
        ];
        $response = $this -> actingAs($login_user);
        $response = $this -> post('/',$data);
        
        $picture = Picture::where('title','title') -> first();
        return $picture;
    }

    
    public function testIndex()
    {
        //非ログイン時・ログイン時のステータスコードの確認、テスト項目は2項目
        $response = $this -> get('/');
        $response->assertStatus(302);

        $login_user = User::factory() -> create();
        $response = $this -> actingAs($login_user);
        $response = $this -> get('/');
        $response->assertStatus(200);
    }

    public function testPostPicture()
    {
        //画像、タグ、通知に関するテスト、計５項目
        
        $another_user = User::factory() -> create();
        $login_user = User::factory() -> create();
        $another_user -> follows() -> syncWithoutDetaching($login_user->id);
        $login_user -> followers_count = count($login_user -> followers);
        $login_user -> save();
        //通知のためのフォロワーを用意

        Notification::fake();
        
        $picture = $this -> test($login_user);
        $this -> assertDatabaseHas('pictures', [
            'file_name' => 'item.jpg',
            'title' => 'title',
            'post_comment' => 'post_comment',
            'tag_count' => 3,
        ]);
        //picturesテーブルのレコードに正しく画像ファイル名が登録されているか確認
        //同時にタグ数が正しく登録されているかを確認し、画像とタグが結び付けられているかをテスト

        $this -> assertDatabaseHas('tags', [
            'name' => 'tag1'
        ]);
        $this -> assertDatabaseHas('tags', [
            'name' => 'tag2'
        ]);
        $this -> assertDatabaseHas('tags', [
            'name' => 'tag3'
        ]);

        //通知が届いたことの確認
        Notification::assertTimesSent(
            1,
            PictureNotification::class
        );
    }

    public function testSearchPictures ()
    {
        //画像一覧ページで投稿された画像が存在しているかをテスト、１項目
        $login_user = User::factory() -> create();

        $picture = $this -> test($login_user);
        
        $response = $this -> get('/pictures',['contents' => 'tag1 tag2 tag3'])->assertSee('item.jpg');

    }

    public function testPicturePage()
    {
        //まず画像を投稿し、その後画像個別ページ内で画像が存在するかを確かめる、１項目
        $login_user = User::factory() -> create();
        $picture = $this -> test($login_user);
        $response = $this->get(route('picturepage', [
            'picture_id' => $picture->id,
        ])) -> assertSee('item.jpg');
    }

    public function testInsertTag ()
    {
        //タグがデータベースに存在するか、画像ページに表示されているかを確認、4項目
        $login_user = User::factory() -> create();

        $picture = $this -> test($login_user);
        $response = $this -> post(route('inserttag',[
            'picture_id' => $picture->id,
            'tags' => 'tag4 tag5',
        ]));

        $this -> assertDatabaseHas('tags', [
            'name' => 'tag4'
        ]);

        $this -> assertDatabaseHas('tags', [
            'name' => 'tag5'
        ]);

        $response = $this->get(route('picturepage', [
            'picture_id' => $picture->id,
        ])) -> assertSee('tag4');

        $response = $this->get(route('picturepage', [
            'picture_id' => $picture->id,
        ])) -> assertSee('tag5');
    }

    public function testDeleteTag ()
    {
        //画像とタグの結びつきがなくなるか確認、１項目
        $login_user = User::factory() -> create();

        $picture = $this -> test($login_user);
        $tag_id = Tag::where('name','tag3') -> first() ->id;
        $response = $this -> delete(route('deletetag',[
            'picture_id' => $picture->id,
            'tag_id' => $tag_id
        ]));

        $response = $this->get(route('picturepage', [
            'picture_id' => $picture->id,
        ])) -> assertDontSee('tag3');
    }

    public function testChangeTitle ()
    {
        //タイトルの変更に関するデータベースと画像個別ページの確認、２項目
        $login_user = User::factory() -> create();

        $picture = $this -> test($login_user);

        $response = $this -> actingAs($login_user);
        $response = $this -> put(route('changetitle',[
            'picture_id' => $picture->id,
            'title' => 'newtitle',
        ]));

        $this -> assertDatabaseHas('pictures', [
            'title' => 'newtitle'
        ]);

        $response = $this->get(route('picturepage', [
            'picture_id' => $picture->id,
        ])) -> assertSee('newtitle');
    }

    public function testChangePostComment ()
    {
        // 投稿者コメントが更新されていることを確認、２項目
        $login_user = User::factory() -> create();
        $picture = $this -> test($login_user);

        $response = $this -> actingAs($login_user);
        $response = $this -> put(route('changepostcomment',[
            'picture_id' => $picture->id,
            'post_comment' => 'newpostcomment',
        ]));

        $this -> assertDatabaseHas('pictures', [
            'post_comment' => 'newpostcomment'
        ]);

        $response = $this->get(route('picturepage', [
            'picture_id' => $picture->id,
        ])) -> assertSee('newpostcomment');
    }

    public function testAddComment ()
    {
        // コメントが追加されていることを確認、２項目
        $login_user = User::factory() -> create();
        $picture = $this -> test($login_user);

        $response = $this -> actingAs($login_user);
        $response = $this -> post(route('addcomment',[
            'picture_id' => $picture->id,
            'comment' => 'comment1',
        ]));

        $this -> assertDatabaseHas('comments', [
            'content' => 'comment1'
        ]);

        $response = $this->get(route('picturepage', [
            'picture_id' => $picture->id,
        ])) -> assertSee('comment1');
    }

    public function testUpdateComment ()
    {
        //コメントが更新されていることを確認、２項目
        $login_user = User::factory() -> create();
        $picture = $this -> test($login_user);

        $response = $this -> actingAs($login_user);
        $response = $this -> post(route('addcomment',[
            'picture_id' => $picture -> id,
            'comment' => 'comment1',
        ]));

        $comment = Comment::latest() -> first();

        $response = $this -> put(route('updatecomment',[
            'comment_id' => $comment -> id,
            'content' => 'newcomment',
        ]));

        $this -> assertDatabaseHas('comments', [
            'content' => 'newcomment'
        ]);

        $response = $this->get(route('picturepage', [
            'picture_id' => $picture -> id,
        ])) -> assertSee('newcomment');

    }

    public function testDeleteComment ()
    {
        //コメントを投稿した後削除する、コメントが残っていないことを確認、２項目
        $login_user = User::factory() -> create();
        $picture = $this -> test($login_user);

        $response = $this -> actingAs($login_user);
        $response = $this -> post(route('addcomment',[
            'picture_id' => $picture -> id,
            'comment' => 'comment1',
        ]));

        $comment = Comment::latest() -> first();

        $response = $this -> delete(route('deletecomment',[
            'comment_id' => $comment -> id,
        ]));

        $this -> assertDatabaseMissing('comments', [
            'content' => 'comment1'
        ]);

        $response = $this->get(route('picturepage', [
            'picture_id' => $picture -> id,
        ])) -> assertDontSee('comment1');
    }

    public function testAddLike ()
    {
        // いいねがされているか確認、２項目
        $login_user = User::factory() -> create();
        $picture = $this -> test($login_user);

        $response = $this -> actingAs($login_user);
        $response = $this -> get(route('addlike',[
            'picture_id' => $picture -> id,
        ]));

        $this -> assertDatabaseHas('pictures', [
            'id' => $picture -> id,
            'favorites_count' => 1
        ]);

        $this -> assertDatabaseHas('likes', [
            'picture_id' => $picture -> id,
            'user_id' => $login_user -> id
        ]);
    }

    public function testDeleteLike ()
    {
        // いいねが解除されているか確認、２項目
        $login_user = User::factory() -> create();
        $picture = $this -> test($login_user);

        $response = $this -> actingAs($login_user);
        $response = $this -> get(route('addlike',[
            'picture_id' => $picture -> id,
        ]));

        $response = $this -> get(route('deletelike',[
            'picture_id' => $picture -> id,
        ]));

        $this -> assertDatabaseHas('pictures', [
            'id' => $picture -> id,
            'favorites_count' => 0
        ]);

        $this -> assertDatabaseMissing('likes', [
            'picture_id' => $picture -> id,
            'user_id' => $login_user -> id
        ]);
    }
}
