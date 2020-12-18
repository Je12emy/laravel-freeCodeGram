# Installing Laravel
First we need to make sure we have [Composer](https://getcomposer.org/) installed in our system. 
- Follow the install script, in linux the [following steps](https://www.osradar.com/how-to-install-composer-on-linux/) may be needed. 
- [Node.js](https://nodejs.org/en/) is also needed along with NPM.
- Install [Laravel](https://laravel.com/docs/8.x#via-laravel-installer) globally using Composer.

After running `composer global require laravel/installer` you may need to add laravel into your `PATH`.

In this proyect we will be replicating instagram, to start a new proyect run `laravel new [proyect name]` in this case `laravel new freeCodeGram`

Similarly to Node proyects, composer has a `composer.json` which holds scripts and dependencies in this proyect.

## Artisan
Artisan is a CLI tool which allows us to interact with our proyect with many usefull commands.

Inside your proyect run `php artisan serve` to start the development server, if you visit the URL provided you should be able to see our new web site.

### Modifying the Site
To modify this HTML we simply modify the welcome page located at `resources/vies/welcome.blade.php` this *blade* file is a template engine used in php.

Now we need to add some authentication to our side, though a little early this is required right now, usually you could run `php artisan make:auth` but this only works with Laravel 5.8. For newer version the following commands are needed.

```cli
composer require laravel/ui
php artisan ui vue --auth
npm install && npm run dev
```

After this, you should be able to see a new file named `welcome.blade.php` file in the same folder and a new `auth` folder. Also if you refresh your site a new Login and Register bar will be shown.

