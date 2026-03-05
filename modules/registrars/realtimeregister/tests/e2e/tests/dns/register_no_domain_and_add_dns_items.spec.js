import {test} from '@playwright/test';
import {addDnsItem, generateDomainName, loginAsAdmin, loginAsUser, orderDomain, setDnsServers} from "../helpers"

// TODO delete used domainnames after tests
test ('Set dns setup to BASIC and order domain', async ({ page }) => {
    await loginAsAdmin(page)
    await setDnsServers(page, [process.env.BASIC_NAMESERVER_1, process.env.BASIC_NAMESERVER_2], 'BASIC', false);
    // logout
    await page.goto('/admin/logout.php')

    await loginAsUser(page)

    let domain = generateDomainName('no')
    await orderDomain(page, domain, true)
    await addDnsItem(page, domain)
});