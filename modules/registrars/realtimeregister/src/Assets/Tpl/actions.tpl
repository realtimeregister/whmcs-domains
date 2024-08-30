<div id="actions">

    <div class="action-group">
        <button class="import-wizard" type="button" onclick="onAction(this, 'importWizard')">Import Domains</button>
        <button class="sync-expiry" type="button" onclick="onAction(this, 'syncExpiry')">Sync Domain Expiry Dates</button>
        <button class="auto-renew" type="button" onclick="onAction(this, 'autoRenew')">Change Auto Renew Status</button>
    </div>

    <div class="modal-container modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Action</h4>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    const module = 'realtimeregister';
    const modal = $('.modal-container');


    function onAction(element, action) {
        const contentArea = $('.modal-body');
        $('.modal-title').text(element.innerText);

        $.post(
            window.location.href,
            {
                action,
                module
            },
            function (response) {
                contentArea.html(response);
                modal.modal("show");
                window.scrollTo(0, 0);
            },
            "html"
        ).fail(console.error);
    }


    $(document).ready(
        function () {

        }
    );
</script>