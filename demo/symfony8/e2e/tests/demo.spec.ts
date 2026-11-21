import { test, expect } from '@playwright/test';

/** REQ-DEMO-013: failing fragments fall back to the unavailable template and the parent page stays healthy. */
test.describe('Fragment Kit demo', () => {
  test('healthy, forbidden and missing fragments', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.status()).toBe(200);
    await expect(page.getByTestId('demo-platform')).toBeVisible();
    await expect(page.getByTestId('demo-case-ok')).toContainText('Healthy fragment rendered OK.');
    await expect(page.getByTestId('demo-case-forbidden')).toContainText('Fragment unavailable (HTTP 403)');
    await expect(page.getByTestId('demo-case-missing')).toContainText('Fragment unavailable (HTTP 404)');
    await expect(page.getByTestId('demo-case-missing')).toContainText('Parent page continued successfully.');
  });
});
