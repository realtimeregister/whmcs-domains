var statusMapping = {};

if(adacLang.status != undefined) {
    statusMapping = adacLang.status;
} else {
    statusMapping[0] = 'Checking...';
    statusMapping[1] = 'available';
    statusMapping[2] = 'taken';
    statusMapping[3] = 'invalid';
    statusMapping[4] = 'No response';
    statusMapping[5] = 'unknown';
}

var registerLang = 'Register';
if (adacLang.register != undefined) {
    registerLang = adacLang.register;
}

var transferLang = 'Transfer';
if (adacLang.transfer != undefined) {
    transferLang = adacLang.transfer;
}

var premiumLang = 'Premium';
if (adacLang.premium != undefined) {
    premiumLang = adacLang.premium;
}

var checkoutLang = 'Checkout';
if (adacLang.checkout != undefined) {
    checkoutLang = adacLang.checkout;
}

var suggestionLang = 'Need suggestions? You might also like:';
if (adacLang.suggestions != undefined) {
    suggestionLang = adacLang.suggestions;
}

var premiumNotSupportedLang = 'Premium domains are not supported';
if (adacLang.premium_not_supported != undefined) {
    premiumNotSupportedLang = adacLang.premium_not_supported;
}

var statusClass = {};
statusClass[0] = 'label-default';
statusClass[1] = 'label-success';
statusClass[2] = 'label-warning';
statusClass[3] = 'label-danger';
statusClass[4] = 'label-default';
statusClass[5] = 'label-default';

function ready(fn) {
    if (document.readyState != 'loading') {
        fn();
    }
    else if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', fn);
    }
    else {
        document.attachEvent('onreadystatechange', function () {
            if (document.readyState != 'loading') {
                fn();
            }
        });
    }
}

function getCheckedBoxes(checkboxName) {
    var checkboxes = document.getElementsByName(checkboxName);
    var checkboxesChecked = [];

    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            checkboxesChecked.push(checkboxes[i]);
        }
    }

    return checkboxesChecked.length > 0 ? checkboxesChecked : null;
}

var init = function () {
    adac.ensureUUID();

    var setupFallbackConnection = function () {
        var xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function () {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                if (xhr.responseText) {
                    if (adac.debug) {
                        console.log('non-empty xhr response!');
                    }
                    var dataArray = JSON.parse(xhr.responseText);

                    dataArray.forEach(function (data) {
                        adac['action_' + data.action](data.data);
                    });
                }

                if (xhr.status == 200) {
                    adac.pollServer();
                }
                else {
                    setTimeout(function () {
                        adac.pollServer();
                    }, adac.RECONNECT_TIME);
                }
            }
        };

        adac.inputElement.onpaste = function (event) {
            setTimeout(function () { adac.processInput.call(adac.inputElement, event, adac.inputElement.value); }, 0);
        };

        adac.pollServer = function (xhr_type, command) {
            xhr_type = xhr_type || 'GET';
            command = command || null;

            xhr.open(xhr_type, adac.XHR_URL + '?session_id=' + localStorage.getItem('sessionId'), true);

            if (xhr_type === 'POST') {
                xhr.setRequestHeader("Content-Type", "application/json");
            }

            xhr.send(command);
        };

        adac.sendCommand = function (command) {
            adac.pollServer('POST', JSON.stringify(command));
        };

        adac.initInputListener();
    };

    if (!window.WebSocket) {
        if (adac.debug) {
            console.log('No websocket support, using fallback');
        }
        setupFallbackConnection();
    } else {
        var setupWebsocketConnection = function () {
            adac.sendCommand = function (command) {
                if (adac.connection.readyState === 1) {
                    adac.connection.send(JSON.stringify(command));
                }
                else {
                    setTimeout(function () {
                        adac.sendCommand(command);
                    }, 100);
                }
            };

            adac.connection = new WebSocket(adac.WEBSOCKET_URL + '?session_id=' + localStorage.getItem('sessionId'));

            adac.connection.onopen = function () {
                adac.initInputListener();
            };

            adac.connection.onmessage = function (message) {
                var data = JSON.parse(message.data);
                adac['action_' + data.action](data.data);
            };

            adac.connection.onclose = function (event) {
                if (adac.debug) {
                    console.log('ws connection closed!');
                }
                if (event.code == 1006 && !event.wasClean) {
                    if (adac.debug) {
                        console.log('ws failed, using fallback');
                    }
                    setupFallbackConnection();
                } else {
                    setTimeout(function () {
                        if (adac.debug) {
                            console.log('ws connection retrying...');
                        }
                        setupWebsocketConnection();
                    }, adac.RECONNECT_TIME);
                }
            };
        };

        setupWebsocketConnection();
    }
};

var adac = {
    WEBSOCKET_URL: 'wss://adac.api.yoursrs.com/ws',
    XHR_URL: 'https://adac.api.yoursrs.com/ajax',
    RECONNECT_TIME: 3000,
    DEBOUNCE_TIME: 500,

    DOMAIN_STATUS_WAITING: 0,
    DOMAIN_STATUS_AVAILABLE: 1,
    DOMAIN_STATUS_TAKEN: 2,
    DOMAIN_STATUS_INVALID: 3,
    DOMAIN_STATUS_ERROR: 4,
    DOMAIN_STATUS_UNKNOWN: 5,

    debounce: function (fn, delay) {
        var timer = null;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(context, args);
            }, delay);
        };
    },

    initialize: function (API_KEY, user_config) {
        var config = {
            PRIORITY_LIST_TOKEN: null,
            inputElement: document.getElementById('adac-js-domain-input'),
            resultsElement: document.getElementById('adac-js-domain-results'),
            suggestionElement: document.getElementById('adac-js-suggestions'),
            categoriesElement: document.getElementById('adac-js-categories'),
            debug: false
        };

        if (ote != undefined && ote == 'on') {
            adac.WEBSOCKET_URL = 'wss://adac.api.yoursrs-ote.com/ws';
            adac.XHR_URL = 'wss://adac.api.yoursrs-ote.com/ws';
        }

        for (var attr in user_config) {
            config[attr] = user_config[attr];
        }

        adac.CUSTOMER_API_KEY = API_KEY;
        adac.PRIORITY_LIST_TOKEN = config.PRIORITY_LIST_TOKEN;
        adac.inputElement = config.inputElement;
        adac.resultsElement = config.resultsElement;
        adac.suggestionElement = config.suggestionElement;
        adac.categoriesElement = config.categoriesElement;
        adac.debug = config.debug;

        ready(function() {
            init();
            adac.fetch_categories();

            $(document).ready(function () {
                if (adac.inputElement.value) {
                    adac.processInput.call(adac.inputElement, null, adac.inputElement.value);
                }
            });
        });
    },

    fetch_categories: function () {
        var command = {api_key: adac.CUSTOMER_API_KEY, action: 'categories', data: ''};
        adac.sendCommand(command);
    },

    initInputListener: function () {
        adac.inputElement.onkeyup = adac.debounce(function (event) {
            var ignoreKeys = [9, 16, 17, 18, 20, 37, 38, 39, 40, 91, 92, 93];
            if (ignoreKeys.indexOf(event.which) > -1) {
                return false;
            }

            adac.preInput(this.value);
            adac.processInput.call(this, event, this.value);
        }, adac.DEBOUNCE_TIME);

        if (adac.categoriesElement) {
            adac.categoriesElement.onchange = function (event) {
                adac.preInput(adac.inputElement.value);
                adac.processInput.call(adac.inputElement, event, adac.inputElement.value);
            }
        }
    },

    preInput: function (value) {
    },

    processInput: function (event, value) {
        if (adac.resultsElement) {
            while (adac.resultsElement.firstChild) {
                adac.resultsElement.removeChild(adac.resultsElement.firstChild);
            }
        }

        if (adac.suggestionElement) {
            while (adac.suggestionElement.firstChild) {
                adac.suggestionElement.removeChild(adac.suggestionElement.firstChild);
            }
        }

        if (value !== '') {
            if (adac.debug) {
                console.log('Input given: ' + value);
            }

            var categories = adac.getSelectedCategories();

            var command = {
                api_key: adac.CUSTOMER_API_KEY,
                action: 'input',
                data: {priority_list_token: adac.PRIORITY_LIST_TOKEN, input: value, categories: categories}
            };

            adac.sendCommand(command);

        }

        return false;
    },

    action_domain_status: function (domainResult) {
        if (adac.debug) {
            console.log("action_domain_status() progress: ", domainResult);
        }

        adac.addDomainResult(domainResult);
    },

    action_suggestion: function (domainResult) {
        if (adac.debug) {
            console.log("action_suggestion() progress: ", domainResult);
        }
        var target = adac.suggestionElement;
        var h3 = $(target).find('h3');

        if (!h3.length) {
            target.style.backgroundColor = '#EDEEEF';

            var h3 = $('<h3/>')
                .css('padding', '30px 15px 15px 15px')
                .text(suggestionLang)
                .appendTo(target);
        }


        adac.addSuggestion(domainResult);
    },

    action_categories: function (categories) {
        if (adac.debug) {
            console.log("action_categories(): ", categories);
        }
        adac.addCategories(categories);
    },

    action_error: function (error) {
        if (adac.debug) {
            console.error("action_error(): ", error);
        }
        adac.showError(error);
    },

    addDomainResult: function (domainResult) {
        var div = document.getElementById('domain-result-' + domainResult.domain_name);

        if (!div) {
            div = document.createElement('div');

            var domainName = document.createElement('div');
            domainName.id = 'domain-name-' + domainResult.domain_name;
            div.appendChild(domainHtml(domainName, domainResult));

            div.id = 'domain-result-' + domainResult.domain_name;
            div.className = 'domain-option domain_status_' + domainResult.status;
            adac.resultsElement.appendChild(div);
        }
        else {
            div.className = 'domain-option domain_status_' + domainResult.status;

            // Premium label
            if(domainResult.price != undefined){
                document.getElementById('domain-name-' + domainResult.domain_name).innerHTML += ' <span class="label label-primary">' + premiumLang + '</span>';
            }
        }

        var status = document.getElementById('status-' + domainResult.domain_name);
        if (!status) {
            // Status
            div.appendChild(statusHtml(document.createElement('div'), domainResult));
        } else {
            status.innerHTML = '<span class="label ' + statusClass[domainResult.status] + '">' + statusMapping[domainResult.status] + '</span>';
        }


        var price = document.getElementById('price-' + domainResult.domain_name);

        if (!price) {
            var price = document.createElement('div');
            price.id = "price-" + domainResult.domain_name;
            div.appendChild(price);
        } else {
            price = priceHtml(price, domainResult, status);
        }

        var orderBtn = document.getElementById('order-' + domainResult.domain_name);
        var suffixWithoutDot = domainResult.suffix.split('.').join("");

        if (!orderBtn) {
            var orderBtn = document.createElement('div');
            orderBtn.id = "order-" + domainResult.domain_name;
            orderBtn.className = 'text-center col-xs-12 col-sm-4 col-md-3 col-lg-2';
            div.appendChild(orderBtn);
        } else {
            if(tldPrices['' + suffixWithoutDot + ''] && tldPrices['' + suffixWithoutDot + ''].domainregister != undefined) {
                if(domainResult.status == 1) {
                    var registerBtnClass = 'adac-register-domain btn btn-success';
                    if (domainResult.price != undefined) {
                        registerBtnClass += ' disabled';
                    }

                    if (cartDomains != undefined && cartDomains.indexOf(domainResult.domain_name) > -1) {
                        orderBtn.innerHTML = '<a href="cart.php?a=confdomains" class="checkout ' + registerBtnClass + '" domain="' + domainResult.domain_name + '"><i class="glyphicon glyphicon-shopping-cart"></i> ' + checkoutLang + '</a>';
                    } else {
                        var years = 1;
                        if (tldPrices['' + suffixWithoutDot + ''].interval && tldPrices['' + suffixWithoutDot + ''].interval > 12) {
                            years = tldPrices['' + suffixWithoutDot + ''].interval / 12;
                        }
                        orderBtn.innerHTML = '<a onclick="clickRegister(event, \'' + domainResult.domain_name + '\')" href="cart.php?a=add&domain=register&domains[]=' + domainResult.domain_name + '&domainsregperiod[' + domainResult.domain_name + ']=' + years + '" class="' + registerBtnClass + '" domain="' + domainResult.domain_name + '"><i class="fa fas fa-plus"></i> ' + registerLang + '</a>';
                    }
                } else if(domainResult.status == 2) {
                    orderBtn.innerHTML = '<a class="adac-transfer-domain btn btn-primary transfer" href="cart.php?a=add&domain=transfer&query=' + domainResult.domain_name + '"><i class="fa fas fa-share fa-fw"></i> ' + transferLang + '</a>';
                }
            }
        }

    },

    addSuggestion: function (domainResult) {
        if (! adac.suggestionElement) {
            if (adac.debug) {
                console.error('Suggestion container not found. Please check the `adac.suggestionElement` setting.');
            }
        } else {
            var suffixWithoutDot = domainResult.suffix.split('.').join("");

            if(tldPrices['' + suffixWithoutDot + ''] != undefined) {
                var div = document.createElement('div');
                div.className = 'suggestion suggestion_' + domainResult.status;

                domainName = document.createElement('div');
                div.appendChild(domainHtml(domainName, domainResult));

                adac.suggestionElement.appendChild(div);

                // Status
                div.appendChild(statusHtml(document.createElement('div'), domainResult));

                // Price
                var price = document.createElement('div');
                price.id = "price-" + domainResult.domain_name;
                price = priceHtml(price, domainResult, status);
                div.appendChild(price);

                if(domainResult.status == 1 && tldPrices['' + suffixWithoutDot + ''] && tldPrices['' + suffixWithoutDot + ''].domainregister != undefined) {
                    var orderBtn = document.createElement('div');
                    orderBtn.id = "order-" + domainResult.domain_name;

                    if (cartDomains != undefined && cartDomains.indexOf(domainResult.domain_name) > -1) {
                        orderBtn.innerHTML = '<a href="cart.php?a=confdomains" class="checkout adac-register-domain btn btn-success" domain="' + domainResult.domain_name + '"><i class="glyphicon glyphicon-shopping-cart"></i> ' + checkoutLang + '</a>';
                    } else {
                        orderBtn.innerHTML = '<a onclick="clickRegister(event, \'' + domainResult.domain_name + '\')" href="cart.php?a=add&domain=register&domains[]=' + domainResult.domain_name + '&domainsregperiod[' + domainResult.domain_name + ']=1" class="adac-register-domain btn btn-success" domain="' + domainResult.domain_name + '"><i class="fa fas fa-plus"></i> ' + registerLang + '</a>';
                    }
                    orderBtn.className = 'text-center col-xs-12 col-sm-4 col-md-3 col-lg-2';
                    div.appendChild(orderBtn);
                }
            }
        }
    },

    addCategories: function (categories) {
        if (! adac.categoriesElement) {
            if (adac.debug) {
                console.error('Categories container not found. Please check the `adac.categoriesElement` setting.');
            }
        } else {
            categories.forEach(function (category, _index, _array) {
                var id = category[0],
                    name = category[1],
                    checkbox = document.createElement('input');

                checkbox.type = 'checkbox';
                checkbox.name = 'adac-js-categories';
                checkbox.value = id;
                checkbox.id = id;

                var label = document.createElement('label');
                label.htmlFor = id;
                label.appendChild(document.createTextNode(name));

                adac.categoriesElement.appendChild(checkbox);
                adac.categoriesElement.appendChild(label);
            });
        }
    },

    showError: function(error) {
        var p = document.createElement('p');
        p.appendChild(document.createTextNode('Error: ' + error));
        adac.resultsElement.appendChild(p);
    },

    getSelectedCategories: function () {
        var checkedBoxes = getCheckedBoxes('adac-js-categories');

        if (checkedBoxes) {
            return checkedBoxes.map(function (element) {
                return parseInt(element.value);
            });
        }

        return null;
    },

    ensureUUID: function() {
        if (localStorage.getItem('sessionId') === null) {
            localStorage.setItem('sessionId', 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {var r = Math.random()*16|0,v=c=='x'?r:r&0x3|0x8;return v.toString(16);}));
        }
    }
};

function statusHtml(status, domainResult)
{
    status.id = "status-" + domainResult.domain_name;
    status.className = 'col-sm-3 col-md-2 col-xs-12 hidden-sm hidden-xs';
    status.innerHTML = '<span class="label ' + statusClass[domainResult.status] + '">' + statusMapping[domainResult.status] + '</span>';

    return status;
}

function domainHtml(domainName, domainResult)
{
    var suffixWithoutDot = domainResult.suffix.split('.').join("");
    var domainWithoutExtension = domainResult.domain_name.split('.');

    domainName.className = 'domain col-xs-12 col-sm-5 text-xs-center';
    domainName.innerHTML = '<span class="domain">' + domainWithoutExtension[0] + '</span><span class="extension">.' + domainResult.suffix + '</span>';

    if (tldPrices['' + suffixWithoutDot + ''] != undefined &&
        tldPrices['' + suffixWithoutDot + ''].group != undefined &&
        tldPrices['' + suffixWithoutDot + ''].group != 'none' &&
        tldPrices['' + suffixWithoutDot + ''].group != ''
    ) {
        var group = tldPrices['' + suffixWithoutDot + ''].group;
        var labelClass = 'label-warning';
        if (group == 'new') {
            labelClass = 'label-success';
        } else if (group == 'hot') {
            labelClass = 'label-danger';
        }

        domainName.innerHTML += ' <span class="label ' + labelClass + '">' + tldPrices['' + suffixWithoutDot + ''].group_title + '!</span>';
    }

    // Premium label
    if (domainResult.price != undefined) {
        domainName.innerHTML = '<span class="domain">' + domainWithoutExtension[0] + '</span><span class="extension">.' + domainResult.suffix + '</span> <span class="label label-primary">' + premiumLang + '</span>';
    }

    return domainName;
}

function priceHtml(price, domainResult, status)
{
    if(domainResult.status == 1 || domainResult.status == 2) {

        var suffixWithoutDot = domainResult.suffix.split('.').join("");

        if(domainResult.price != undefined) {
            if (!premiumDomains) {
                status.innerHTML = '<span class="label label label-warning">Taken</span>';
                price.innerHTML = premiumNotSupportedLang;
            } else {
                // Do ajax call to get premium price
                $.post(document.location.href, {
                    adacpremium: domainResult.domain_name,
                    adacpremiumprice: domainResult.price,
                    adacpremiumcurrency: domainResult.currency
                })
                    .done(function (data) {
                        var json = $.parseJSON(data);

                        if (json.error != undefined) {
                            price.innerHTML = '';
                            status.innerHTML = '<span class="label label-danger">error</span>';
                            $('.adac-register-domain[domain="' + domainResult.domain_name + '"]').replaceWith(
                                json.error);
                        } else {
                            // Set register to enable
                            price.innerHTML = json.price + ' ' + json.currency['suffix'];
                            $('.adac-register-domain[domain="' + domainResult.domain_name + '"]').removeClass(
                                'disabled');
                        }
                    }, "json");
                price.innerHTML = '<i class=\'fa fa-spinner fa-spin \'></i>';
            }
        } else if(tldPrices['' + suffixWithoutDot + ''] && tldPrices['' + suffixWithoutDot + ''].domainregister != undefined) {
            price.innerHTML = tldPrices['' + suffixWithoutDot + ''].domainregister;
        } else {
            price.innerHTML = 'N/A';
        }
    }
    price.className = 'price col-xs-12 col-sm-3 col-md-2 col-lg-3 text-xs-center';

    return price;
}

function clickRegister(event, domain)
{
    var button = $('.adac-register-domain[domain="' + domain + '"]');

    if (!button.hasClass('checkout')) {
        event.preventDefault();
        button.html("<i class='fa fa-spinner fa-spin '></i>");

        $.post(window.location.pathname, {a: "addToCart", domain: domain, token: csrfToken, whois: 0})
            .done(function (data) {
                button.attr("href", "cart.php?a=confdomains");
                button.html('<i class="glyphicon glyphicon-shopping-cart"></i> ' + checkoutLang);
                button.addClass('checkout');
            });
    }
}
