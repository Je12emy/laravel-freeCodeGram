# Telescope
[Telescope](https://laravel.com/docs/8.x/telescope) is a companion app to monitor all activity in our application.

To install Telescope run: `composer require laravel/telescope`. Now we have access to more commands in `artisan`.

```cli
jeremy@pop-os ~/Documents/Code/freeCodeGram                                                 [14:46:33] 
> $ php artisan                                                                             ⬡ 12.16.3 
Laravel Framework 8.13.0

Usage:
  command [options] [arguments]

Options:

Available commands:
telescope
  telescope:clear      Clear all entries from Telescope
  telescope:install    Install all of the Telescope resources
  telescope:prune      Prune stale entries from the Telescope database
  telescope:publish    Publish all of the Telescope resources
```

Install Telescope

```cli
jeremy@pop-os ~/Documents/Code/freeCodeGram                                                 [14:46:35] 
> $ php artisan telescope:install                                                           ⬡ 12.16.3 
Publishing Telescope Service Provider...
Publishing Telescope Assets...
Publishing Telescope Configuration...
Telescope scaffolding installed successfully.
```

And run the required migration for telescope to run.

```cli
jeremy@pop-os ~/Documents/Code/freeCodeGram                                                 [14:47:40] 
> $ php artisan migrate                                                                     ⬡ 12.16.3 
Migrating: 2018_08_08_100000_create_telescope_entries_table
Migrated:  2018_08_08_100000_create_telescope_entries_table (1,284.86ms)
```

Visit: http://127.0.0.1:8000/telescope/ in order to access the Telescope GUI.