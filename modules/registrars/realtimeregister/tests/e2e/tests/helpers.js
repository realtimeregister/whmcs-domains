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

export async function orderDomain(page, dnsManagement) {
    // order a domain
    await page.goto('/cart.php?a=add&domain=register')

    // We need to generate a unique domainname...
    const today = new Date()
    const date = today.getFullYear() + '' + ('0' + (today.getMonth() + 1)).slice(-2) + '' + ('0' + today.getDate()).slice(-2) +
        't' + ('0' + today.getHours()).slice(-2) + '' + ('0' + today.getMinutes()).slice(-2) + '' + ('0' + today.getSeconds()).slice(-2)
    const domain = 'whmcs' + date + '.com'

    await page.goto('cart.php?a=add&domain=register&domains[]=' + domain + '&domainsregperiod[' + domain + ']=1')

    if (dnsManagement === true) {
        // Check the checkbox
        await page.getByText('DNS Management').check()
    }

    await page.getByRole('button', {name: /^Continue/ }).click()
    await page.waitForURL('cart.php?a=view')
    await page.locator('#checkout').click()
    await page.waitForURL('cart.php?a=checkout&e=false')
    await page.getByRole('button', {name: /^Complete Order/ }).click()

    // here we are
    await page.waitForURL('cart.php?a=complete')
    await expect(page.getByText(/Your Order Number is/)).toBeVisible()
}