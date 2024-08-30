<div id="actions">

    <div class="action-group">
        <div title="Import Domains"
             class="action-item health-status-block status-badge-cyan clearfix"
             onclick="onAction(this, 'importWizard')"
        >
            <div class="icon" style="width: 100px;">
                <i class="fas fa-file-import"></i>
            </div>
            <div class="detail">
                <span class="count">Import domains</span>
                <span class="desc">Import domains/clients from RealtimeRegister into WHMCS</span>
            </div>
        </div>
        <div title="Sync Domain Expiry Dates"
             class="action-item health-status-block status-badge-cyan clearfix"
             onclick="onAction(this, 'syncExpiry')"
        >
            <div class="icon" style="width: 100px;">
                <i class="fas fa-sync"></i>
            </div>
            <div class="detail">
                <span class="count">Sync Expiry Dates</span>
                <span class="desc">Sync the expiry date for all domains in your WHMCS account</span>
            </div>
        </div>
        <div title="Change Auto Renew status"
             class="action-item health-status-block status-badge-cyan clearfix"
             onclick="onAction(this, 'autoRenew')"
        >
            <div class="icon" style="width: 100px;">
                <i class="fas fa-retweet"></i>
            </div>
            <div class="detail">
                <span class="count">Change Auto Renew Status</span>
                <span class="desc">Change the domains autorenew status to false at RealtimeRegister</span>
            </div>
        </div>
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
        $('.modal-title').text(element.title);

        $.post(
            window.location.href,
            {
                action,
                module
            },
            function (response) {
                contentArea.html(response);
                modal.modal("show");
            },
            "html"
        ).fail(console.error);
    }
</script>