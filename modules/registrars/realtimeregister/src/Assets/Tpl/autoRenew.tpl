<div class="autorenew">
    <p>
        Set the auto-renewal for <span class="amount">{count($domains)}</span> domains to <strong>false</strong>
    </p>

    <p class="processed_updates">Processed updates: <span class="processed_updates__amount">0</span>/<span
                class="total">{count($domains)}</span></p>

    <div class="rtr-progress-bar">
        <div class="progress-label">Processing...</div>
    </div>

    <p class="failed_updates">Failed updates: <span class="failed_updates__amount">0</span></span></p>
    <div class="failed_domains"></div>


    <p class="small">
        <strong>NOTE</strong>: if you have .NL and .DE domains with a period shorter than 12 months, the auto-renew
        status will not be changed.<br>
        .NL/.DE --> periods 1 and 3 months must be on AUTO_RENEW TRUE at Realtime Register.<br>
    </p>

    <div class="modal-footer">
        <form id="renew_form" method="post">
            <button type="submit" class="submit btn btn-success"><i class="fa fa-refresh" aria-hidden="true"></i>
                Update all domains
            </button>
        </form>
    </div>
</div>

<script type='text/javascript'>
    $(function () {
        const domains = {$domainsJSON};
        const domainCount = domains.length;
        let updatedDomains = 0;
        let failedDomains = 0;

        $(".autorenew #renew_form").submit(function (event) {
            event.preventDefault();
            $("#renew_form .submit").prop("disabled", true);
            showProgressBar();

            $(".autorenew .processed_updates").addClass("is-active");

            if (domains.length > 0) {
                domains.forEach(value => updateDomain(value));
            } else {
                progressComplete();
            }
        });

        function updateDomain(domain) {
            $.post(
                window.location.href,
                {
                    module: "realtimeregister",
                    action: "autoRenew",
                    domain
                },
                function () {
                    updatedDomains++;

                    $(".autorenew .processed_updates__amount").text(updatedDomains + failedDomains);

                    setProgress(domainCount, updatedDomains + failedDomains, progressComplete);
                },
                "json"
            ).fail(function (response) {
                failedDomains++;

                $(".autorenew .processed_updates__amount").text(updatedDomains + failedDomains);

                $(".autorenew .failed_updates__amount").text(failedDomains);
                $(".autorenew .failed_domains")
                    .addClass("is-active")
                    .prepend("<p>" + response.responseJSON.message + "</p>");
                $(".autorenew .failed_updates").addClass("is-active");

                setProgress(domainCount, updatedDomains + failedDomains);
            });
        }
    });

</script>
