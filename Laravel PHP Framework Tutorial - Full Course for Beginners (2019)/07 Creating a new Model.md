# Creating a new Model

In order to display more information in our profile view, we need to capture more data other than the user's username. To do this we'll need to create a new model no represent our data and then render these fields in our views.

To create a new model we may use artisan with the make command

```cli
> $ php artisan make:model --help                                                                                                                   ⬡ 12.16.3 
Description:
  Create a new Eloquent model class

Usage:
  make:model [options] [--] <name>

Arguments:
  name                  The name of the class
Options:
  -m, --migration       Create a new migration file for the model
```

This _eloquent_ term means that these models are database agnostic and they work with any database as long as we write eloquent queries. With this command we are also able to create a migration file for this specific model.

```cli
> $ php artisan make:model Profile -m                                                                                                               ⬡ 12.16.3 
Model created successfully.
Created Migration: 2020_11_15_044057_create_profiles_table
```

Let's update our migration for the user profile:

```php
Schema::create("profiles", function (Blueprint $table) {
    $table->id();
    $table->string("title")->nullable();
    $table->text("description")->nullable();
    $table->string("url")->nullable();
    $table->timestamps();
});
```

## Relationships

Since this profile belongs to a user, we need to reference our user table, the type for this foreign key should be `unsignedBigInteger` and a index should be created for this field.

```php
Schema::create("profiles", function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger("user_id");
    $table->string("title")->nullable();
    $table->text("description")->nullable();
    $table->string("url")->nullable();
    $table->timestamps();
    // Should always add a index for FK's
    $table->index("user_id");
});
```

Now we need to migrate this table

```cli
> $ php artisan migrate                                                                                                                             ⬡ 12.16.3 
Migrating: 2020_11_15_044057_create_profiles_table
Migrated:  2020_11_15_044057_create_profiles_table (153.87ms)
```

Laravel makes it easy to specify relationships between tables, this is done at the profile model.

```php
class Profile extends Model
{
    use HasFactory;
    public function user()
    {
        return $this->BelongsTo(User::class);
    }
}
```

And the same need to be done with the user, which has one single profile.

```php
public function profile()
{
    return $this->hasOne(Profile::class);
}
```

Now we're ready to create a new profile for our user using tinker.

```php
> $ php artisan tinker                                                                                                                              ⬡ 12.16.3 
Psy Shell v0.10.4 (PHP 7.4.12 — cli) by Justin Hileman
>>> $profile = new \App\Models\Profile();
=> App\Models\Profile {#3272}
>>> $profile->title = 'Cool title';
=> "Cool title"
>>> $profile->description = 'Cool description';
=> "Cool description"
>>> $profile->user_id = 1
=> 1
>>> $profile->save();
=> true
>>> 
```

With this profile we can actually call our user

```php
>>> $profile->user
=> App\Models\User {#3995
     id: "1",
     name: "Jeremy Zelaya",
     email: "JeremyZelayaR@gmail.com",
     email_verified_at: null,
     username: "DIO",
     created_at: "2020-11-14 05:58:02",
     updated_at: "2020-11-14 05:58:02",
   }
>>> 
```

The same can be done in reverse

```php
>>> $user = \App\Models\User::find(1);
=> App\Models\User {#4060
     id: "1",
     name: "Jeremy Zelaya",
     email: "JeremyZelayaR@gmail.com",
     email_verified_at: null,
     username: "DIO",
     created_at: "2020-11-14 05:58:02",
     updated_at: "2020-11-14 05:58:02",
   }
>>> $user->profile
=> App\Models\Profile {#4061
     id: "1",
     user_id: "1",
     title: "Cool title",
     description: "Cool description",
     url: null,
     created_at: "2020-11-15 05:25:20",
     updated_at: "2020-11-15 05:25:20",
   }
>>> 
```

So now we can access this properties in our view which has access to the user itself and we can invoke it's profile.

```php
<div class="pt-4"><strong>{{$user->profile->title}}</strong></div>
<div>{{$user->profile->description}}</div>
<a href="">{{$user->profile->   url}}</a>
```

Now if we where to create a field for the profile's url we have a extra function.

```php
>>> $user->profile->url = "https://www.twitch.tv/imDio_"
=> "https://www.twitch.tv/imDio_"
>>> $user->profile->save();
=> true
>>> // ^^^ Does not save profile changes
>>> $user->push();
=> true
>>> // ^^ Saves all changes
```

_What is the user profile is not found?_ Instead of returning a error stack a 404 error would be the **apropiate** response, Laravel provides a method for this.

```php
public function index($user)
{
    $user = User::findOrFail($user);
    return view('home',[
        'user' => $user
    ]);
}
```
