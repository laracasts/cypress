Cypress.Commands.add('login', (attributes = {}) => {
    return cy.csrfToken().then((token) => {
        return cy.request({
            method: 'POST',
            url: '/__cypress__/login',
            body: {
                attributes,
                _token: token,
            },
        });
    });
});

Cypress.Commands.add('logout', () => {
    return cy.csrfToken().then((token) => {
        return cy.request({
            method: 'POST',
            url: '/__cypress__/logout',
            body: {
                _token: token,
            },
        });
    });
});

Cypress.Commands.add('csrfToken', () => {
    return cy.request('GET', '/__cypress__/csrf_token').its('body');
});

Cypress.Commands.add('create', (model, times = null, attributes = {}) => {
    if (typeof times === 'object') {
        attributes = times;
        times = null;
    }

    return cy
        .csrfToken()
        .then((token) => {
            return cy.request({
                method: 'POST',
                url: '/__cypress__/factory',
                body: {
                    attributes,
                    model,
                    times,
                    _token: token,
                },
            });
        })
        .its('body');
});

Cypress.Commands.add('refreshDatabase', () => {
    return cy.artisan('migrate:fresh');
});

Cypress.Commands.add('seed', (seederClass) => {
    return cy.artisan('db:seed', {
        '--class': seederClass,
    });
});

Cypress.Commands.add('artisan', (command, parameters = {}) => {
    return cy.csrfToken().then((token) => {
        return cy.request({
            method: 'POST',
            url: '/__cypress__/artisan',
            body: {
                command: command,
                parameters: parameters,
                _token: token,
            },
        });
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
            });
        })
        .its('body.result');
});
