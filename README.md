# Laravel + Cypress Integration

This package provides the necessary boilerplate to quickly begin testing your Laravel applications using Cypress.

<img src="https://user-images.githubusercontent.com/183223/89684657-e2e5ef00-d8c8-11ea-825c-ed5b5acc37a4.png" width="300">

## Video Tour

If you'd prefer a more visual review of this package, [please watch this video](https://laracasts.com/series/jeffreys-larabits/episodes/22) on Laracasts.

## Table of Contents

- [Installation](#installation)
- [Environment Handling](#environment-handling)
- [API](#api)
- [Routing](#routing)

## Installation

If you haven't already installed [Cypress](https://www.cypress.io/); that's your first step.

```bash
npm install cypress --save-dev
```

Now you're ready to install this package through Composer. Pull it in as a development-only dependency.

```bash
composer require laracasts/cypress --dev
```

Finally, run the `cypress:boilerplate` command to copy over the initial boilerplate files for your Cypress tests.

```bash
php artisan cypress:boilerplate
```

That's it! You're ready to go. We've provided an `example.cy.js` spec for you to play around with it. Let's run it now:

```
npx cypress open
```

In the Cypress window that opens, Choose "E2E Testing," and then "Start E2E Testing in Chrome." This will bring up a list of all specs in your application. Of course, at this point, we only have the single example spec. Click `example.cy.js` to run it. Wew! All green.

## Cypress Configuration

We've declared some initial settings in your project's `cypress.config.js` file. Have a quick look now to ensure that everything is in order. In particular, please ensure that the `baseUrl` property is set correctly (we default to your app's `APP_URL` environment setting).

## Environment Handling

After running the `php artisan cypress:boilerplate` command, you'll now have a `.env.cypress`
file in your project root. To get you started, this file is a duplicate of `.env`. Feel free to update
it as needed to prepare your application for your Cypress tests.

Likely, you'll want to use a special database to ensure that your Cypress acceptance tests are isolated from your development database.

```
DB_CONNECTION=mysql
DB_DATABASE=cypress
```

When running your Cypress tests, this package, by default, will automatically back up your primary `.env` file, and swap it out with `env.cypress`.
Once complete, of course the environment files will be reset to how they originally were.

> All Cypress tests run according to the environment specified in `.env.cypress`.

However, when your Cypress tests fail, it can often be useful to manually **browse your application in the exact state that triggered the test failure**. You can't do this if your environment is automatically reverted after each test run. 

To solve this, you have two choices:

#### Option 1:

Temporarily disable the Cypress task that resets the environment. Visit `cypress/support/index.js` and comment 
out this portion.

```js
after(() => {
  // cy.task("activateLocalEnvFile", {}, { log: false });
});
```

That should do it! Just remember to manually revert to your local .env file when you're done performing your Cypress tests.

#### Option 2:

When booting a server with `php artisan serve`, you can optionally pass an `--env` flag to specify your desired environment for the application.

```
php artisan serve --env="cypress"
```

^ This command instructs Laravel to boot up a server and use the configuration that is declared in `.env.cypress`.

Now visit `cypress.json` and change the `baseUrl` to point to your local server.

```
{
  "baseUrl": "http://127.0.0.1:8000"
}
```

And you're all set! I'd recommend creating an npm script to simplify this process. Open `package.json`, and add:

```
{
  "scripts": {
    "test:cypress": "php artisan serve --env=cypress & cypress open"
  }
}
```

Now from the command line, you can run `npm run test:cypress` to start a local server and open Cypress.

If you choose this second option, visit `cypress/support/index.js` and delete the `activateCypressEnvFile` and `activateLocalEnvFile` tasks, [as shown here](https://github.com/laracasts/cypress/blob/master/src/stubs/support/index.js#L23). They're no longer required, as you'll be handling the environment handling yourself.


## API

This package will add a variety of commands to your Cypress workflow to make for a more familiar Laravel testing environment.

We allow for this by exposing a handful of Cypress-specific endpoints in your application. Don't worry: these endpoints will **never** be accessible in production.

### cy.login()

Find an existing user matching the optional attributes provided and set it as the authenticated user for the test. If not found, it'll create a new user and log it in.

```js
test('authenticated users can see the dashboard', () => {
  cy.login({ username: 'JohnDoe' });

  cy.visit('/dashboard').contains('Welcome Back, JohnDoe!');
});
```

Should you need to also eager load relationships on the user model or specifiy a certain model factory state before it's returned from the server, instead pass an object to `cy.login()`, like so:

```js
test('authenticated users can see the dashboard', () => {
    cy.login({
        attributes: { username: 'JohnDoe' },
        state: ['guest'],
        load: ['profile']
    });

    cy.visit('/dashboard').contains('Welcome Back, JohnDoe!');
});
```

If written in PHP, this object would effectively translate to: 

```php
$user = User::factory()->guest()->create([ 'username' => 'JohnDoe' ])->load('profile');

auth()->login($user);
````

### cy.currentUser()

Fetch the currently authenticated user from the server, if any. Equivalent to Laravel's `auth()->user()`.

```js
test('assert the current user has email', () => {
    cy.login({ email: 'joe@example.com' });

    cy.currentUser().its('email').should('eq', 'joe@example.com');
    
    // or...
    
    cy.currentUser().then(user => {
        expect(user.email).to.eql('joe@example.com');
    });
});
```

### cy.logout()

Log out the currently authenticated user. Equivalent to Laravel's `auth()->logout()`.

```js
test('once a user logs out they cannot see the dashboard', () => {
  cy.login({ username: 'JohnDoe' });

  cy.logout();

  cy.visit('/dashboard').assertRedirect('/login');
});
```

### cy.create()

Use Laravel factories to create and persist a new Eloquent record.

```js
test('it shows blog posts', () => {
  cy.create('App\\Post', { title: 'My First Post' });

  cy.visit('/posts').contains('My First Post');
});
```

Note that the `cy.create()` call above is equivalent to:

```php
App\Post::factory()->create(['title' => 'My First Post']);
```

You may optionally specify the number of records you require as the second argument. This will then return a collection of posts.

```js
test('it shows blog posts', () => {
  cy.create('App\\Post', 3, { title: 'My First Post' });
});
```

Lastly, you can alternatively pass an object to `cy.create()`. This should be the preferred choice, if you need to eager load relationships or create the model record in a given model factory state.

```js
test('it shows blog posts', () => {
    cy.create({
        model: 'App\\Post',
        attributes: { title: 'My First Post' },
        state: ['archived'],
        load: ['author'],
        count: 10
    })
});
```

If written in PHP, this object would effectively translate to:

```php
$user = \App\Post::factory(10)->archived()->create([ 'title' => 'My First Post' ])->load('author');

auth()->login($user);
````

### cy.refreshRoutes()

Before your Cypress test suite run, this package will automatically fetch a collection of all named routes for your Laravel app and store them in memory.
You shouldn't need to manually call this method, however, it's available to you if your routing will change as side effect of a particular test.

```js
test('it refreshes the list of Laravel named routes in memory', () => {
    cy.refreshRoutes();
});
```

### cy.refreshDatabase()

Trigger a `migrate:refresh` on your test database. Often, you'll apply this in a `beforeEach` call to ensure that,
before each new test, your database is freshly migrated and cleaned up.

```js
beforeEach(() => {
  cy.refreshDatabase();
});

test('it does something', () => {
  // php artisan migrate:fresh has been
  // called at this point.
});
```

### cy.seed()

Run all database seeders, or a single class, in the current Cypress environment.

```js
test('it seeds the db', () => {
  cy.seed('PlansTableSeeder');
});
```

Assuming that `APP_ENV` in your `.env.cypress` file is set to "acceptance," the call above would be equivalent to:

```bash
php artisan db:seed --class=PlansTableSeeder --env=acceptance
```

### cy.artisan()

Trigger any Artisan command under the current environment for the Cypress test. Remember to precede options with two dashes, as usual.

```js
test('it can create posts through the command line', () => {
  cy.artisan('post:make', {
    '--title': 'My First Post',
  });

  cy.visit('/posts').contains('My First Post');
});
```

This call is equivalent to:

```bash
php artisan post:make --title="My First Post"
```

### cy.php()

While not exactly in the spirit of acceptance testing, this command will allow you to trigger and evaluate arbitrary PHP.

```js
test('it can evaluate PHP', () => {
    cy.php(`
        App\\Plan::first();
    `).then(plan => {
        expect(plan.name).to.equal('Monthly'); 
    });
});
```

Be thoughtful when you reach for this command, but it might prove useful in instances where it's vital that you verify the state of the application or database in response to a certain action. It could also be used 
for setting up the "world" for your test. That said, a targeted database seeder - using `cy.seed()` - will typically be the better approach.

## Routing

Each time your test suite runs, this package will fetch all named routes for your Laravel application,
and store them in memory. You'll additionally find a `./cypress/support/routes.json` file that contains a dump of this JSON.

This package overrides the base `cy.visit()` method to allow for optionally passing a `route` name instead of a URL.

```js
test('it loads the about page using a named route', () => {
    cy.visit({
        route: 'about'
    });
});
```

If the named route requires a wildcard, you may include it using the `parameters` property.

```js
test('it loads the team dashboard page using a named route', () => {
    cy.visit({
        route: 'team.dashboard',
        parameters: { team: 1 }
    });
});
```

Should you need to access the full list of routes for your application, use the `Cypress.Laravel.routes` property.

```js
// Get an array of all routes for your app.

Cypress.Laravel.routes; // ['home' => []]
```

Further, if you need to translate a named route to its associated URL, instead use the `Cypress.Laravel.route()` method, like so:

```js
Cypress.Laravel.route('about'); // /about-page

Cypress.Laravel.route('team.dashboard', { team: 1 }); // /teams/1/dashboard
```

### Security

If you discover any security related issues, please email jeffrey@laracasts.com instead of using the issue tracker.

## Credits

- [Jeffrey Way](https://twitter.com/jeffrey_way)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
