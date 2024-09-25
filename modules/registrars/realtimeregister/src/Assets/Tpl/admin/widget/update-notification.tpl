<div class="alert alert-warning clearfix">
    <div class="col-xs-12 col-sm-6 col-lg-6">
        <i class="glyphicon glyphicon-refresh"></i> There is an update available for the Realtime Register plugin.
    </div>
    <div class="col-xs-12 col-sm-6 col-lg-6">
        <div>Newest version: {$update_data->version} {if $update_data->prerelease}<span class="label label-warning">Prerelease</span>{/if}</div>
        <div>{$update_data->description}</div>
        <a class="btn btn-info pull-right" href="{$update_data->link}" target="_blank" style="margin-left: 10px;">View update</a>
    </div>
</div>