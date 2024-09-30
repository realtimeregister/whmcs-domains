<div class="alert alert-warning clearfix">
    <div class="col-xs-12 col-sm-6 col-lg-6">
        <i class="glyphicon glyphicon-refresh"></i> {$LANG.rtr.update_notification.update_available}
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-6">
        <div>{$LANG.rtr.update_notification.newest}: {$update_data->version} {if $update_data->prerelease}<span class="label label-warning">{$LANG.rtr.update_notification.prerelease}</span>{/if}</div>
        <div>{$update_data->description}</div>
        <a class="btn btn-info pull-right" href="{$update_data->link}" target="_blank" style="margin-left: 10px;">{$LANG.rtr.update_notification.view_update}</a>
    </div>
</div>