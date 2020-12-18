# Adding Posts to the Database & Many To Many Relationship

Profiles have photo posts in their profile, so we should add this too to our application. Create a new model for our posts:

```cli
> $ php artisan make:model Post -m                                                                                                                  ⬡ 12.16.3 
Model created successfully.
Created Migration: 2020_11_16_024325_create_posts_table
```

Update our model like so

```php
Schema::create("posts", function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger("user_id");
    $table->string("caption");
    $table->string("image");
    $table->timestamps();

    $table->index("user_id");
});
```

Next, migrate this new entity

```cli
> $ php artisan migrate                                                                                                                             ⬡ 12.16.3 
Migrating: 2020_11_16_024325_create_posts_table
Migrated:  2020_11_16_024325_create_posts_table (245.29ms)
```

Now let's set up the relationships for these two entities

```php
// Post model
public function user()
{
    return $this->belongsTo(User::class);
}
```

```php
// User model
public function posts()
{
    // In a one to many relationship, use a plural
    return $this->hasMany(Post::class);
}
```

Now lets create the route for this controller over at `web.php`, remember we are following the Laravel restfull naming [contention](https://laravel.com/docs/8.x/controllers#shallow-nesting, so we're using the create action

```php
Route::get('/p', [App\Http\Controllers\PostsController::class, 'create'] );
```

And create this new controller for our posts using artisan

```cli
> $  php artisan make:controller PostsController                                                                                                    ⬡ 12.16.3 
Controller created successfully.
```

## Renaming our Views

We need to create a new view in order to create a new post, this view must be created in our Resources folder, but this is not salable if our app grows in size. To introduce a good practice we will create a folder for each view and in here we'll create `blade` files for each action.

For the profile view, something like this would be needed.

```cli
> $ tree resources/views/profiles                                                                                                                   ⬡ 12.16.3 
resources/views/profiles
└── index.blade.php -> Previously home.blade.php
```

Now we need to alter the profile controller a bit to return this index view.

```php
public function index($user)
{
    $user = User::findOrFail($user);
    return view('profiles.index',[
        'user' => $user
    ]);
}
```

With this in mind, let's create our view for the create post blade. This is the folder structure needed.

```cli
> $ tree resources/views/posts                                                                                                                      ⬡ 12.16.3 
resources/views/posts
└── create.blade.php

0 directories, 1 file
```

This `create.blade.php` file only need to hold the content section for our master layout.

```php
@extends('layouts.app')

@section('content')
<div class="container">
    
</div>
@endsection
```

Now we need to return this view in our `PostsController` in the  `create` action.

```php
public function create()
{
    return view('posts.create')
}
```

Finally let's update the route with the `/create` route endpoint in order to follow the convention.

```php
Route::get('/p/create', [App\Http\Controllers\PostsController::class, 'create'] );
```

The design for our form will mostly reuse the markup for the register form as a base.

```HTML
<div class="row">
  <div class="col-8 offset-2">
    <div class="row"><h1>Add New Post</h1></div>
    <div class="form-group row">
      <label for="caption" class="col-md-4 col-form-label">Post Caption</label>

      <input
        id="caption"
        type="text"
        class="form-control @error('name') is-invalid @enderror"
        name="caption"
        value="{{ old('caption') }}"
        required
        autocomplete="name"
        autofocus
      />

      @error('caption')
      <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
      </span>
      @enderror
    </div>
    <div class="row">
      <label for="image" class="col-md-4 col-form-label">Post Image</label>

      <input type="file" class="form-control-file" id="image" name="image" />
      @error('image')
      <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
      </span>
      @enderror
    </div>
    <div class="row">
      <button class="btn btn-primary mt-4">Add New Post</button>
    </div>
  </div>
</div>
```

To actually POST this form we must post o the resource with the `store` method.

```HTML
<form action="/p" method="POST" enctype="multipart/form-data">
[Form Here]
</form>
```

Let's create this endpoint in our routes with the store method.

```php
Route::post('/p', [\App\Http\Controllers\PostsController::class, 'store']);
```

And let's just dump the request in our create function

```php
public function store()
{
    dd(request()->all());
}
```

We are not ready yet to create a new post into our database, if you where to test this form you would get a 419 error code.

This error codes references a CSRF error, which limits which users are able to post to a URL, Laravel does this through a large key which must be sent in every request. Fixing this is pretty simple and we only need to add a decorator into our form.

```HTML
<form action="/p" method="POST" enctype="multipart/form-data">
    @csrf
    [Form]
</form>
```

This alters our rendered HTML a little bit like so

```HTML
<input type="hidden" name="_token" value="veCVKDWHN7BcaK3fwNyU0YERlHrzEJ9Zz5pfzHp5">
```

If we create this new post, the request information is shown.

```
array:3 [▼
  "_token" => "veCVKDWHN7BcaK3fwNyU0YERlHrzEJ9Zz5pfzHp5"
  "caption" => "New Post"
  "image" => Illuminate\Http\UploadedFile {#284 ▶}
]
```

There's still a little problem surrounding our form, users are able to upload files other than images, say a `.pdf` or a `.md`. We can fix this by adding some validation in our controller.

```php
public function store()
{
    $data = request()->validate([
            'caption'=>'required',
            'image'=>['required','image']
    ]);
}
```

After this is done, there is still another layer of protection provided by Laravel at the model level. Since a user could at his own fields and pass in his own fields Laravel is blocking them (fill able), though this is great, we are already validating these fields (and also naming them) this is not needed so we will be removing this.

In our model let's overwrite this

```php
class Post extends Model
{
    use HasFactory;

    // Do NOT guard
    protected $guarded = [];

}
```

Finally these post still require a `user_id` for the authenticated user, to solution for this is to create the post **through the relationship**. Laravel can do this for use by accessing the authenticated user.

```php
public function store()
{
    $data = request()->validate([
    	'caption'=>'required',
        'image'=>['required','image']
    ]);

    auth()->user()->posts()->create($data);
        
    dd(request()->all());
}
```

This works great, but a unauthenticated user is still able to access this form. We can fix this by using the auth `middleware`

```php
class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    } 
}
```

Now only logged in users are able to access this view.

## Uploading/Saving a Image

Our uploaded images are of type `uploaded file` which is great since this makes it easy for us to handle it with a simple store function.

This can be done using the `public` storage engine which will store all images into the public directory

```php
       	// Upload the image into the public dir
        $imagePath = request('image')->store('uploads', 'public');
```

But these images cannot be accesed yet, we need to link our storage using `artisan`

```
Jeremy@pop-os ~/Documents/Code/freeCodeGram                                                 [22:57:12] 
> $ php artisan storage:link                                                                ⬡ 12.16.3 
The [/home/jeremy/Documents/Code/freeCodeGram/public/storage] link has been connected to [/home/jeremy/Documents/Code/freeCodeGram/storage/app/public].
The links have been created.
```

Now this `imagePath` we can add the path to this post for our user's post entity, but we need to do this manually.

```php
    public function store()
    {
        $data = request()->validate([
            'caption'=>'required',
            'image'=>['required','image']
        ]);

        $imagePath = request('image')->store('uploads', 'public');

        auth()->user()->posts()->create($data);
        
        auth()->user()->posts()->create([
            'caption' => $data['caption'],
            'image' => $imagePath,
        ]);
        
        return redirect('/profile/'. auth()->user()->id);
    }
```

This works but we still need to display the user's images.

```php
    <div class="row pt-5">
        @foreach($user->posts as $post)
        <div class="col-4"><img class="w-100" src="/storage/{{$post->image}}" /></div>
        @endforeach
    </div>
```

The posts are not really ordered as they should be, this can be fixed in the `user` model, by ordering the posts.

```php
  public function posts()
    {
        // In a one to many relationship, use a plural
        return $this->hasMany(Post::class)->orderBy('created_at','DESC');
    }
```

These images have very diferent sizes, there is a library named [Intervention/image](http://image.intervention.io/) which should allow us to resize them in our fetch request. 
```
> $ composer require intervention/image  
```

Usage is pretty simple, access the image path and resize.

```php
    // Fit into a 1200x1200 square.
        $image = Image::make(public_path("storage/{$imagePath}"))->fit(1200, 1200);
        $image->save();
```

Note. I had to install a new library for php, read more [here](https://stackoverflow.com/questions/34009844/gd-library-extension-not-available-with-this-php-installation-ubuntu-nginx)

Let's add the page for viewing our posts, remember there should be a show action for this posts controller.

```php
    <div class="row pt-5">
        @foreach($user->posts as $post)
        <div class="col-4 pb-4">
            <a href="/p/{{$post->id}}">
                <img class="w-100" src="/storage/{{$post->image}}" />
            </a>
        </div>
        @endforeach
    </div>
```

Create the route for the `show` method in `web.php`

```php
Route::get('/p/{post}', [App\Http\Controllers\PostsController::class, 'show'] );
```

```php
   public function show($post)
    {
        dd($post);
    }
```

Now in `resources/views/posts` create `show.blade.php`.
```php
@extends('layouts.app')

@section('content')

<div class="container">
    Show
</div>

@endsection
```

For now this only returns the passed post ID, but Laravel allows us to type this `$post` variable and it will fetch it for us.

```php
    public function show(\App\Models\Post $post)
    {
        dd($post);
    }
```

In this way this post's information is fetched for us and not found errors are handdled for us with a 404.

Let's pass this post's information into the `show` blade.

```php
    public function show(\App\Models\Post $post)
    {
        // return view('posts.show', [
        //     'post' => $post
        // ]);

        return view('posts.show', compact('post'));

    }
```

And render this information.

```php
<div class="container">
    <div class="row">
        <div class="col-8">
            <img class="w-100" src="/storage/{{ $post->image }}">
        </div>
        <div class="col-4">
            <div class="">
                <h3>{{$post->user->username}}</h3>
                <p>{{$post->caption}}</p>
            </div>
        </div>
    </div>
</div>
```