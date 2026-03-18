# WHMCS module for Realtime Register API
[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)
[![CI](https://github.com/realtimeregister/whmcs/actions/workflows/phpcs.yml/badge.svg)](https://github.com/realtimeregister/whmcs/actions/workflows/phpcs.yml)

This is our completely rebuilt WHMCS Domains module, featuring great new enhancements and available under an open-source license. We recommend using this module for all new deployments. If you are currently using our old module, please refer to [this upgrade guide](https://github.com/realtimeregister/whmcs-domains/wiki/upgrade-from-registrar-module-version-1.4.13-and-below) and deploy it to your testing environment first.

### Quick start
[Install guide](https://github.com/realtimeregister/whmcs-domains/wiki/Registrar-module-installation)

### Features
- [X] Domain registration
- [X] Domain transfer
- [X] Domain check
- [X] Domain renew/restore
- [X] IDN Domains
- [X] Premium domain support
- [X] Pricing sync with Realtime Register
- [X] Override of contact handles (either in general or per provider)
- [X] DNSSec support
- [X] Child hosts
- [X] Transfer in/out of domains
- [X] Import of portfolio
- [X] Bulk Sync
- [X] Additional fields support for all supported TLDs
- [X] DNS-management (works best if you are using our template as a base)
- [X] Transfer domains when invoice is paid via DTS, try to move the domains to Realtime Register

We also provide widgets for you to use on your admin homepage:
- Promotion overview
- Your balance
- Domainoverview
- Error Log

### Prerequisites
- Account at [realtimeregister.com](https://realtimeregister.com) with the ability to create an API key
- Access to WHMCS admin area
- A [currently supported version](https://docs.whmcs.com/about-whmcs/whmcs-development/#active-development) of WHMCS
- PHP 8+

### Releases/Downloads
Please check the latest [Releases](https://github.com/realtimeregister/whmcs-domains/releases) and Download files 

### Suggestions, Bugs, Issues, New Features
You are of course more than welcome to send us requests for new features, suggestions, issues or any possible bugs found
[via Github issues](https://github.com/realtimeregister/whmcs-domains/issues/new).

### WIKI
Check out our [WIKI](https://github.com/realtimeregister/whmcs-domains/wiki) for manuals and description of all features.

### Playwright
The Playwright tests are located in tests/e2e. They can be run by installing the test framework (`yarn install`) and running 
Playwright (`yarn playwright test`).
