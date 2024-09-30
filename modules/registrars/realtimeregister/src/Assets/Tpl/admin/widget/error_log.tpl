<div class="log-container">
    {foreach from=$logs key=$i item=log}
        <div class="log">
            <small class="log-created">{$log.created_at}</small>
            <button onclick="onAction('{$i}')" class="log-details btn btn-default block"><small>Show Details</small></button>
            <span class="log-message">{$log.message}</span>
        </div>
        <hr/>
    {/foreach}
    <div class="log-modal-container modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{$LANG.rtr.errorlog.details}</h4>
                </div>
                <div class="modal-body log-modal">
                    <div>
                        <strong>{$LANG.rtr.errorlog.filename}: </strong><span class="log-filename"></span>
                    </div>
                    <div>
                        <strong>{$LANG.rtr.errorlog.classname}: </strong><span class="log-classname"></span>
                    </div>
                    <div>
                        <strong>{$LANG.rtr.errorlog.linenumber}: </strong><span class="log-linenumber"></span>
                    </div>
                    <div>
                        <strong>{$LANG.rtr.errorlog.message}: </strong><span class="log-message"></span>
                    </div>
                    <div>
                        <strong>{$LANG.rtr.errorlog.time}: </strong><span class="log-time"></span>
                    </div>
                    <div>
                        <strong class="block">{$LANG.rtr.errorlog.stacktrace}: </strong><span class="stacktrace"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const logModal = $('.log-modal-container');
    const logs = {$logsJSON};

    function onAction(i) {
        $('.log-filename').text(logs[i].filename);
        $('.log-classname').text(logs[i].exception_class);
        $('.log-message').text(logs[i].message);
        $('.log-linenumber').text(logs[i].line);
        $('.log-time').text(logs[i].created_at);

        $('.stacktrace').html(logs[i].details.split("\n").join("<br/>"));
        logModal.modal("show");
    }
</script>

<style>
    .log-container {
        height: 500px;
        overflow-y: scroll;

        &:first-child {
            margin-top: 1rem;
        }
    }
    .log {
        position: relative;
        padding: 0 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .log-created {
        position: absolute;
        left: 1.5rem;
    }
    .log-details {
        cursor: pointer;
        align-self: end;
        padding: 0 12px;
    }
    .log-modal {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }
    .block {
        display: block;
    }
</style>