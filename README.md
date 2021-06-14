# Laravel + Cypress Integration

This package provides the necessary boilerplate to quickly begin testing your Laravel applications using Cypress.

<img src="https://user-images.githubusercontent.com/183223/89684657-e2e5ef00-d8c8-11ea-825c-ed5b5acc37a4.png" width="300">

## Table of Contents

- [Installation](#installation)
- [Environment Handling](#environment-handling)
- [Routing](#routing)
- [API](#api)

## Installation

If you haven't already installed [Cypress](https://www.cypress.io/), that's your first step.

```bash
npm install cypress --save-dev && npx cypress open
```

As part of the initial `npx cypress open` command, Cypress will add a `./cypress` directory to your project root, 
as well as a `cypress.json` configuration file.

You'll almost always want to set a `baseUrl` for your Cypress tests, so do so now within `cypress.json`.

```json
{
  "baseUrl": "http://my-app.test"
}
```

When making requests through Cypress, this `baseUrl` path will be prepended to any relative URL you provide.

```js
cy.visit('/foo'); // http://my-app.test/foo
```

Now you're ready to install this package through Composer. Pull it in as a development-only dependency.

```bash
composer require laracasts/cypress --dev
```

Finally, run the `cypress:boilerplate` command to copy over the initial boilerplate files for your Cypress tests.

```bash
php artisan cypress:boilerplate
```

That's it! You're ready to go.

## Environment Handling

After running the `php artisan cypress:boilerplate` command, you'll now have a `.env.cypress`
file in your project root. To get you started, this file is a duplicate of `.env`. Feel free to update
it as needed to prepare your application for your Cypress tests.

Likely, you'll want to use a special database to ensure that your Cypress acceptance tests are isolated from your development database.

```
DB_CONNECTION=mysql
DB_DATABASE=cypress
```

When running your Cypress tests, this package will automatically back up your primary `.env` file, and swap it out with `env.cypress`.
Once complete, of course the environment files will be reset to how they originally were.

> All Cypress tests run according to the environment specified in `.env.cypress`.

However, when your Cypress tests fail, it can often be useful to manually browse your application in the exact state that triggered the test failure. You can't do this if 
your environment is automatically reverted after each test run. To solve this, temporarily disable the Cypress task that resets the environment. Visit `cypress/support/index.js` and comment 
out this portion.

```js
after(() => {
  // cy.task("activateLocalEnvFile", {}, { log: false });
});
```

That should do it!

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

## API

This package will add a variety of commands to your Cypress workflow to make for a more familiar Laravel testing environment.

We allow for this by exposing a handful of Cypress-specific endpoints in your application. Don't worry: these endpoints will **never** be accessible in production.

### cy.login()

Finds an existing user matching the optional attributes provided and set it as the authenticated user for the test. Create a new user record if not found. 

```js
test('authenticated users can see the dashboard', () => {
  cy.login({ username: 'JohnDoe' });

  cy.visit('/dashboard').contains('Welcome Back, JohnDoe!');
});
```

### cy.logout()

Log out the currently authenticated user. Equivalent to `auth()->logout()`.

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
  cy.create('App\\Post', 3);
});
```

Alternatively, if you pass an object as the second argument to `cy.create()`, you can override any default attributes for the factory call.

```js
test('it shows blog posts', () => {
    cy.create('App\\Post', { title: 'My First Post' });

    //
});
```

Lastly, you can request that certain model relationships be loaded and returned as part of the JSON response. 

```js
test('it shows blog posts', () => {
    cy.create('App\\Post', { title: 'My First Post' }, ['author']);
});
```

As you can see, the `cy.create()` argument list is dynamic to make for a simpler API.

### cy.refreshRoutes()

Before your Cypress test suite begins, this package will automatically fetch a collection of all named routes for your Laravel app and store them in memory.
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

Trigger any Artisan command under the current environment for the Cypress test. Remember to proceed options with two dashes, as usual.

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

### Security

If you discover any security related issues, please email jeffrey@laracasts.com instead of using the issue tracker.

## Credits

- [Jeffrey Way](https://twitter.com/jeffrey_way)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
