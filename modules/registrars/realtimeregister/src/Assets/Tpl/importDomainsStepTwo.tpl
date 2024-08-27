<div class="step-two">

    <div class="stepwizard-row">
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary" onclick="goToStep(1)">1</button>
            <span>Setup</span>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary btn-current">2</button>
            <span>Confirm</span>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-default btn-circle btn-primary disabled">3</button>
            <span>Import</span>
        </div>
    </div>

    <form class="step-two-form">
        <h2>Confirm</h2>
        <p>
            Please confirm to continue
        </p>

        {if count($fields['selectedDomains'])}
            <div class="domains-length">
                <strong>Number of domains:</strong><br />
                {count($fields['selectedDomains'])}<br />
                <br />
            </div>
        {/if}

        {if $fields['selectedBrands'] && count($fields['selectedBrands'])}
            <div class="brands-length">
                <strong>Number of brands to clients:</strong><br />
                {count($fields['selectedBrands'])}<br />
            </div>
        {/if}

        <div class="preferred-payment">
            <strong>Preferred payment method:</strong><br />
            {$fields['paymentMethod']}
            <br />
        </div>

        <button class="btn btn-default" type="button" onclick="goToStep(1)">Previous step</button>
        <button class="btn btn-success" type="submit">Import domains</button>
    </form>

    {*            //{if $domains|@count <= 0 || $errors|@count > 0 || !$paymentmethod}disabled{/if}*}

    <div style="font-size: 12px; margin-top: 20px;">Press on Import domains to start importing domain names. Or go back to the previous page if anything is missing.</div>
</div>


<script type="text/javascript">
    const fields = JSON.parse('{$fieldsJSON}')

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
        $('.step-two-form').on("submit", function(event) {
            event.preventDefault();
            goToStep(3);
        });
    });



</script>