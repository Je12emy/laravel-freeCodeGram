@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-3 pt-5">
                <img class="rounded-circle w-100" src="{{ $user->profile->profileImage() }}">
            </div>
            <div class="col-9 pt-5">
                <div class="d-flex justify-content-between align-items-baseline">
                    <h1>{{ $user->username }}</h1>
                    <Follow-Button user-id="{{ $user->id }}" follows="{{ $follows }}"></Follow-Button>
                    <a href="/p/create">Add new post</a>
                </div>
                @can('update', $user->profile)
                    <a href="/profile/{{ $user->id }}/edit">Edit Profile</a>
                @endcan

                <div class="d-flex">
                    <div class="pr-5"><strong>{{ $postsCount }}</strong> posts</div>
                    <div class="pr-5"><strong>{{ $followersCount }}</strong> followers</div>
                    <div class="pr-5"><strong>{{ $followingCount }}</strong> following</div>
                </div>
                <div class="pt-4"><strong>{{ $user->profile->title }}</strong></div>
                <div>{{ $user->profile->description }}</div>
                <a href="">{{ $user->profile->url }}</a>
            </div>
        </div>
        <div class="row pt-5">
            @foreach ($user->posts as $post)
                <div class="col-4 pb-4">
                    <a href="/p/{{ $post->id }}">
                        <img class="w-100" src="/storage/{{ $post->image }}" />
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
