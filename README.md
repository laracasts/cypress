# Laravel + Cypress Integration

[![Total Downloads](https://img.shields.io/packagist/dt/laracasts/cypress.svg?style=flat-square)](https://packagist.org/packages/laracasts/cypress)

This package provides the necessary boilerplate to quickly begin testing your Laravel applications using Cypress.

## Installation

Begin by installing the package through Composer.

```bash
composer require laracasts/cypress
```

If you haven't yet pulled in Cypress through npm, that's your next step:

```bash
npm install cypress --save-dev && npx cypress open
```

Next, generate the Laravel Cypress boilerplate:

```bash
php artisan cypress:boilerplate
```

This command will generate a number of Cypress files to assist with testing a Laravel application.

The final step is to update your `cypress.json` file with the `baseUrl` of your application.

```json
{
    "baseUrl": "http://my-app.test"
}
```

When making requests in your Cypress tests, this `baseUrl` will be prepended to any relative URL you provide.

## Environment Handling

After running the `php artisan cypress:boilerplate` command, you'll now have a `.env.cypress` 
file in your project root. To get you started, this file is a duplicate of `.env`. Feel free to update 
it as needed to prepare your application for your Cypress tests. 

Likely, you'll want to use a special database to ensure that your Cypress tests are isolated from your local database.

```
DB_CONNECTION=mysql
DB_DATABASE=cypress
```

When running your Cypress tests, this package will automatically back up your primary `.env` file, and swap it out with `env.cypress`. 
Once complete, of course the environment files will be reset to how they originally were. 

> All Cypress tests run according to the environment specified in `.env.cypress`.

## Usage

Coming soon...

##### cy.login()

##### cy.logout()

##### cy.create()

##### cy.refreshDatabase()

##### cy.artisan()

## Examples

Coming soon...

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email jeffrey@laracasts.com instead of using the issue tracker.

## Credits

- [Jeffrey Way](https://github.com/laracasts)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
