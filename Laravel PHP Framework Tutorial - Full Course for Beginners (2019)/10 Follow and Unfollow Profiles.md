# Follow and Unfollow Profiles Using Vie.js Components

Vue components are located at `resources/js/componets` here we will be creating a follow button to allow us to not re-render the whole page once the follow button is pressed.

```javascript
<template>
    <div>
        <button class="btn btn-primary">Follow</button>
    </div>
</template>

<script>
export default {
    mounted() {
        console.log("Component mounted.");
    }
};
</script>
```

In `resources/js/app` where are able to export this component.

```js
Vue.component('follow-button', require('./components/FollowButton.vue').default);
```

We do need to compile using `npm run dev` but since we will be doing several changes `npm run watch` will compile everytome a JavaScript file is altered.

For our followers controller we will be accessing it's routes using the built in Axios http client, to start of let's create a new endpoint and the followers controller.

```
php artisan make:controller FollowsController
```

In this controller we will be using the `store` action and expect a user.

```php
class FollowsController extends Controller
{
    public function store(User $user){
        return $user->username;
    }
}
```

Now we can set up this endpoint in `web.php`.

```php
Route::post('follow/{user}', [\App\Http\Controllers\FollowsController::class, 'store']);
```

In the `FollowButton.vue` component we need to set up a way to receive the user's id just so we can follow him and a function to fire a post request to the `follow/{user}` endpoint.

We can pass as many props to our vue components, just like we would do in React.

```php
<Follow-Button user-id="{{ $user->id }}"></Follow-Button>
```

Now we need to set up the expeted props and the Axios post request.

```javascript
<script>
export default {
    props: ["userId"],

    mounted() {
        console.log("Component mounted.");
    },

    methods: {
        followUser() {
            axios.post("/follow/" + this.userId).then(response => {
                alert(response.data);
            });
        }
    }
};
</script>
```

This should show the user's username after we click.

## Many to Many Relationship

A profile can have many followers and a user can follow many profiles, this needs a pivot table. Let's create the migration table (now the model with the migration like we did before in [[07 Creating a new Model]]).

This pivot table follows a contention, for the two models which we will be pivoting: User and Profile, they need to be in alphabetical order so: `profileuser` and add in a underscore between them: `profle_user`. For the migration name we will be naming it `creates_profile_user_pivot_table`.

```cli
jeremy@pop-os ~/Documents/Code/freeCodeGram                                                 [12:49:52] 
> $ php artisan make:migration --help                                                       ⬡ 12.16.3 
Description:
  Create a new migration file

Usage:
  make:migration [options] [--] <name>

Arguments:
  name                   The name of the migration

Options:
      --create[=CREATE]  The table to be created
```

```cli
jeremy@pop-os ~/Documents/Code/freeCodeGram                                                 [12:48:16] 
> $ php artisan make:migration creates_profile_user_pivot_table --create profile_user       ⬡ 12.16.3 
Created Migration: 2020_12_17_184945_creates_profile_user_pivot_table
```

Update our migration like so

```php
    public function up()
    {
        Schema::create('profile_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
    }
```

Run this migration

```cli
jeremy@pop-os ~/Documents/Code/freeCodeGram                                                 [13:51:01] 
> $ php artisan migrate                                                                     ⬡ 12.16.3 
Migrating: 2020_12_17_184945_creates_profile_user_pivot_table
Migrated:  2020_12_17_184945_creates_profile_user_pivot_table (134.17ms)
```

Now let's set up these relationships. In the user model:

```php
public function following()
    {
        return $this->belongsToMany(Profile::class);
    }
```

In the profile model

```php
public function followers()
    {
        return $this->belongsToMany(User::class);
    }
```

Now in the `store` function we need a way to attach or detach the relationship between the authenticated user and the profile to be followed.

```php
class FollowsController extends Controller
{
    public function store(User $user)
    {
        // Grab the authenticated user, invoke the following function 
        // and attach or detach the relationship between these two
        return auth()->user()->following()->toggle($user->profile);
    }
}
```

Let's set up the front-end to handdle this toggle, in the `ProfileController` we need to pass wheter the current user is following the visited user profile.

```php
  public function index(User $user)
    {
        // If the authenticated user is following the visited profile (find by id)
        $follows = auth()->user() ? auth()->user()->following->contains($user->id) : false;
        return view('profiles.index', compact('user', 'follows'));
    }
```

From the index view pass this status into the vue component.

```php
<Follow-Button user-id="{{ $user->id }}" follows="{{$follows}}"></Follow-Button>
```

Set up our state for this button which is passed from the props.

```js
<script>
export default {
    props: ["userId", "follows"],

    data: function() {
        return {
            status: this.follows
        };
    },
</script>
```

Set up a method to toggle the button text based on the status.

```js
<script>
export default {
    computed: {
        buttonText() {
            return this.status ? "Unfollow" : "Follow";
        }
    }
};
</script>
```

And finnaly flip the state based on the axios post request response.

```js
<script>
export default {
    methods: {
        followUser() {
            axios.post("/follow/" + this.userId).then(response => {
                this.status = !this.status
                console.log(response.data);
            });
        }
    },
</script>
```

The problem right now, is that non authenticated users are able to trigger the `store` function. We can prevent this by applying the `auth` middleware in the `FollowController`.

```php
class FollowsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
}
```

This should allows us to `catch` the related error code and redirect the user into the login page. This catch block is done in the vue component.

```js
export default {
    methods: {
        followUser() {
            axios.post("/follow/" + this.userId).then(response => {
                this.status = !this.status;
                console.log(response.data);
            })
            .catch(errors => {
                if (errors.response.status === 401) {
                    window.location = '/login'
                }
            });
        }
    },
};
</script>
```

We are now able to display the profile's followers and followed profiles.

```php
<div class="d-flex">
	<div class="pr-5"><strong>{{ $user->posts->count() }}</strong> posts</div>
	<div class="pr-5"><strong>{{ $user->profile->followers->count() }}</strong> followers</div>
	<div class="pr-5"><strong>{{ $user->following->count() }}</strong> following</div>
</div>
```