@extends('layouts.app')

@section('content')
<div class="container">
    <form action="/p" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-8 offset-2">
                <div class="row">
                    <h1>Add New Post</h1>
                </div>
                <div class="form-group row">
                    <label for="caption" class="col-md-4 col-form-label">Post Caption</label>

                    <input id="caption" type="text" class="form-control @error('name') is-invalid @enderror" name="caption" value="{{ old('caption') }}" required autocomplete="name" autofocus>

                    @error('caption')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="row">
                    <label for="image" class="col-md-4 col-form-label">Post Image</label>

                    <input type="file" class="form-control-file" id="image" name="image">
                    @error('image')
                    <strong class="alert">{{ $message }}</strong>
                    @enderror
                </div>
                <div class="row">
                    <button class="btn btn-primary mt-4">Add New Post</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection