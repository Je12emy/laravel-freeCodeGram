# Sending Emails

To set up our mailing service we will be using [Maltrap](https://mailtrap.io), which already provides the required credentials for a Laravel backend.

In our `.env` file the email paste in the required credentials-

```env
MAIL_USERNAME=secret
MAIL_PASSWORD=secret
```

Laravel allows us to write emails using markdown, artisan has a helpfull command with this template.

```cli
jeremy@pop-os ~/Documents/Code/freeCodeGram                                                 [14:05:52] 
> $ php artisan help make:mail                                                              ⬡ 12.16.3 
Description:
  Create a new email class

Usage:
  make:mail [options] [--] <name>

Arguments:
  name                       The name of the class

Options:
  -m, --markdown[=MARKDOWN]  Create a new Markdown template for the mailable
```

Let's create this template.

```cli
jeremy@pop-os ~/Documents/Code/freeCodeGram                                                 [14:06:01] 
> $ php artisan make:mail NewUserWelcomeMail -m emails.welcome-email                        ⬡ 12.16.3 
Mail created successfully.
```

This will save this template inside `resources/view/emails/welcome-email.blade.php` also in `app/Mail/NewUserWelcomeEmail.php`

We can update our markdown located in `resources/view/emails/welcome-email.blade.php`.

```php
@component('mail::message')
# Welcome to freeCodeGram

This is a community of fellow developers and we love that you have joined us.

All the best,<br>
Jeremy
@endcomponent
```

And we can display this email temporarely by returning a new instance of `NewUserWelcomeEmail.php` in our routes at `web.php`.

```php
// Temporal
Route::get('/email', function () {
    return new NewUserWelcomeMail();
});
```

In the user model we can send out this email with the `Mail` class.

```php
    protected static function boot()
    {
        parent::boot();

        // Fired event when the model is a user is created
        static::created(function ($user){
            $user->profile()->create([
                'title' => $user->username,
            ]);
            Mail::to($user->email)->send(new NewUserWelcomeMail());
        });
    }
```

In mail trap you should be able to see this email.