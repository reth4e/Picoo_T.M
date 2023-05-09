@extends('layouts.default')

@section('main')
    <div class="container">
        <form class="picture-form" action="/pictures" method="POST" enctype="multipart/form-data">
            @csrf
            
            <input class="picture-form-file" type="file" name="image"  required accept="image/jpeg, image/png, .jpeg, .png"/>
            
            @error('title')
                <p class="error_message red">{{$message}}</p>
            @enderror
            @error('post_comment')
                <p class="error_message red">{{$message}}</p>
            @enderror
            <label>
                
                画像タイトル(30文字以内)
                <input type="text" placeholder="画像タイトル" name="title">
            </label>
            <label>
                投稿コメント(300文字以内)
                <textarea name="post_comment" rows="10" placeholder="投稿コメント"></textarea>
            </label>
            <label>
                タグ名(20文字以内、10個まで)
                <textarea type="text" placeholder="タグ名" name="tags" rows="10" required></textarea>
            </label>
            <button class="btn picture-form-btn">投稿する</button>
            
        </form>
    </div>
@endsection