<div class="step-three">

    <div class="stepwizard-row">
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary" onclick="goToStep(1)">Setup</button>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary" onclick="goToStep(2)">Confirm</button>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary disabled">Import</button>
        </div>
    </div>

    <div class="spinner">
        <span class="spinner__text">The domain importer is running. Please be patient until this process completes.</span>
        <div class="bounce1"></div>
        <div class="bounce2"></div>
        <div class="bounce3"></div>
        <span class="domain-progress-bar" style="display: block; margin-top: 10px;">
            0%
        </span>
    </div>
    <div style="margin-top: 20px; text-align: center;">
        <p class="processed-domains">Processed domains: 0</p>
    </div>
    <div class="import-complete hidden mt-1">
        <p><strong></strong></p>
    </div>
</div>

<script type="text/javascript">
    const CHUNK_SIZE = 1;
    const fields = JSON.parse('{$fieldsJSON}');
    const selectedDomains = fields.selectedDomains;
    let processed = 0;
    let updated = 0;

    function goToStep(step) {
        const contentArea = $('#contentarea');
        $.post(
            window.location.href,
            {
                action: 'importWizard',
                module: 'realtimeregister',
                step,
                fields
            },
            function (response) {
                contentArea.html(response)
                window.scrollTo(0,0);
            },
            "html"
        ).fail(
            function (e) {
            }
        );
    }

    $(function () {
        executeChunk(0);
    });

    function executeChunk(i) {
        const domains = selectedDomains.slice(i, Math.min(selectedDomains.length, i + CHUNK_SIZE));
        $.post(
            window.location.href,
            {
                action: 'importWizard',
                module: 'realtimeregister',
                step: 3,
                fields,
                domains
            },
            function (response) {
                processed += CHUNK_SIZE;
                updated += response.updated;
                $('.domain-progress-bar').each((_, elem) => {
                    elem.innerText = Math.ceil(processed / selectedDomains.length * 100) + '%'
                });
                $('.processed-domains').each((_, elem) => {
                    elem.innerText = 'Processed domains: ' + processed;
                });
                if (i + CHUNK_SIZE >= selectedDomains.length) {
                    $('.import-complete').removeClass('hidden');
                    $('.import-complete strong').each((_, elem) => {
                        elem.innerText = 'Total domains imported: ' + updated
                    })
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