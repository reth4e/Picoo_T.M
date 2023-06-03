@extends('layouts.default')

@section('main')
    <div class="container">
        <div class="userpage">
            <div class="username">
                <p>{{$user->name}}</p>
            </div>
            
            <div class="follow_follower">
                <p>フォロー: {{$user -> follows -> count()}}</p>
                <p>フォロワー: {{$user -> followers -> count()}}</p>
            </div>
            <div class="userpage-icon">
                <img src = "../../{{$user -> icon_path}}" alt = "{{$user -> icon_path}}" class="userpage-icon-img">
            </div>

            @if (Auth::id() === $user -> id)
            <div>
                <form action="/user/{{$user -> id}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="file" name="image"  required accept="image/jpeg, image/png, .jpeg, .png"/>
                    <input type="hidden" name="_method" value="PUT">
                    <button>アイコン変更</button>
                </form>
            </div>
            @endif
        </div>
        
        <div class="pictures userpage-pictures">
        @foreach($pictures as $picture)
            <div class="userpage-picture-card card">
                <a href="/pictures/{{$picture->id}}">
                        <div class="picture userpage-picture">
                            <img src="../../{{$picture->file_path}}" alt="" class="userpage-picture-img img">
                        </div>
                        <div>
                            <p>{{$picture->title}}</p>
                        </div>
                </a>
                @if(Auth::id() === $user -> id)
                    <form action="/user/picture/{{$picture->id}}" method="post">
                        @csrf
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="btn btn-delete">×</button>
                    </form>
                @endif
            </div>
        @endforeach
        </div>
        {{ $pictures->links('pagination::bootstrap-4') }}

    </div>
@endsection