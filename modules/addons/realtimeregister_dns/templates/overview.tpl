<h1>{$domain->domain}</h1>

{if $zones == null}
    <div class="alert alert-primary" role="alert">
        {$_lang['no_records_yet']}
    </div>
{/if}

{if $success == true}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {$_lang['save_successful']}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
{/if}
<form id="realtimeregister-dns-addon" method="post">
    <h5>SOA record</h5>

    <table class="table table-striped dns-overview-form-soa">
        <thead>
            <tr>
                <th scope="col">{$_lang['hostmaster']}</th>
                <th scope="col">{$_lang['refresh']}</th>
                <th scope="col">{$_lang['retry']}</th>
                <th scope="col">{$_lang['expire']}</th>
                <th scope="col">{$_lang['ttl']}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <label class="sr-only" for="soa_hostmaster">{$_lang['hostmaster']}</label>
                    <input id="soa_hostmaster" type="text" name="soa[hostmaster]" value="{$soa['hostmaster']}" class="form-control">
                </td>
                <td>
                    <label class="sr-only" for="soa_refresh">{$_lang['refresh']}</label>
                    <input id="soa_refresh" type="text" name="soa[refresh]" value="{$soa['refresh']}" class="form-control">
                </td>
                <td>
                    <label class="sr-only" for="soa_retry">{$_lang['retry']}</label>
                    <input id="soa_retry" type="number" name="soa[retry]" value="{$soa['retry']}" class="form-control">
                </td>
                <td>
                    <label class="sr-only" for="soa_expire">{$_lang['expire']}</label>
                    <input id="soa_expire" type="number" name="soa[expire]" value="{$soa['expire']}" class="form-control">
                </td>
                <td>
                    <label class="sr-only" for="soa_ttl">{$_lang['ttl']}</label>
                    <input id="soa_ttl" type="number" name="soa[ttl]" value="{$soa['ttl']}" class="form-control">
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table table-striped dns-overview-form">
        <thead>
            <tr>
                <th scope="col">{$_lang['name']}</th>
                <th scope="col">{$_lang['type']}</th>
                <th scope="col">{$_lang['content']}</th>
                <th scope="col">{$_lang['ttl']}</th>
                <th scope="col">{$_lang['prio']}</th>
            </tr>
        </thead>
        <tbody>

            {if $zones}
                {foreach $zones as $zone}
                    <!-- {$zone@index} --> {* this needs to be here for the counter in item-row.tpl*}
                    {include './item-row.tpl' }
                {/foreach}
            {else}
                {* If there isn't a line yet, we add the first, to help our users *}
                {include './item-row.tpl' }
            {/if}
        </tbody>
    </table>
    <button type="button" class="btn btn-success" id="add-row-btn"><i class="fa fa-plus"></i> {$_lang['add_new_row']}</button>
    <button type="submit" class="btn btn-primary float-right">{$_lang['save']}</button>
</form>

<script src="/modules/addons/realtimeregister_dns/templates/js/overview.js"></script>