# Creating a Profile Controller

Controllers manage our business logic along with routing, the routes which point towards each controller are located at `routes/web.php`.

```php
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
```

As you can see it points towards a action "index" which matches a function in the `HomeController`.

```php
public function index()
{
    return view('home');
}
```

This controller does implement some auth middleware which is fine but for our use case a public profile does not need any special authentication to be visited.

## Creating a Controller with PHP Artisan

As mention earlier [[02 Installing Laravel#Artisan|artisan]] allows us to interact with our application in many ways, the [[02 Installing Laravel#Modifying the Site|first time]] we did this we had set up the authentication for our project. Now we'll be using artisan to create a new controller for our Profiles. Do remember we can use the `--help` flag with any command available:

```cli
> $ php artisan make:controller --help                                                                                                              ⬡ 12.16.3 
Description:
  Create a new controller class

Usage:
  make:controller [options] [--] <name>

Arguments:
  name                   The name of the class
```

So to create a new controller use the following command

```cli
> $ php artisan make:controller ProfilesController                                                                                                  ⬡ 12.16.3 
Controller created successfully.
```

This new `ProfilesController` should show up at `Http/Controllers/Auth/ProfilesController`, so inside this new class we can return the "Home" view.

```php
class ProfilesController extends Controller
{
    /**
     * Show the user profile.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
}
```

And modify our routes to use this controller instead.

```php
Route::get('/home', [App\Http\Controllers\ProfilesController::class, 'index'])->name('home');
```

## Conventions

When using Laravel we should take the [conventions](https://laravel.com/docs/8.x/controllers#actions-handled-by-resource-controller) into consideration, since these allow our controller to stay light, fast in performance and easy to maintain. For our profile view we wish to return only a **single** user profile and not all of the profiles, so maybe a URL like `/profile/{id}` or maybe `/url/{username}` just like it's done in Instagram

So in our `web.php` file we'll implement a convention like so;

```php
Route::get('/profile/{user}', [App\Http\Controllers\ProfilesController::class, 'index'])->name('profile.show');
```

_But how to we access this user?_ In our `ProfilesController` we can alter our index function to receive the parameter through the URL.

```php
    public function index($user)
    {
        dd($user);
        return view('home');
    }
```

_`dd` is a useful function which allows us to log a variable and stop all other processes_

If we where to visit this profile though `/profile/1` the number 1 would be logged and we can use this parameter to fetch our database with the use of our user model like so:

```php
    public function index($user)
    {
        $user = User::find($user);
        return view('home',[
            'user' => $user
        ]);
    }
```

Here ware passing the user model into the view, so we need to access this variable in our view.

```php
<div>
	<h1>{{ $user->username }}</h1>
</div>
```