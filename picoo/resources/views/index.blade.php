@extends('layouts.default')

@section('main')
    <div class="container">
        <form class="picture-form" id="form" action="/pictures" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex jc-sb">
                <div class="picture">
                    <img src="" id="preview" class="img confirm-img" alt="画像のプレビュー">
                </div>
                <input class="picture-form-file" type="file" name="image" id="image" required accept="image/jpeg, image/png, .jpeg, .png"/>
            </div>
            @error('title')
                <p class="error_message red">{{$message}}</p>
            @enderror
            @error('post_comment')
                <p class="error_message red">{{$message}}</p>
            @enderror
            <div class="flex jc-sb">
                <p class="w-25vw bw">タイトル(30文字以内)： <span id="title-preview"></span></p>
                <input type="text" placeholder="画像タイトル" name="title" id="title">
            </div>
            <div class="flex jc-sb">
                <p class="w-25vw bw">投稿者コメント(300文字以内)： <span id="post-comment-preview"></span></p>
                <textarea name="post_comment" id="post-comment" rows="10" placeholder="投稿コメント"></textarea>
            </div>
            <div class="flex jc-sb">
                <p class="w-25vw bw">タグ(20文字以内、10個まで)： <span id="tags-preview"></span></p>
                <textarea type="text" placeholder="タグ名" name="tags" id="tags" rows="10" required></textarea>
            </div>
            <button class="btn picture-form-btn" id="form-post">投稿する</button>
        </form>
    </div>
@endsection

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

    $(function(){
        //投稿確認
        $("#form-post").on("click", function(){
            if(window.confirm('入力内容に問題なければOKを押してください')) {
                return true;
            } else {
                return false;
            }
        });

        // 投稿画像のプレビューを表示する
        $('#image').change(function() {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        });

        // タグの内容を表示する
        $('#tags').keyup(function() {
            $('#tags-preview').text($(this).val());
        });

        // タイトルの内容を表示する
        $('#title').keyup(function() {
            $('#title-preview').text($(this).val());
        });

        // 投稿者コメントの内容を表示する
        $('#post-comment').keyup(function() {
            $('#post-comment-preview').text($(this).val());
        });

    });

</script>