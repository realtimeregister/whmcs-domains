<div id="actions">

    <div class="action-group">
        {if $status == 'offline'}
            <div class="alert alert-danger m-5">
                You have not <a href="{$modulelink}configregistrars.php">configured the module
                    <span class="glyphicon glyphicon-cog" aria-hidden="true"></span></a> yet, please do so before using the actions below.

            </div>
        {/if}
        <div {if $status == 'offline'}style="opacity: 30%;"{/if}>
            <div title="Import Domains"
                 class="action-item health-status-block status-badge-cyan clearfix"
                 onclick="onAction(this, 'importWizard')"
            >
                <div class="icon" style="width: 100px;">
                    <i class="fas fa-file-import"></i>
                </div>
                <div class="detail">
                    <span class="count">{$LANG.rtr.actions.import_domains}</span>
                    <span class="desc">{$LANG.rtr.actions.import_into_whmcs}</span>
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
                    <span class="count">{$LANG.rtr.actions.sync_expire_dates}</span>
                    <span class="desc">{$LANG.rtr.actions.sync_all_expire_dates}</span>
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
                    <span class="count">{$LANG.rtr.actions.change_autorenew_status}</span>
                    <span class="desc">{$LANG.rtr.actions.change_autorenew_to_false}</span>
                </div>
            </div>
        </div>

        <div class="modal-container modal fade" id="import-modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{$LANG.rtr.action}</h4>
                    </div>
                    <div class="modal-body"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    const module = 'realtimeregister';
    const modal = $('#import-modal');

    function onAction(element, action) {
        const contentArea = $('#import-modal .modal-body');
        modal.find('.modal-title').text(element.title);

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