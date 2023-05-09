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
    <header class = "header">
        <p class="header-logo">Picoo</p>
        
        <form action="/pictures?contents={{$search_tags}}" method="get" class="header-form">
            <input class="search" type="text" name="contents" placeholder="タグ名検索" value ="{{$search_tags}}" />
            <label>新しい順
                <input type="radio" name="dateorder" value="new">
            </label>
            <label>古い順
                <input type="radio" name="dateorder" value="old">
            </label>
            <input class="btn search-btn" type="submit" value="検索">
        </form>
        
        <nav class="header-nav">
            <p class="header-menu" id="menu">メニュー</p>
            <!-- 以下はクリックで表示 -->
            <div class="dropdown" id = "dropdown">
                @if (auth()->user())
                <div class="notification-block">
                    <button class="btn-notification btn" id="btn-notification">通知</button>

                    @if ($notifications -> count() > 0)
                        <span>{{$notifications -> count()}}</span>
                    @endif

                    <div class="header-notifications" id="notifications">

                        @if ($notifications -> count() > 0)
                            <a href="/user/notifications" class="block">通知ページへ</a>
                            <a href="/user/readall" class="mg-b-3 red block">すべて既読にする</a>
                        @endif

                        @forelse ($notifications as $notification)
                        <div class="header-notification mg-b-3">
                            <a href="/pictures/{{$notification -> data['id']}}">{{$notification->data['message']}}</a>
                            <p>{{$notification->created_at}}</p>
                            <a href="/user/read/{{$notification -> id}}">既読にする</a>
                        </div>
                        @empty
                            <p>お知らせはありません</p>
                        @endforelse

                    </div>
                    
                </div>
                <ul>
                    <li class="header-link"><a href="/user/{{auth()->user()->id}}">マイページ</a></li>
                    <li class="header-link"><a href="/">画像投稿</a></li>
                    <li class="header-link"><a href="/user/follows">フォローユーザー</a></li>
                    <li class="header-link"><a href="/user/favorites">お気に入り画像</a></li>
                    <li class="header-link"><a href="/popular">人気ユーザー・画像</a></li>
                    <li class="header-link">
                        <form action="/logout" method="post" class="form-logout">
                        @csrf
                        <input class="btn btn-logout" type="submit" value="ログアウト">
                        </form>
                    </li>
                    
                </ul>
                @else
                <ul>
                    <li class="header-link"><a href="/register">新規登録</a></li>
                    <li class="header-link"><a href="/login">ログイン</a></li>
                </ul>
                @endif
            </div>
        </nav>
    </header>

    <main>
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
        @yield('main')
    </main>

    <footer class = "footer">
        <small class="footer-logo">2023. Picoo</small>
    </footer>
</body>

</html>

<script>
    const target = document.getElementById("menu");
    target.addEventListener('click', () => {
        target.classList.toggle('open');
        const dropdown = document.getElementById("dropdown");
        dropdown.classList.toggle('appear');
    });

    const btn_notification = document.getElementById("btn-notification");
    btn_notification.addEventListener('click', () => {
        const notifications = document.getElementById("notifications");
        notifications.classList.toggle('appear');
    });
</script>

<style>

    .header-notifications {
        display: none;
    }

    .appear {
        display: block;
    }

</style>