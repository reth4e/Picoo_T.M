<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use App\Notifications\PictureNotification;
use Illuminate\Support\Facades\Notification;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test($user,$file_name)
    {
        //共通処理、画像投稿処理、picturesのレコードを返す、テスト項目は無し
        //テスト時に、このアクションにテスト項目がないことが原因でエラーが出ますが仕様です
        $login_user = $user;

        $tags = 'tag1 tag2 tag3';
        $image = UploadedFile::fake() -> image($file_name);
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
    
    public function testUserPage()
    {
        //userに対応する画像が表示されているか確認、4項目
        $login_user = User::factory() -> create();
        $another_user = User::factory() -> create();
        $picture1 = $this -> test($login_user,'item1.jpg');
        $picture2 = $this -> test($another_user,'item2.jpg');

        $response = $this -> actingAs($login_user);
        $response = $this->get(route('userpage', [
            'user_id' => $login_user->id,
        ])) -> assertSee('item1.jpg');

        $response = $this->get(route('userpage', [
            'user_id' => $login_user->id,
        ])) -> assertDontSee('item2.jpg');

        $response = $this->get(route('userpage', [
            'user_id' => $another_user->id,
        ])) -> assertDontSee('item1.jpg');

        $response = $this->get(route('userpage', [
            'user_id' => $another_user->id,
        ])) -> assertSee('item2.jpg');
    }

    public function testDeleteMyPicture()
    {
        //画像が削除されているか確認,2項目
        $login_user = User::factory() -> create();
        $picture = $this -> test($login_user,'item.jpg');
        $response = $this -> actingAs($login_user);

        $this -> assertDatabaseHas('pictures', [
            'file_name' => 'item.jpg'
        ]);

        $response = $this->delete(route('deletemypicture', [
            'picture_id' => $picture->id,
        ]));

        $this -> assertDatabaseMissing('pictures', [
            'file_name' => 'item.jpg'
        ]);

    }

    public function testAddFollow()
    {
        //フォローが反映されているか確認,１項目
        $login_user = User::factory() -> create();
        $another_user = User::factory() -> create();

        $response = $this -> actingAs($login_user);
        $response = $this->get(route('addfollow', [
            'user_id' => $another_user->id,
        ]));

        $this -> assertDatabaseHas('follower_user', [
            'follower_id' => $login_user -> id,
            'user_id' => $another_user -> id,
        ]);
    }

    public function testDeleteFollow()
    {
        //フォローが解消されているか確認,１項目
        $login_user = User::factory() -> create();
        $another_user = User::factory() -> create();

        $response = $this -> actingAs($login_user);
        $response = $this->get(route('addfollow', [
            'user_id' => $another_user->id,
        ]));

        $response = $this->get(route('deletefollow', [
            'user_id' => $another_user->id,
        ]));

        $this -> assertDatabaseMissing('follower_user', [
            'follower_id' => $login_user -> id,
            'user_id' => $another_user -> id,
        ]);
    }

    public function testFavorites()
    {
        //userがいいねした画像が表示されているか確認,2項目
        $login_user = User::factory() -> create();
        $picture1 = $this -> test($login_user,'item1.jpg');
        $picture2 = $this -> test($login_user,'item2.jpg');
        $response = $this -> actingAs($login_user);

        $response = $this -> get(route('addlike',[
            'picture_id' => $picture1 -> id,
        ]));

        $response = $this -> get('/user/favorites') -> assertSee('item1.jpg');
        $response = $this -> get('/user/favorites') -> assertDontSee('item2.jpg');
    }

    public function testFollows()
    {
        //フォローしたユーザーが表示されているか確認,2項目
        $login_user = User::factory() -> create();
        $another_user1 = User::factory() -> create();
        $another_user2 = User::factory() -> create();

        $response = $this -> actingAs($login_user);
        $response = $this->get(route('addfollow', [
            'user_id' => $another_user1->id,
        ]));

        $response = $this -> get('/user/follows') -> assertSee($another_user1->name);
        $response = $this -> get('/user/follows') -> assertDontSee($another_user2->name);
    }

    public function testAddNg()
    {
        //NGされているか確認,１項目
        $login_user = User::factory() -> create();
        $another_user = User::factory() -> create();

        $response = $this -> actingAs($login_user);
        $response = $this->get(route('addng', [
            'user_id' => $another_user->id,
        ]));

        $this -> assertDatabaseHas('ng_user', [
            'user_id' => $login_user -> id,
            'ng_user_id' => $another_user -> id,
        ]);
    }

    public function testDeleteNg()
    {
        //NGが解除されているか確認,１項目
        $login_user = User::factory() -> create();
        $another_user = User::factory() -> create();

        $response = $this -> actingAs($login_user);
        $response = $this->get(route('addng', [
            'user_id' => $another_user->id,
        ]));

        $response = $this->get(route('deleteng', [
            'user_id' => $another_user->id,
        ]));

        $this -> assertDatabaseMissing('ng_user', [
            'user_id' => $login_user -> id,
            'ng_user_id' => $another_user -> id,
        ]);
    }

    public function testRead()
    {
        //既読化されているか確認,2項目
        $login_user = User::factory() -> create();
        $another_user = User::factory() -> create();

        $response = $this -> actingAs($another_user);
        $response = $this->get(route('addfollow', [
            'user_id' => $login_user->id,
        ]));


        $response = $this -> actingAs($login_user);
        $picture = $this -> test($login_user,'item.jpg');

        $this -> assertDatabaseHas('notifications', [
            'read_at' => NULL,
        ]);

        $response = $this -> actingAs($another_user);
        $response = $this->get(route('read', [
            'notification_id' => $another_user -> unreadNotifications() -> first() -> id,
        ]));

        $this -> assertDatabaseMissing('notifications', [
            'read_at' => NULL,
        ]);
    }

    public function testReadAll()
    {
        //既読化されているか確認,2項目
        $login_user = User::factory() -> create();
        $another_user = User::factory() -> create();

        $response = $this -> actingAs($another_user);
        $response = $this->get(route('addfollow', [
            'user_id' => $login_user->id,
        ]));


        $response = $this -> actingAs($login_user);
        $picture = $this -> test($login_user,'item1.jpg');
        $picture = $this -> test($login_user,'item2.jpg');

        $this -> assertDatabaseHas('notifications', [
            'read_at' => NULL,
        ]);

        $response = $this -> actingAs($another_user);
        $response = $this->get('/user/readall');

        $this -> assertDatabaseMissing('notifications', [
            'read_at' => NULL,
        ]);
    }
}
