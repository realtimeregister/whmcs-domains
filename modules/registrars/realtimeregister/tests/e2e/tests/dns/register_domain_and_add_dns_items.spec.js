import {test} from '@playwright/test';
import {addDnsItem, generateDomainName, loginAsAdmin, loginAsUser, orderDomain} from "../helpers"

// The tests in this file depend on a specific global WHMCS setting, so they cannot run in parallel
test.describe.configure({ mode: 'serial' });

// TODO delete used domainnames after tests
test ('Set dns setup to BASIC and order domain', async ({ page }) => {
    await loginAsAdmin(page)
    // change config of WHMCS
    await page.goto('/admin/configgeneral.php#tab=0');

    if (await page.isVisible('text="Password"')) {
    // if (page.getByRole('textbox', {name: 'Password'})) {
        await page.getByRole('textbox', {name: 'Password'}).fill(process.env.ADMIN_PASSWORD)
        await page.getByRole('button', { name: 'Confirm Password' }).click()
    }

    await page.selectOption('select[name="template"]', 'realtimeregister')
    await page.getByRole('button', {name: 'Save Changes'}).click()

    // Set default nameservers
    await page.goto('/admin/configgeneral.php#tab=4');

    if (await page.isVisible('text="Password"')) {
    // if (page.getByRole('textbox', {name: 'Password'})) {
        await page.getByRole('textbox', {name: 'Password'}).fill(process.env.ADMIN_PASSWORD)
        await page.getByRole('button', { name: 'Confirm Password' }).click()
    }

    await page.fill('input[name="ns1"]', process.env.BASIC_NAMESERVER_1)
    await page.fill('input[name="ns2"]', process.env.BASIC_NAMESERVER_2)

    await page.goto('/admin/configregistrars.php')
    await page.locator('td:nth-child(3) > .btn.btn-default').click();
    await page.selectOption('select[name="dns_support"]', 'BASIC')
    await page.fill('input[name="dns_vanity_nameserver_1"]', '')
    await page.fill('input[name="dns_vanity_nameserver_2"]', '')
    await page.getByRole('button', {name: 'save'}).click()

    // logout
    await page.goto('/admin/logout.php')

    await loginAsUser(page)

    let domain = generateDomainName()
    await orderDomain(page, domain, true)
    await addDnsItem(page, domain)
});

// test ('Set dns setup to PREMIUM and order domain', async ({ page }) => {
//     await loginAsAdmin(page)
//
//     // Set default nameservers
//     await page.goto('/admin/configgeneral.php#tab=4');
//     if (page.getByRole('textbox', {name: 'Password'})) {
//         await page.getByRole('textbox', {name: 'Password'}).fill(process.env.ADMIN_PASSWORD)
//         await page.getByRole('button', { name: 'Confirm Password' }).click()
//     }
//     await page.fill('input[name="ns1"]', process.env.PREMIUM_NAMESERVER_1)
//     await page.fill('input[name="ns2"]', process.env.PREMIUM_NAMESERVER_2)
//
//     // change config of WHMCS
//     await page.goto('/admin/configregistrars.php')
//     await page.locator('td:nth-child(3) > .btn.btn-default').click();
//     await page.selectOption('select[name="dns_support"]', 'PREMIUM')
//     await page.fill('input[name="dns_vanity_nameserver_1"]', '')
//     await page.fill('input[name="dns_vanity_nameserver_2"]', '')
//     await page.getByRole('button', {name: 'save'}).click()
//
//     // logout
//     await page.goto('/admin/logout.php')
//
//     await loginAsUser(page)
//     let domain = generateDomainName()
//
//     await orderDomain(page, domain, true)
//     await addDnsItem(page, domain)
//
//
//     // domain = generateDomainName();
//     // await orderDomain(page, domain, false)
// });


// test ('Set dns setup to PREMIUM with custom nameservers and order domain', async ({ page }) => {
//     await loginAsAdmin(page)
//
//     // Set default nameservers
//     await page.goto('/admin/configgeneral.php#tab=4');
//     if (page.getByRole('textbox', {name: 'Password'})) {
//         await page.getByRole('textbox', {name: 'Password'}).fill(process.env.ADMIN_PASSWORD)
//         await page.getByRole('button', { name: 'Confirm Password' }).click()
//     }
//
//     await page.fill('input[name="ns1"]', process.env.PREMIUM_NAMESERVER_1)
//     await page.fill('input[name="ns2"]', process.env.PREMIUM_NAMESERVER_2)
//
//     // change config of WHMCS
//     await page.goto('/admin/configregistrars.php')
//     await page.locator('td:nth-child(3) > .btn.btn-default').click();
//     await page.selectOption('select[name="dns_support"]', 'PREMIUM')
//     await page.fill('input[name="dns_vanity_nameserver_1"]', process.env.VANITY_NAMESERVER_1)
//     await page.fill('input[name="dns_vanity_nameserver_2"]', process.env.VANITY_NAMESERVER_2)
//     await page.getByRole('button', {name: 'save'}).click()
//
//     // logout
//     await page.goto('/admin/logout.php')
//
//     await loginAsUser(page)
//
//     let domain = generateDomainName()
//     await orderDomain(page, domain, true)
//     domain = generateDomainName()
//     await orderDomain(page, domain, false)
// });
