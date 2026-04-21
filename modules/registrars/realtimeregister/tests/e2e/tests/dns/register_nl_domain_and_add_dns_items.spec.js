import {test} from '@playwright/test';
import {addDnsItem, generateDomainName, loginAsAdmin, loginAsUser, orderDomain, setDnsServers} from "../helpers"


// TODO delete used domainnames after tests
test ('Set dns setup to "none" and own dnsservers, then order domain', async ({ page }) => {
    await loginAsAdmin(page)
    await setDnsServers(page, [process.env.OWN_NAMESERVER_1, process.env.OWN_NAMESERVER_2], 'none', false);
    // logout
    await page.goto('/admin/logout.php')

    await loginAsUser(page)

    let domain = generateDomainName('nl')
    await orderDomain(page, domain, false)
    // TODO check for absence of dns management option in menu
});

test ('Set dns setup to "none" and order domain', async ({ page }) => {
    await loginAsAdmin(page)
    await setDnsServers(page, [process.env.OWN_NAMESERVER_1, process.env.OWN_NAMESERVER_2], 'none', false);
    // logout
    await page.goto('/admin/logout.php')

    await loginAsUser(page)

    let domain = generateDomainName('nl')
    await orderDomain(page, domain, false)
    // TODO check for absence of dns management option in menu
});

test ('Set dns setup to "BASIC" and order domain', async ({ page }) => {
    await loginAsAdmin(page)
    await setDnsServers(page, [process.env.BASIC_NAMESERVER_1, process.env.BASIC_NAMESERVER_2], 'BASIC', false);
    // logout
    await page.goto('/admin/logout.php')

    await loginAsUser(page)

    let domain = generateDomainName('nl')
    await orderDomain(page, domain, true)
    await addDnsItem(page, domain)
});

test ('Set dns setup to "PREMIUM" and order domain', async ({ page }) => {
    await loginAsAdmin(page)
    await setDnsServers(page, [process.env.PREMIUM_NAMESERVER_1, process.env.PREMIUM_NAMESERVER_2], 'PREMIUM', false);
    // logout
    await page.goto('/admin/logout.php')

    await loginAsUser(page)
    let domain = generateDomainName('nl')

    await orderDomain(page, domain, true)
    await addDnsItem(page, domain)
});


test ('Set dns setup to "PREMIUM" with custom nameservers and order domain', async ({ page }) => {
    await loginAsAdmin(page)
    await setDnsServers(page, [process.env.PREMIUM_NAMESERVER_1, process.env.PREMIUM_NAMESERVER_2], 'PREMIUM', [process.env.VANITY_NAMESERVER_1, process.env.VANITY_NAMESERVER_2]);
    // logout
    await page.goto('/admin/logout.php')

    await loginAsUser(page)

    let domain = generateDomainName('nl')
    await orderDomain(page, domain, true)
    await addDnsItem(page, domain)
});
