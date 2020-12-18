@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-8">
            <img class="w-100" src="/storage/{{ $post->image }}">
        </div>
        <div class="col-4">
            <div class="d-flex align-items-center">
                <div class="pr-3">
                    <img src="{{$post->user->profile->profileImage()}}" style="max-width: 40px" class="rounded-circle w-100" alt="">
                </div>
                <div class="">
                    <div class="font-weight-bold"><a class="text-dark" href="/profile/{{$post->user->id}}">{{$post->user->username}} |</a></div>
                </div>
                <a href="#" class="pl-2">Follow</a>
   
            </div>
            <hr>
            <p><span class="font-weight-bold"><a class="text-dark" href="/profile/{{$post->user->id}}">{{$post->user->username}}</a></span> {{$post->caption}}</p>
        </div>
    </div>
</div>

@endsection