import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  testMatch: '*.spec.mjs',
  workers: 1,
  fullyParallel: false,
  timeout: 90000,
  retries: 0,
  reporter: [['list'], ['json', { outputFile: 'review/test-results.json' }]],
  use: {
    baseURL: 'http://localhost:8888',
    browserName: 'chromium',
    ...devices['Desktop Chrome'],
  },
});
