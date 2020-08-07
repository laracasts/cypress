Cypress.Commands.add('login', (attributes = {}) => {
    return cy.csrfToken()
        .then(token => {
            return cy.request({
                method: 'POST',
                url: '/__cypress__/login',
                body: {
                    attributes,
                    _token: token,
                },
                log: false
            })
        })
        .then(({body}) => {
            Cypress.log({
                name: 'login',
                message: attributes,
                consoleProps: () => {
                    return {
                        user: body
                    };
                }
            });
        }).its('body', {log: false});
});

Cypress.Commands.add('logout', () => {
    return cy.csrfToken()
        .then(token => {
            return cy.request({
                method: 'POST',
                url: '/__cypress__/logout',
                body: {
                    _token: token,
                },
                log: false
            });
        })
        .then(() => {
            Cypress.log({
                name: 'logout',
                message: ''
            });
        });
});

Cypress.Commands.add('csrfToken', () => {
    return cy
        .request({
            method: 'GET',
            url: '/__cypress__/csrf_token',
            log: false,
        })
        .its('body', {log: false});
});

Cypress.Commands.add('create', (model, times = null, attributes = {}) => {
    if (typeof times === 'object' && times !== null) {
        attributes = times;
        times = null;
    }

    return cy
        .csrfToken()
        .then(token => {
            return cy.request({
                method: 'POST',
                url: '/__cypress__/factory',
                body: {
                    attributes,
                    model,
                    times,
                    _token: token,
                },
                log: false
            })
        })
        .then(response => {
            Cypress.log({
                name: 'create',
                message: model + (times ? `(${times} times)` : ''),
                consoleProps: () => {
                    return {
                        [model]: response.body
                    };
                }
            });
        })
        .its('body', {log: false});
});

Cypress.Commands.add('refreshDatabase', (options = {}) => {
    return cy.artisan('migrate:fresh', options);
});

Cypress.Commands.add('seed', (seederClass) => {
    return cy.artisan('db:seed', {
        '--class': seederClass,
    });
});

Cypress.Commands.add('artisan', (command, parameters = {}) => {
    Cypress.log({
        name: 'artisan',
        message: command,
        consoleProps: () => {
            return {command, parameters};
        }
    });

    return cy.csrfToken()
        .then(token => {
            return cy.request({
                method: 'POST',
                url: '/__cypress__/artisan',
                body: {
                    command: command,
                    parameters: parameters,
                    _token: token,
                },
                log: false
            })
        });
});

Cypress.Commands.add('php', (command) => {
    return cy
        .csrfToken()
        .then((token) => {
            return cy.request({
                method: 'POST',
                url: '/__cypress__/run-php',
                body: {
                    command: command,
                    _token: token,
                },
                log: false
            });
        })
        .then(response => {
            Cypress.log({
                name: 'php',
                message: command,
                consoleProps: () => {
                    return {
                        result: response.body.result
                    };
                }
            });
        })
        .its('body.result', {log: false});
});
