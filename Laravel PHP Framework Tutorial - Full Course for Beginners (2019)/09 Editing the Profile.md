# Editing the Profile
Let's add a Edit Profile view, start off by adding a link into this route.
```php
 <a href="/profile/{{$user->id}}/edit">Edit Profile</a>
```

Create this route in `web.php`

```php
Route::get('/profile/{user}/edit', [\App\Http\Controllers\ProfilesController::class, 'edit'])->name('profile.edit');
```

Return the desired view

```php
 public function edit(User $user)
    {
        return view('profiles.edit', compact('user'));
    }
```

And create this view in `resources/views/profiles/edit.blade.php`, we will be resuing the form code from the create post.

```php
     <form action="/profile/{{$user->id}}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-8 offset-2">
                <div class="row">
                    <h1>Edit Profile</h1>
                </div>
                <div class="form-group row">
                    <label for="title" class="col-md-4 col-form-label">Title</label>

                    <input id="title" type="text" class="form-control @error('name') is-invalid @enderror" name="title" value="{{ old('title') ?? $user->profile->title }}" required autocomplete="title" autofocus>

                    @error('title')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group row">
                    <label for="description" class="col-md-4 col-form-label">Description</label>

                    <input id="description" type="text" class="form-control @error('name') is-invalid @enderror" name="description" value="{{ old('description') ?? $user->profile->description }}" required autocomplete="description" autofocus>

                    @error('description')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group row">
                    <label for="URL" class="col-md-4 col-form-label">URL</label>

                    <input id="URL" type="text" class="form-control @error('URL') is-invalid @enderror" name="URL" value="{{ old('URL') ?? $user->profile->url }}" required autocomplete="URL" autofocus>

                    @error('URL')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>


                <div class="row">
                    <label for="image" class="col-md-4 col-form-label">Profile Image</label>

                    <input type="file" class="form-control-file" id="image" name="image">
                    @error('image')
                    <strong class="alert">{{ $message }}</strong>
                    @enderror
                </div>
                <div class="row">
                    <button class="btn btn-primary mt-4">Save Profile</button>
                </div>
            </div>
        </div>
    </form>
```

Let's set up the update function and it's validation

```php
    public function update(User $user)
    {
        $data = request()->validate([
            'title' => 'required',
            'description' => 'required',
            'url'=>'url',
            'image'=>''
        ]);
        dd($data);
		$user->profile->update($data);
		return redirect("/profile/{$user->id}");
    }
```

Disable the model protection

```php
class Profile extends Model
{
    // Disable protection
    protected $guarded = [];
}
```

Now let's restric guess users from editing a user's profile

```php
    public function update(User $user)
    {
        // grab de authenticated user
        auth()->user()->profile->update($data);
    }
```

Now as a guess user trying to update a profile, a error with be thrown.

## Model Policy

A policy is a simple way to restric what a user can do with a specific user, say a policy for the profile. 

We can create a new policy for our app using `artisan`

```cli
jeremy@pop-os ~/Documents/Code/freeCodeGram                        [15:06:24] 
> $ php artisan make:policy ProfilePolicy -m Profile               ⬡ 12.16.3 
Policy created successfully.
```

This new policy will show up at` app/Http/Policies`, we want to limit which users can update the user profile, in this policy there is a resource for `update`

```php
  public function update(User $user, Profile $profile)
    {
        // Restrict only users which own the profile to be updated
        return $user->id==$profile->user_id;
    }
```

Now let's apply this authorization in the edit profile view.

```php
 public function edit(User $user)
    {
        // Apply authorization for user's profile
        $this->authorize('update', $user->profile);
    }

    public function update(User $user)
    {
        // Apply authorization for user's profile
        $this->authorize('update', $user->profile);

    }
```

This restricts access for the `update` function, throwing a 403 error if you do not own the profile.

Finally let's hide the `Edit Profile` link for unauthorized users, using a `blade` directive

```php
@can('update',$user->profile)
 	<a href="/profile/{{$user->id}}/edit">Edit Profile</a>
@endcan
```

## Editing the Profile Image

We need to update our `ProfilesController` to handle a profile image, the thing is, there is a chance the user may not update their profile image everytime they wish to update theirp profile. We need to check is a profile image has been passed in our request and then handle it.

```php
    public function update(User $user)
    {   
        if (request('image')) {
            // create an image manager instance with favored driver
            $imagePath = request('image')->store('profile', 'public');
            // Fit into a 1200x1200 square.
            $image = Image::make(public_path("storage/{$imagePath}"))->fit(1000, 1000);
            $image->save();
        }

        // grab de authenticated user
        // array_merge replaces the matched key in the first array with the seccond array0s mathced keys
        auth()->user()->profile->update(array_merge(
            $data,
            ['image' => $imagePath]
        ));
        
        return redirect("/profile/{$user->id}");
    }
```

Update the `create_profile` migration, which need a image column.

```php
 public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('img')->nullable();
            $table->timestamps();
            // Should always add a index for FK's
            $table->index('user_id');
        });
    }
```

And we now need to migrate.

```cli
jeremy@pop-os ~/Documents/Code/freeCodeGram                                                 [10:38:53] 
> $ php artisan migrate:fresh                                                               ⬡ 12.16.3 
Dropped all tables successfully.
Migration table created successfully.
```

But we will run into a profile, when a user registers there is no profile attached to him wich causes the app to crash.

## Model Events

Model events can be configured in the `model`, with the `boot` function we have access to several events (read more [here](https://laravel.com/docs/8.x/eloquent#events))

```php
    protected static function boot()
    {
        parent::boot();

        // Fired event when the model is a user is created
        static::created(function ($user){
            $user->profile()->create([
			     // Create a default value for title
                'title' => $user->username,
            ]);
        });
    }
```

Update our HTML to render the profile image.

```php
<div class="col-3 pt-5">
	<img class="rounded-circle w-100" src="/storage/{{$user->profile->image}}">
</div>
```

## The Default Profile Image

We can craete a function to return the user profile image or a default image when not provided, this is done in the profile model located at `app/Models/Profile.php`.

```php
class Profile extends Model
{
    // Get the profile image
    public function profileImage()
    {
        $imagePath = ($this->image) ? $this->image : "http://127.0.0.1:8000/storage/profile/bUnSucoES6fm3zoYypRQ5NlkINHbJf95OdmySyzp.png";
        return '/storage/' . $imagePath;
    }
}
```

And we can replace the profile image property in the HTML using this function.

```php
<div class="col-3 pt-5">
 	<img class="rounded-circle w-100" src="{{$user->profile->profileImage()}}">
</div>
```