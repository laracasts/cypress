/**
 * Create a new user and log them in.
 *
 * @param {Object} attributes
 *
 * @example cy.login();
 *          cy.login({ name: 'JohnDoe' });
 */
Cypress.Commands.add('login', (attributes = {}) => {
    return cy
        .csrfToken()
        .then((token) => {
            return cy.request({
                method: 'POST',
                url: '/__cypress__/login',
                body: { attributes, _token: token },
                log: false,
            });
        })
        .then(({ body }) => {
            Cypress.Laravel.currentUser = body;

            Cypress.log({
                name: 'login',
                message: attributes,
                consoleProps: () => ({ user: body }),
            });
        })
        .its('body', { log: false });
});

/**
 * Logout the current user.
 *
 * @example cy.logout();
 */
Cypress.Commands.add('logout', () => {
    return cy
        .csrfToken()
        .then((token) => {
            return cy.request({
                method: 'POST',
                url: '/__cypress__/logout',
                body: { _token: token },
                log: false,
            });
        })
        .then(() => {
            Cypress.log({ name: 'logout', message: '' });
        });
});

/**
 * Fetch a CSRF token.
 *
 * @example cy.csrfToken();
 */
Cypress.Commands.add('csrfToken', () => {
    return cy
        .request({
            method: 'GET',
            url: '/__cypress__/csrf_token',
            log: false,
        })
        .its('body', { log: false });
});

/**
 * Fetch and store all named routes.
 *
 * @example cy.refreshRoutes();
 */
Cypress.Commands.add('refreshRoutes', () => {
    return cy.csrfToken().then((token) => {
        return cy
            .request({
                method: 'POST',
                url: '/__cypress__/routes',
                body: { _token: token },
                log: false,
            })
            .its('body', { log: false })
            .then((routes) => {
                cy.writeFile('cypress/support/routes.json', routes, {
                    log: false,
                });

                Cypress.Laravel.routes = routes;
            });
    });
});

/**
 * Visit the given URL or route.
 *
 * @example cy.visit('foo/path');
 *          cy.visit({ route: 'home' });
 *          cy.visit({ route: 'team', parameters: { team: 1 } });
 */
Cypress.Commands.overwrite('visit', (originalFn, subject, options) => {
    if (subject.route) {
        return originalFn({
            url: Cypress.Laravel.route(subject.route, subject.parameters || {}),
            method: Cypress.Laravel.routes[subject.route].method[0],
        });
    }

    return originalFn(subject, options);
});

/**
 * Create a new Eloquent factory.
 *
 * @param {String} model
 * @param {Number|null} times
 * @param {Object} attributes
 *
 * @example cy.create('App\\User');
 *          cy.create('App\\User', 2);
 *          cy.create('App\\User', 2, { active: false });
 *          cy.create('App\\User', { active: false });
 *          cy.create('App\\User', 2, { active: false });
 *          cy.create('App\\User', 2, { active: false }, ['profile']);
 *          cy.create('App\\User', { active: false }, ['profile']);
 *          cy.create('App\\User', ['profile']);
 */
Cypress.Commands.add('create', (model, times = 1, attributes = {}, relations = []) => {
    if (Array.isArray(times)) {
        attributes = {};
        relations = times;
        times = 1;
    }

    if (typeof times === 'object') {
        relations = attributes;
        attributes = times;
        times = 1;
    }

    return cy
        .csrfToken()
        .then((token) => {
            return cy.request({
                method: 'POST',
                url: '/__cypress__/factory',
                body: { attributes, model, times, relations, _token: token },
                log: false,
            });
        })
        .then((response) => {
            Cypress.log({
                name: 'create',
                message: model + (times ? `(${times} times)` : ''),
                consoleProps: () => ({ [model]: response.body }),
            });
        })
        .its('body', { log: false });
});

/**
 * Refresh the database state.
 *
 * @param {Object} options
 *
 * @example cy.refreshDatabase();
 *          cy.refreshDatabase({ '--drop-views': true });
 */
Cypress.Commands.add('refreshDatabase', (options = {}) => {
    return cy.artisan('migrate:fresh', options);
});

/**
 * Seed the database.
 *
 * @param {String} seederClass
 *
 * @example cy.seed();
 *          cy.seed('PlansTableSeeder');
 */
Cypress.Commands.add('seed', (seederClass) => {
    return cy.artisan('db:seed', {
        '--class': seederClass,
    });
});

/**
 * Trigger an Artisan command.
 *
 * @param {String} command
 * @param {Object} parameters
 * @param {Object} options
 *
 * @example cy.artisan('cache:clear');
 */
Cypress.Commands.add('artisan', (command, parameters = {}, options = {}) => {
    options = Object.assign({}, { log: true }, options);

    if (options.log) {
        Cypress.log({
            name: 'artisan',
            message: command,
            consoleProps: () => ({ command, parameters }),
        });
    }

    return cy.csrfToken().then((token) => {
        return cy.request({
            method: 'POST',
            url: '/__cypress__/artisan',
            body: { command: command, parameters: parameters, _token: token },
            log: false,
        });
    });
});

/**
 * Execute arbitrary PHP.
 *
 * @param {String} command
 *
 * @example cy.php('2 + 2');
 *          cy.php('App\\User::count()');
 */
Cypress.Commands.add('php', (command) => {
    return cy
        .csrfToken()
        .then((token) => {
            return cy.request({
                method: 'POST',
                url: '/__cypress__/run-php',
                body: { command: command, _token: token },
                log: false,
            });
        })
        .then((response) => {
            Cypress.log({
                name: 'php',
                message: command,
                consoleProps: () => ({ result: response.body.result }),
            });
        })
        .its('body.result', { log: false });
});
