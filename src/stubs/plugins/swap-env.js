import fs from 'fs';

export default {
    activateCypressEnvFile() {
        if (fs.existsSync('.env.cypress')) {
            fs.renameSync('.env', '.env.backup');
            fs.renameSync('.env.cypress', '.env');
        }

        return null;
    },

    activateLocalEnvFile() {
        if (fs.existsSync('.env.backup')) {
            fs.renameSync('.env', '.env.cypress');
            fs.renameSync('.env.backup', '.env');
        }

        return null;
    }
};
