Cypress.Commands.add("login", (attributes = {}) => {
    return cy.request('/__cypress__/login', attributes).its('body');
});

Cypress.Commands.add("logout", () => {
    return cy.request('/__cypress__/logout');
});

Cypress.Commands.add("create", (model, times = 1, attributes = {}) => {
    if (typeof times === 'object') {
        attributes = times;
        times = 1;
    }

    return cy.request(`/__cypress__/factory`, {
        attributes, model, times
    }).its('body');
});

Cypress.Commands.add("refreshDatabase", () => {
    return cy.request('/__cypress__/artisan', {
        command: 'migrate:fresh'
    });
});

Cypress.Commands.add("artisan", command => {
    return cy.request('/__cypress__/artisan', {
        command
    });
});


