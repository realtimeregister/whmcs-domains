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

export function generateDomainName(tld = 'nl')
{
    // We need to generate a unique domainname...
    let today = new Date()
    let date = today.getFullYear() + '' + ('0' + (today.getMonth() + 1)).slice(-2) + '' + ('0' + today.getDate()).slice(-2) +
        't' + ('0' + today.getHours()).slice(-2) + '' + ('0' + today.getMinutes()).slice(-2) + '' + ('0' + today.getSeconds()).slice(-2)
    return 'whmcs' + date + '.' + tld;
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

export async function setDnsServers(page, dnsServers, typeOf, vanityNameservers){
    // change config of WHMCS
    await page.goto('/admin/configgeneral.php#tab=0');

    if (await page.isVisible('text="Password"')) {
        // if (page.getByRole('textbox', {name: 'Password'})) {
        await page.getByRole('textbox', {name: 'Password'}).fill(process.env.ADMIN_PASSWORD)
        await page.getByRole('button', { name: 'Confirm Password' }).click()
    }

    await page.selectOption('select[name="template"]', 'realtimeregister-example')
    await page.getByRole('button', {name: 'Save Changes'}).click()

    // Set default nameservers
    await page.goto('/admin/configgeneral.php#tab=4');

    if (await page.isVisible('text="Password"')) {
        // if (page.getByRole('textbox', {name: 'Password'})) {
        await page.getByRole('textbox', {name: 'Password'}).fill(process.env.ADMIN_PASSWORD)
        await page.getByRole('button', { name: 'Confirm Password' }).click()
    }

    await page.fill('input[name="ns1"]', dnsServers[0])
    await page.fill('input[name="ns2"]', dnsServers[1])

    await page.goto('/admin/configregistrars.php')
    await page.locator('td:nth-child(3) > .btn.btn-default').click();
    await page.selectOption('select[name="dns_support"]', typeOf)

    if (typeOf === 'BASIC' || typeOf === 'PREMIUM') {
        await page.fill('input[name="dns_vanity_nameserver_1"]', '')
        await page.fill('input[name="dns_vanity_nameserver_2"]', '')
    }

    if (vanityNameservers.length > 0) {
        await page.fill('input[name="dns_vanity_nameserver_1"]', vanityNameservers[0])
        await page.fill('input[name="dns_vanity_nameserver_2"]', vanityNameservers[1])
    }
    await page.getByRole('button', {name: 'save'}).click()
}