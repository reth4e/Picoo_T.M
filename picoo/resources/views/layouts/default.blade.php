<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Picoo</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
</head>

<body>
    <header>
        <p class="header-logo">Picoo</p>
        
        <form action="/pictures?contents={{$search_tags}}" method="get" class="">
            <input type="text" name="contents" placeholder="タグ名検索"  />
            <input class="btn" type="submit" value="検索">
        </form>

        @if (auth()->user())
        <button class="btn-mypage btn" >
            <!-- ここにイメージ画像 ログイン時のみ表示 -->
            <a href="/user/{{auth()->user()->id}}">マイページ</a>
        </button>

        <button class="btn-notification btn">通知</button>
        @if ($notifications -> count() > 0)
        <p>{{$notifications -> count()}}</p>
        @endif
        <div>
            <a href="/user/{{auth()->user()->id}}/notification">すべて既読にする</a>
            <div>
            @forelse ($notifications as $notification)
                <p>{{$notification->data['message']}}</p>
                <p>{{$notification->created_at}}</p>
                <a href="/user/{{auth()->user()->id}}/notification/{{$notification -> id}}">既読にする</a>
            @empty
                <p>お知らせはありません</p>
            @endforelse
            {{ $notifications->links('pagination::bootstrap-4') }}
            </div>
        </div>
        
        @endif
        
        <div class="header-menu"  id="menu">
            <span class="menu_line-top"></span>
            <span class="menu_line-middle"></span>
            <span class="menu_line-bottom"></span>
        </div>
        <div class="dropdown" >
            <!-- 以下はクリックで表示 -->
            <!-- ログイン時のみ表示 -->
            @if (auth()->user())
            <ul>
                <li><a href="/" style="text-decoration: none; color: #000;">画像投稿</a></li>
                <li><a href="/user/follows" style="text-decoration: none; color: #000;">フォローユーザー</a></li>
                <li><a href="/user/favorites" style="text-decoration: none; color: #000;">お気に入り画像</a></li>
                <li><a href="/popular" style="text-decoration: none; color: #000;">人気ユーザー・画像</a></li>
                <li class="header-link">
                    <form action="/logout" method="post" class="form-logout">
                    @csrf
                    <input class="btn btn-logout" type="submit" value="ログアウト">
                    </form>
                </li>
                
            </ul>
            <!-- 非ログイン時のみ表示 -->
            @else
            <ul>
                <li><a href="/register" style="text-decoration: none; color: #000;">新規登録</a></li>
                <li><a href="/login" style="text-decoration: none; color: #000;">ログイン</a></li>
            </ul>
            @endif
        </div>
    </header>

    <main>
        @yield('main')
    </main>

    <footer>
        <small class="footer-logo">2023. Picoo</small>
    </footer>
</body>

</html>

<script>

</script>