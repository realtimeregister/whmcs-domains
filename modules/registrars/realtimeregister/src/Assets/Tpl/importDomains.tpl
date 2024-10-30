<div class="step-one">
    <p>
        This wizard will import domains from your Realtime Register account into WHMCS.
        Domain attributes like expiry date and status will be copied automatically. The importer will match up domain
        registrant data with existing clients, or create new clients if no match is found.
    </p>

    <div class="stepwizard-row">
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary btn-current">1</button>
            <span>Setup</span>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary" disabled>2</button>
            <span>Confirm</span>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary" disabled>3</button>
            <span>Import</span>
        </div>
    </div>

    {if !empty($flashMessages)}
        {foreach from=$flashMessages item=flash}
            <div class="alert {$flash.alertClass}">
                <strong>{$flash.status}</strong> {$flash.message}
            </div>
        {/foreach}
    {/if}

    <div class="rtr-import">
        <div class="step-one">
                <h2>Setup</h2>
                <form class="step-one-form">
                    <fieldset class="form-group">
                        <label for="domainselection_all">Domain import method *</label>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input onchange="setDomainSelection('all')"
                                       class="form-check-input"
                                       type="radio"
                                       id="domainselection_all"
                                       value="all"
                                       name="domainnameSelection"
                                       {if $fields['domainSelectionMethod'] == 'all'}checked{/if}
                                >
                                Import all domains from realtimeregister
                            </label>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input onchange="setDomainSelection('list')"
                                       class="form-check-input"
                                       type="radio"
                                       id="domainselection_list"
                                       value="list"
                                       name="domainnameSelection"
                                       {if $fields['domainSelectionMethod'] == 'list'}checked{/if}
                                >
                                Import a list of domain names
                            </label>
                        </div>

                        <small class="form-text text-muted">Please choose whether you want to import all domains from
                            your Realtime Register account, or specify a list of domains you would like to
                            import.</small>
                    </fieldset>

                    <fieldset class="form-group domain-selection-list">
                        <textarea class="form-control" rows="4" cols="50" name="domainselection_list"></textarea>
                    </fieldset>

                    <ul class="failed-domains"></ul>

                    {if !empty($fields['nonActiveTlds'])}
                        <ul class="warning_domains">
                            <li>
                                In order to use the import functionality correctly, please activate the TLDs below:
                            </li>
                            {foreach from=$fields['nonActiveTlds']|@array_unique item=tld}
                                <li>
                                    <span>{$tld}</span> is not an active TLD.
                                </li>
                            {/foreach}
                            <li>
                                <span>{$fields['nonActiveTlds']|@count}</span> domains will be skipped.
                            </li>
                        </ul>
                    {/if}

                    <hr>

                    <fieldset class="form-group">
                        <label for="brandselection_default">Client import method *</label>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input onchange="setBrandSelection('contactAsClients')"
                                       class="form-check-input"
                                       type="radio"
                                       id="brandselection_default"
                                       value="contactAsClients"
                                       name="brandSelection"
                                       checked>
                                Import RTR contacts as clients.
                            </label>
                        </div>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" id="allBrands" value="all"
                                       name="brandSelection" onchange="setBrandSelection('all')">
                                Import all brands as clients.
                            </label>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" id="brandselection_list" value="list"
                                       name="brandSelection" onchange="setBrandSelection('list')">
                                Import a list of brands as clients (brands that are not selected will be imported the
                                default way; contacts will be clients).
                            </label>
                        </div>

                        <small class="form-text text-muted">
                            Please choose whether you want to import all contacts as clients, all brands from your
                            Realtime Register account, or specify a list of brands you would like to import.
                            <span style="color: orange;">(Depending on your WHMCS license there may be a client limit.)</span>
                        </small>
                    </fieldset>

                    <div class="brand-selection-list">
                        <a href="#" onclick="selectAllBrands(true)" title="Select all">Select all</a> /
                        <a href="#" onclick="selectAllBrands(false)" title="Deselect all">
                            Deselect all
                        </a>
                        <fieldset>
                            {if !empty($fields['allBrands'])}
                                <ul class="brand_overview" style="margin-top: 0;">
                                    {foreach from=$fields['allBrands'] item=brand}
                                        <li>
                                            <input class="brand-handle"
                                                   type="checkbox"
                                                   name="brandSelectionList[]"
                                                   id="{$brand.handle}"
                                                   value="{$brand.handle}"
                                                   {if in_array($brand.handle, $fields['brandSelectionList'])}checked{/if}
                                            >
                                            <label style="font-weight: normal;"
                                                   for="{$brand.handle}">{$brand.organization} - {$brand.email} <small
                                                        class="d-none d-lg-block"
                                                        style="color: #8d8d8d;">{$brand.handle}</small></label>
                                        </li>
                                    {/foreach}
                                </ul>
                            {else}
                                <div style="margin: 15px 0;">Loading...</div>
                            {/if}
                        </fieldset>
                    </div>

                    <hr>

                    <fieldset class="form-group">
                        {if !empty($fields['gateways'])}
                            <label for="paymentMethod">Preferred payment method:</label>
                            <select id="paymentMethod" class="form-control" name="paymentmethod">
                                {foreach from=$fields['gateways'] item=gateway}
                                    <option value="{$gateway['gateway']}">{$gateway['value']}</option>
                                {/foreach}
                            </select>
                            <small class="form-text text-muted">Select preferred payment method that you want to use
                                within WHMCS.</small>
                        {else}
                            <strong style="color: red;">In order to use the import functionality, please set up a
                                payment gateway</strong>
                        {/if}
                    </fieldset>
                    <div class="modal-footer">
                        <button class="btn btn-success" type="submit">Next step</button>
                    </div>
                </form>
            </div>
    </div>
</div>

<script type='text/javascript'>
    const fields = JSON.parse('{$fieldsJSON}')
    let brandSelectionMethod = fields.brandSelectionMethod;
    let domainSelectionMethod = fields.domainSelectionMethod;
    const allBrands = fields.allBrands;
    const allDomains = fields.allDomains;
    const nonActiveTlds = fields.nonActiveTlds;

    function getTld(domain) {
        const domainsParts = domain.split(".");

        if (domainsParts.length === 1) {
            return domainsParts;
        }
        domainsParts.shift();
        return domainsParts.join('.');
    }

    function availableDomain(domain) {
        return !nonActiveTlds.includes(getTld(domain));
    }

    function selectAllBrands(checkAll) {
        $('.brand_overview li input')
            .each((_, checkbox) => {
                checkbox.checked = checkAll;
            });
        return false;
    }

    function setBrandSelection(type) {
        brandSelectionMethod = type;
        if (type === 'list') {
            $('.brand-selection-list').show();
        } else {
            $('.brand-selection-list').hide();
        }
    }

    function setDomainSelection(type) {
        domainSelectionMethod = type;
        if (type === 'list') {
            $('.domain-selection-list').show();
            $('.warning_domains').hide();
        } else {
            $('.domain-selection-list').hide();
            $('.warning_domains').show();
        }
    }

    function getSelectedDomains() {
        if (domainSelectionMethod === 'all') {
            return allDomains.filter(availableDomain);
        }
        return ($('.domain-selection-list textarea').val()?.split('\n') || [])
    }

    function getSelectedBrands() {
        switch (brandSelectionMethod) {
            case 'all':
                return allBrands.map(brand => brand.handle);
            case 'list': {
                return $('.brand-selection-list .brand-handle:checked')
                    .map((_, elem) => elem.value)
                    .get()
            }
            default:
                return [];
        }
    }

    function validateDomains() {
        const selectedDomains = getSelectedDomains();
        const errors = [
            ...selectedDomains
                .filter(domain => !allDomains.includes(domain))
                .map(domain => domain + " is unknown to RealtimeRegister"),
            ...selectedDomains
                .filter(domain => nonActiveTlds.includes(getTld(domain)))
                .map(domain => domain + " - '." + getTld(domain) + "' is not an active tld")
        ];

        if (errors.length) {
            $('.failed-domains').html(errors.map(error => "<li>" + error + "</li>").join("\n"));
            return false;
        }
        return true;
    }


    $(function () {
        if (domainSelectionMethod === 'list') {
            $('.domain-selection-list textarea').val((fields.selectedDomains || []).join("\n"));
            $('.warning_domains').hide();
        } else {
            $('.domain-selection-list').hide();
        }

        if (brandSelectionMethod === 'list') {
            $('.brand-selection-list').show();
        } else {
            $('.brand-selection-list').hide();
        }

        $('.step-one-form').on("submit", event => {
            event.preventDefault();
            if (domainSelectionMethod === 'list' && !validateDomains()) {
                return;
            }

            const contentArea = $('.modal-body');
            $.post(
                window.location.href,
                {
                    action: 'importWizard',
                    module: 'realtimeregister',
                    step: 2,
                    fields: {
                        paymentMethod: $('#paymentMethod').val(),
                        allBrands,
                        allDomains,
                        nonActiveTlds,
                        brandSelectionMethod,
                        domainSelectionMethod,
                        selectedDomains: getSelectedDomains(),
                        selectedBrands: getSelectedBrands()
                    }
                },
                function (response) {
                    contentArea.html(response)
                    window.scrollTo(0, 0);
                },
                "html"
            ).fail(
                function (e) {
                }
            );
        });
    });
</script>

