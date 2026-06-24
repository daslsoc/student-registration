import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        environment: 'jsdom',
        include: ['tests/js/**/*.test.js'],
        setupFiles: ['tests/js/setup.js'],
        globals: false,
        coverage: {
            provider: 'v8',
            reporter: ['text', 'html', 'lcov'],
            reportsDirectory: 'tests/js-coverage',
            include: ['resources/js/**/*.js'],
            exclude: ['resources/js/bootstrap.js', 'resources/js/app.js'],
        },
    },
});
