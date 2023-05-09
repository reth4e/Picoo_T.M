@extends('layouts.default')

@section('main')
    <div class = "container">
        <div class="picturepage-wrap">
            @if(!$picture)
                <p class="picturepage-deleted">
                    お探しの画像は削除されました。
                </p>
            @else
                <div class="picturepage-picture">
                    <img src = "../../{{$picture -> file_path}}" alt = "{{$picture->file_path}}" class="picture-img">
                </div>

                <div class="like">
                    @if ($picture -> usersWhoLike() -> where('user_id', Auth::id()) -> exists())
                        <a href="/pictures/{{$picture -> id}}/delete_like" class="like-link">いいね解除</a>
                    @else
                        <a href="/pictures/{{$picture -> id}}/add_like" class="like-link">いいね</a>
                    @endif
                    <p>いいね数：{{$picture->usersWhoLike->count()}}</p>
                </div>

                <div class="picturepage-title">
                    @if($picture -> user -> id === Auth::id())
                        <form action = "/pictures/{{$picture -> id}}/title" method = "post" class="picturepage-form" >
                            @csrf
                            <input type = "text" name = "title" value = "{{$picture -> title}}" placeholder = "タイトル編集" id="title">
                            <input type = "hidden" name = "_method" value = "PUT">
                            <input type = "submit" value = "更新">
                        </form>
                    @else
                        <p>{{$picture->title}}</p>
                    @endif
                </div>

                <div class = "picturepage-post_comment">
                    @if($picture->user->id === Auth::id())
                        <form action = "/pictures/{{$picture -> id}}/post_comment" method = "post" class="picturepage-form">
                            @csrf
                            <textarea type = "text" name = "post_comment" placeholder = "投稿者コメント編集" rows="6" id="post_comment">{{$picture -> post_comment}}</textarea>
                            <input type = "hidden" name = "_method" value = "PUT">
                            <input type = "submit" value = "更新"> 
                        </form>
                    @else
                        <p>{{$picture->post_comment}}</p>
                    @endif
                </div>

                <div class = "picturepage-tag">
                    @foreach($tags as $tag)
                        @if($picture->user->id === Auth::id())
                            @if($picture->tag_count > 1)
                                <form action = "/pictures/{{$picture -> id}}/tag/{{$tag -> id}}" method = "post">
                                    @csrf
                                    <input type = "hidden" name = "_method" value = "DELETE">
                                    <button class = "btn btn-delete">×</button>
                                </form>
                            @endif
                        @endif
                        <a href = "/pictures?contents={{$tag -> name}}" class="picturepage-tagname">{{$tag -> name}}</a>
                        
                    @endforeach
                </div>
                    
                    @if($picture->user->id === Auth::id())
                        @if($picture->tag_count < 10)
                            <form action = "/pictures/{{$picture -> id}}/tag" method = "post" class="picturepage-tagform">
                                @csrf
                                <textarea type = "text" placeholder = "タグの追加(1つにつき20文字まで)" name = "tags" rows="5"></textarea>
                                <input type = "submit" value = "追加">
                            </form>
                        @endif
                    @endif

                <div class="picturepage-user">
                    <a href = "/user/{{$picture -> user -> id}}">{{$picture->user->name}}</a>
                    @if ($picture -> user -> followers() -> where('follower_id', Auth::id()) -> exists())
                        <a href="/user/{{$picture -> user -> id}}/delete_follow">フォロー中</a>
                    @else
                        <a href="/user/{{$picture -> user -> id}}/add_follow">フォローする</a>
                    @endif
                </div>

                @error('content')
                    <p class="error_message red">{{$message}}</p>
                @enderror
                
                @if(Auth::id())
                <div class="picturepage-commentform">
                    <form action = "/pictures/{{$picture -> id}}/comment" method = "post">
                        @csrf
                        <textarea type = "text" placeholder = "コメント追加(1文字以上200文字以内)" name = "comment" rows="6" required></textarea>
                        <input type = "submit" value = "追加">
                    </form>
                </div>
                @endif

                <div class="picturepage-comments">
                    @foreach ($comments as $comment)
                    <div class="picturepage-comment">
                        <a href = "/user/{{$comment -> user -> id}}">{{$comment -> user -> name}}</a>
                        @if ($comment -> user -> id === Auth::id())
                            <form action="/pictures/update_comment/{{$comment -> id}}" method = "post">
                                @csrf
                                <input type = "hidden" name = "_method" value = "PUT">
                                <textarea type = "text" name = "content" rows="10" required>{{$comment -> content}}</textarea>
                                <input type = "submit" value = "編集">
                            </form>
                            <form action = "/pictures/delete_comment/{{$comment -> id}}" method = "post">
                                @csrf
                                <input type = "hidden" name = "_method" value = "DELETE">
                                <button class = "btn btn-delete">×</button>
                            </form>
                        @else
                            @if (Auth::id())
                                @if (auth() -> user() -> ngUsers() -> where('ng_user_id', $comment->user->id) -> exists())
                                <a href="/user/{{$comment -> user -> id}}/delete_ng">このユーザーをNG解除</a>
                                @else
                                <p>{{$comment -> content}}</p>
                                <a href="/user/{{$comment -> user -> id}}/add_ng">このユーザーをNG</a>
                                @endif
                            @endif
                        @endif
                        <p class="picturepage-comment-date">{{$comment -> updated_at}}</p>
                    </div>
                    @endforeach
                </div>
                {{ $comments -> links('pagination::bootstrap-4') }}
            @endif
        </div>
    </div>
@endsection