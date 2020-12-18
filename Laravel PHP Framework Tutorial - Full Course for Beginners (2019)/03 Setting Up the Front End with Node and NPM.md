# Setting Up the Front End with Node and NPM
Laravel does include a frontend implementation with [Bootstrap](https://getbootstrap.com/) and [Vue.js](https://vuejs.org/). There are still ways to use [React](https://reactjs.org/) instead of Vue. Before we get started with the frontend, let's install all the npm dependencies included in our project, run `npm install`

Once the instalation is done, we need to compile, run `npm run dev` to compile all our Javascript. This single Javascript file is located in the `public` directory but we don't need to work in this file, in the `resources/js/` we will find the file we will be working with.

## Creating a DataBase
We still need a way to register new users and a database is needed for this. A migration is a file which describes our whole database which is then used as a base for it's creation.

For this project we will be using [SLite](https://sqlite.org/index.html), let's create a new SQLite database file.

```cli
touch database/database.sqlite
```
With our database created, let's configure our enviroment. Alter the `.env` file by changing our `DB_CONNECTION` to `sqllite`

```.env
DB_CONNECTION=sqlite
```
*You can remove the rest of the DB variables*

Now we can migrate our database by running `php artisan migrate`, after our migration is done we can register our new user. 

*Do restart the server, since we modified our .env file*