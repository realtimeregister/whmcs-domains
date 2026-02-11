import {test} from '@playwright/test';
import {loginAsAdmin, loginAsUser, orderDomain} from "../helpers"

test ('Set dns setup to BASIC and order domain', async ({ page }) => {
    await loginAsAdmin(page)
    // change config of Whmcs
    await page.goto('/admin/configregistrars.php')
    await page.locator('td:nth-child(3) > .btn.btn-default').click();
    await page.selectOption('select[name="dns_support"]', 'BASIC')
    await page.fill('input[name="dns_vanity_nameserver_1"]', '')
    await page.fill('input[name="dns_vanity_nameserver_2"]', '')
    await page.getByRole('button', {name: 'save'}).click()

    // logout
    await page.goto('/admin/logout.php')

    await loginAsUser(page)
    await orderDomain(page, true)
    await orderDomain(page, false)
});

test ('Set dns setup to PREMIUM and order domain', async ({ page }) => {
    await loginAsAdmin(page)
    // change config of Whmcs
    await page.goto('/admin/configregistrars.php')
    await page.locator('td:nth-child(3) > .btn.btn-default').click();
    await page.selectOption('select[name="dns_support"]', 'PREMIUM')
    await page.fill('input[name="dns_vanity_nameserver_1"]', '')
    await page.fill('input[name="dns_vanity_nameserver_2"]', '')
    await page.getByRole('button', {name: 'save'}).click()

    // logout
    await page.goto('/admin/logout.php')

    await loginAsUser(page)
    await orderDomain(page, true)
    await orderDomain(page, false)
});


test ('Set dns setup to PREMIUM with custom nameservers and order domain', async ({ page }) => {
    await loginAsAdmin(page)
    // change config of Whmcs
    await page.goto('/admin/configregistrars.php')
    await page.locator('td:nth-child(3) > .btn.btn-default').click();
    await page.selectOption('select[name="dns_support"]', 'PREMIUM')
    await page.fill('input[name="dns_vanity_nameserver_1"]', 'ns1.vanity.com')
    await page.fill('input[name="dns_vanity_nameserver_2"]', 'ns2.vanity.com')
    await page.getByRole('button', {name: 'save'}).click()

    // logout
    await page.goto('/admin/logout.php')

    await loginAsUser(page)
    await orderDomain(page, true)
    await orderDomain(page, false)
});
