import {expect} from "@playwright/test"

export async function loginAsUser(page) {
    // login as client
    await page.goto('/index.php?rp=/login')
    await page.getByPlaceholder('name@example.com').fill(process.env.TEST_USER)
    await page.getByPlaceholder('Password').fill(process.env.TEST_PASSWORD)
    await page.getByRole('button', {name: 'Login'}).click()
}

export async function loginAsAdmin(page) {
    // Login
    await page.goto('/admin');
    await page.getByPlaceholder("Username").fill(process.env.ADMIN_USER)
    await page.getByPlaceholder("Password").fill(process.env.ADMIN_PASSWORD)
    await page.getByRole('button', {name: 'Login'}).click()
}

export function generateDomainName()
{
    // We need to generate a unique domainname...
    let today = new Date()
    let date = today.getFullYear() + '' + ('0' + (today.getMonth() + 1)).slice(-2) + '' + ('0' + today.getDate()).slice(-2) +
        't' + ('0' + today.getHours()).slice(-2) + '' + ('0' + today.getMinutes()).slice(-2) + '' + ('0' + today.getSeconds()).slice(-2)
    return 'whmcs' + date + '.nl'
}

export async function orderDomain(page, domainName, dnsManagement) {
    // order a domain
    await page.goto('/cart.php?a=add&domain=register')

    await page.goto('cart.php?a=add&domain=register&domains[]=' + domainName + '&domainsregperiod[' + domainName + ']=1')

    if (dnsManagement === true) {
        // Check the checkbox
        await page.getByText('DNS Management').check()
    }

    await page.getByRole('button', {name: /^Continue/ }).click()
    await page.waitForURL('cart.php?a=view')
    await page.locator('#checkout').click()
    await page.waitForURL('cart.php?a=checkout&e=false')
    await page.getByRole('button', {name: /^Complete Order/ }).click()

    await page.waitForURL('cart.php?a=complete')
    await expect(page.getByText(/Your Order Number is/)).toBeVisible()
}

export async function addDnsItem(page, domainName) {
    // goto client domain page
    await page.goto('/clientarea.php?action=domains')
    await page.fill('input[type=search]', domainName)

    await page.getByRole('gridcell', { name: 'Active' }).click()
    await page.getByRole('link', { name: 'DNS Management' }).click()

    await page.fill ('input[name="dns-items[0][name]"]', 'arecord.' + domainName)
    await page.fill ('input[name="dns-items[0][content]"]', '127.0.0.1')
    await page.fill ('input[name="dns-items[0][ttl]"]', '7200')

    await page.getByRole('button', { name: 'Save' }).click()
    await expect(page.getByText('Your DNS records have been saved successfully')).toBeVisible()
}