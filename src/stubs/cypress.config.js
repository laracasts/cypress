const { defineConfig } = require("cypress");

module.exports = defineConfig({
  downloadsFolder: '%cypressPath%/downloads',
  videosFolder: '%cypressPath%/videos',
  fixturesFolder: '%cypressPath%/fixtures',
  screenshotsFolder: '%cypressPath%/screenshots',
  supportFolder: '%cypressPath%/support',

  e2e: {
    setupNodeEvents(on, config) {
      return require('./%cypressPath%/plugins/index.js')(on, config)
    },
    baseUrl: "%baseUrl%",
    supportFile: '%cypressPath%/support/e2e.js',
    specPattern: '%cypressPath%/e2e/**/*.cy.{js,jsx,ts,tsx}'
  },
});