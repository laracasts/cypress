Cypress.Commands.add('login', (attributes = {}) => {
    return cy.request('/__cypress__/login', attributes).its('body');
});

Cypress.Commands.add('logout', () => {
    return cy.request('/__cypress__/logout');
});

Cypress.Commands.add('create', (model, times = null, attributes = {}) => {
    if (typeof times === 'object') {
        attributes = times;
        times = null;
    }

    return cy
        .request(`/__cypress__/factory`, {
            attributes,
            model,
            times,
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
    return cy.request('POST', '/__cypress__/artisan', {
        command: 'db:seed',
        parameters,
    });
});
