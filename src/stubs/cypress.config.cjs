const { defineConfig } = require('cypress')

module.exports = defineConfig({
    chromeWebSecurity: false,
    retries: 2,
    defaultCommandTimeout: 5000,
    watchForFileChanges: false,
    videosFolder: '%cypressPath%/videos',
    screenshotsFolder: '%cypressPath%/screenshots',
    fixturesFolder: '%cypressPath%/fixture',
    e2e: {
        setupNodeEvents(on, config) {
            return require('./%cypressPath%/plugins/index.js')(on, config)
        },
        baseUrl: '%baseUrl%',
        specPattern: '%cypressPath%/integration/**/*.cy.{js,jsx,ts,tsx}',
        supportFile: '%cypressPath%/support/index.js',
    },
})
