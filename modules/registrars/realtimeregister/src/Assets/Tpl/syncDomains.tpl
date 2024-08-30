<div>
    {if count($fields['whmcsDomains']) < count($fields['rtrDomains'])}
        <p style="color: red;">
            It looks like your WHMCS installation is out of sync with RealtimeRegister, please use the Import
            Wizard to import all domains from RealtimeRegister.
        </p>
        <p class="domain-count text-muted" style="font-weight: bold;">
            Domain count: 0
        </p>
        <p class="text-muted">
            Domains present in WHMCS: {{count($fields['whmcsDomains'])}}
        </p>
        <p class="text-muted">
            Domains present at Realtime Register: {{count($fields['rtrDomains'])}}
        </p>
    {/if}

    <button class="sync-button btn btn-success" type="button" onclick="syncDomains()">
        Sync RTR domain expiry dates to WHMCS
    </button>

    <hr>

    <div class="rtr-progress-bar">
        <div class="progress-label">Loading...</div>
    </div>


    <div style="margin-top: 20px; text-align: center;">
        <p class="processed-domains">Processed domains: 0/0</p>
    </div>

    <hr>

    <div class="sync-complete">
        <div style="text-align: center; margin: 100px 0;">
            <span class="spinner__text">Expiry date sync completed!</span>
            <i class="fa fa-check-circle" style="font-size: 90px; color: #449d44;"></i>
            <div style="margin-top: 13px;">
                <p><strong class="domains-synced">Total domains synced: 0</strong></p>
                <p class="domains-changed">Domains changed: 0</p>
                <p class="domains-skipped">Domains skipped: 0</p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    const fields = {$fieldsJSON}
    const domains = getDomains();
    const CHUNK_SIZE = 1;
    let processed = 0;
    let updated = 0;

    $(function () {
        $('.sync-complete').hide();
        $('.rtr-progress-bar').hide();
        $('.processed-domains').hide();
        $('.domain-count').text("Domain count: " + domains.length);
    })

    function syncDomains() {
        showProgressBar();
        $('.sync-button').prop('disabled', true)
        $('.processed-domains')
            .text("Processed domains: 0/" + domains.length)
            .show();
        executeChunk(0);
    }

    function getDomains() {
        return fields.rtrDomains.filter(domain => fields.whmcsDomains.includes(domain.domainName));
    }

    function executeChunk(i) {
        const chunk = domains.slice(i, Math.min(domains.length, i + CHUNK_SIZE));
        $.post(
            window.location.href,
            {
                action: 'syncExpiry',
                module: 'realtimeregister',
                domains: chunk
            },
            function (response) {
                processed += CHUNK_SIZE;
                updated += response.updated;
                setProgress(domains.length, processed);
                $('.processed-domains').text('Processed domains: ' + processed + "/" + domains.length);
                if (i + CHUNK_SIZE >= domains.length) {
                    $('.sync-complete').show();
                    $('.domains-synced').text("Total domains synced: " + domains.length);
                    $('.domains-changed').text("Domains changed: " + updated);
                    $('.domains-skipped').text("Domains skipped: " + (processed - updated));
                    progressComplete();
                } else {
                    executeChunk(i + CHUNK_SIZE);
                }
            },
            "json"
        ).fail(
            function (e) {
            }
        );
    }
</script>
