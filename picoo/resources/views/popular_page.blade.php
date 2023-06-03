@extends('layouts.default')

@section('main')
    <div class = "container">
        <div class="popular-pictures pictures">
            @foreach ($popular_pictures as $popular_picture)
            <div class="popular-picture picture">
                <span>{{$loop -> index + 1}}</span>
                <a href="/pictures/{{$popular_picture -> id}}">
                    <img src="../../{{$popular_picture -> file_path}}" alt="" class="popular-picture-img img">
                </a>
            </div>
            @endforeach
        </div>
        <div class="popular-users">
            @foreach ($popular_users as $popular_user)
            <div class="popular-user">
                <span>{{$loop -> index + 1}}</span>
                <a href="/user/{{$popular_user -> id}}">{{$popular_user -> name}}</a>
                <div class="popularpage-icon">
                    <img src="../../{{$popular_user -> icon_path}}" alt="" class="popularpage-icon-img">
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection