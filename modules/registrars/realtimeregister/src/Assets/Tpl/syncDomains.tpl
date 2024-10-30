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



    <div class="rtr-progress-bar">
        <div class="progress-label">Loading...</div>
    </div>


    <div style="margin-top: 20px; text-align: center;">
        <p class="processed-domains">Processed domains: 0/0</p>
    </div>

    <div class="sync-complete">
        <hr>
        <div class="sync-complete__result" style="text-align: center; margin: 30px 0;">
            <i class="fa fa-check-circle" style="font-size: 90px; color: #449d44; display: block"></i>
            <span class="spinner__text">Expiry date sync completed!</span>
            <div style="margin-top: 13px;">
                <p><strong class="domains-synced">Total domains synced: 0</strong></p>
                <p class="domains-changed">Domains changed: 0</p>
                <p class="domains-skipped">Domains skipped: 0</p>
            </div>
        </div>
    </div>


    <div class="modal-footer">
        <button class="sync-button btn btn-success" type="button" onclick="syncDomains()">
            Sync expiry dates to WHMCS
        </button>
    </div>
</div>

<script type="text/javascript">
    const fields = {$fieldsJSON}
    const domains = getDomains();
    const CHUNK_SIZE = 100;
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
                processed += chunk.length;
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
                    executeChunk(i + chunk.length);
                }
            },
            "json"
        ).fail(
            function (e) {
            }
        );
    }
</script>
