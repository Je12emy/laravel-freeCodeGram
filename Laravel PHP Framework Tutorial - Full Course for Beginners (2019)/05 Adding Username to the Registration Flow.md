# Adding Username to the Registration Flow

Let's breakdown how we can add a new username field into our application.

## Validation

Creating a new field should be easy by just copying a form field and changing some attributes The file we need to modify is located in `views/auth/register.blade.php`.

```php
<div class="form-group row">
  <label for="username" class="col-md-4 col-form-label text-md-right">Username</label>

  <div class="col-md-6">
    <input
      id="username"
      type="username"
      class="form-control @error('username') is-invalid @enderror"
      name="username"
      value="{{ old('username') }}"
      required
      autocomplete="username"
    />

    @error('username')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>
</div>
```

Here, validation is done by the server which then returns errors if needed and this is done by the controller. To access this controller visit `App/Http/Controllers/Auth/RegisterController.php`. Here's a function which handles validation named `validator`.

```php
    use Illuminate\Support\Facades\Validator;
    class RegisterController extends Controller
    {
      protected function validator(array $data)
        {
            return Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'username' => ['required', 'string', 'min:3', 'max:10', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
        }
    }
```

Adding the validation for this form values is not enough we also need to set up our migration for this new field in our user entity. Visit `database/migrations/xxxx_xx_xx_xxxxxxxx_create_users_table.php`, here we are describing our entity programatically.

```php
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('username')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
```

Do note back when we added the `'unique:users'` property we are checking for duplicates at a **php level**, now that we are doing this at a the migration we are checking at a **database level**, this is more secure since we stop someone from inserting a repeated username directly into the database.

## Creating a New User

Back in our controller we have a function for posting/creating our new users, we need to alter it in order to create the new user to the database.

```php
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);
    }
```

## Interacting with Our Application

If you where to try creating a new user, you would run into a problem which you may not perceive at the beginning. First let's introduce **tinker** (run `php artisan` to view all the available commands), this tools allows us to simulate php and interact with our application. To start the tinker shell simply run `php artisan tinker` and here we can select all our users like so:

```cli
> $ php artisan tinker                                               ⬡ 12.16.3 
Psy Shell v0.10.4 (PHP 7.4.12 — cli) by Justin Hileman
>>> User::all()
[!] Aliasing 'User' to 'App\Models\User' for this Tinker session.
=> Illuminate\Database\Eloquent\Collection {#3996
     all: [
       App\Models\User {#3995
         id: "1",
         name: "Jeremy Zelaya",
         email: "jeremyzelaya@hotmail.es",
         email_verified_at: null,
         created_at: "2020-11-09 07:06:22",
         updated_at: "2020-11-09 07:06:22",
       },
       App\Models\User {#3589
         id: "2",
         name: "Jeremy Zelaya",
         email: "JeremyZelayaR@gmail.com",
         email_verified_at: null,
         created_at: "2020-11-14 00:13:07",
         updated_at: "2020-11-14 00:13:07",
       },
       App\Models\User {#3957
         id: "3",
         name: "Jeremy Zelaya",
         email: "je12emy@protonmail.com",
         email_verified_at: null,
         created_at: "2020-11-14 05:44:45",
         updated_at: "2020-11-14 05:44:45",
       },
     ],
   }
>>> 
```

Notice how our username property is missing, this is because we need to run our migration first. Run `php artisan migrate:fresh` to drop all our tables and use the database blueprint for it's creation.

## Changing Model

Finally we need to define the model for our controller, this indicates which properties will be used in the register controller. This adds a extra layer of protection for our app overall. Visit `Models/User.php`.

```php
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
    ];
```

Now we are ready to register a new user in our application, create a new username using the provided form and if we use tinker again the new username field should show up.

```cli
>>> User::all()
=> Illuminate\Database\Eloquent\Collection {#3995
     all: [
       App\Models\User {#3589
         id: "1",
         name: "Jeremy Zelaya",
         email: "JeremyZelayaR@gmail.com",
         email_verified_at: null,
         username: "DIO",
         created_at: "2020-11-14 05:58:02",
         updated_at: "2020-11-14 05:58:02",
       },
     ],
   }
```

## Rendering User Information

In the top right corner of our application the logged in user's name is rendered, let's change it to render the logged in user's username. Visit `resources/views/layouts/app.blade.php line 64`.
```php
<a
  id="navbarDropdown"
  class="nav-link dropdown-toggle"
  href="#"
  role="button"
  data-toggle="dropdown"
  aria-haspopup="true"
  aria-expanded="false"
  v-pre>
  {{ Auth::user()->username }}
</a>
```