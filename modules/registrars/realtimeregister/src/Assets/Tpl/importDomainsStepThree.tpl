<div class="step-three">

    <div class="stepwizard-row">
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary" onclick="goToStep(1)">1</button>
            <span>Setup</span>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary" onclick="goToStep(2)">2</button>
            <span>Confirm</span>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary btn-current">3</button>
            <span>Import</span>
        </div>
    </div>

    <div class="rtr-progress-bar">
        <div class="progress-label">Processing...</div>
    </div>

    <div style="margin-top: 20px; text-align: center;">
        <p class="processed-domains">Processed domains: 0</p>
    </div>

    <div class="import-complete mt-1">
        <p><strong></strong></p>
    </div>
</div>

<script type="text/javascript">
    const CHUNK_SIZE = 100;
    const fields = {$fieldsJSON};
    const selectedDomains = fields.selectedDomains;
    let processed = 0;
    let updated = 0;

    function goToStep(step) {
        const contentArea = $('.modal-body');
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
        $('.import-complete').hide();
        showProgressBar();
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
                processed += domains.length;
                updated += response.updated;
                setProgress(selectedDomains.length, processed);
                $('.processed-domains').text('Processed domains: ' + processed);
                if (processed === selectedDomains.length) {
                    progressComplete();
                    $('.import-complete').show();
                    $('.import-complete strong').text('Total domains imported: ' + updated);
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