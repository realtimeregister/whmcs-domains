<h1>{$domain->domain}</h1>

{if $dnsrecords['zones'] == null}
    <div class="alert alert-primary" role="alert">
        {$LANG.rtr.dns.no_records_yet}
    </div>
{/if}
{if $success == true}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {$LANG.rtr.dns.save_successful}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
{/if}

<form id="realtimeregister-dns-addon" method="post" action="{$smarty.server.PHP_SELF}?action=domaindns">
    <input type="hidden" name="sub" value="save" />
    <input type="hidden" name="domainid" value="{$domainid}" />

    <h5>{$LANG.rtr.dns.soa_records}</h5>

    <table class="table table-striped dns-overview-form-soa">
        <thead>
        <tr>
            <th scope="col">{$LANG.rtr.dns.hostmaster}</th>
            <th scope="col">{$LANG.rtr.dns.refresh}</th>
            <th scope="col">{$LANG.rtr.dns.retry}</th>
            <th scope="col">{$LANG.rtr.dns.expire}</th>
            <th scope="col">{$LANG.rtr.dns.ttl}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <label class="sr-only" for="soa_hostmaster">{$LANG.rtr.dns.hostmaster}</label>
                <input id="soa_hostmaster" type="text" name="soa[hostmaster]" value="{$dnsrecords['soa']['hostmaster']}" class="form-control">
            </td>
            <td>
                <label class="sr-only" for="soa_refresh">{$LANG.rtr.dns.refresh}</label>
                <input id="soa_refresh" type="text" name="soa[refresh]" value="{$dnsrecords['soa']['refresh']}" class="form-control">
            </td>
            <td>
                <label class="sr-only" for="soa_retry">{$LANG.rtr.dns.retry}</label>
                <input id="soa_retry" type="number" name="soa[retry]" value="{$dnsrecords['soa']['retry']}" class="form-control">
            </td>
            <td>
                <label class="sr-only" for="soa_expire">{$LANG.rtr.dns.expire}</label>
                <input id="soa_expire" type="number" name="soa[expire]" value="{$dnsrecords['soa']['expire']}" class="form-control">
            </td>
            <td>
                <label class="sr-only" for="soa_ttl">{$LANG.rtr.dns.ttl}</label>
                <input id="soa_ttl" type="number" name="soa[ttl]" value="{$dnsrecords['soa']['ttl']}" class="form-control">
            </td>
        </tr>
        </tbody>
    </table>
    <table class="table table-striped dns-overview-form">
        <thead>
        <tr>
            <th scope="col">{$LANG.rtr.dns.name}</th>
            <th scope="col">{$LANG.rtr.dns.type}</th>
            <th scope="col">{$LANG.rtr.dns.content}</th>
            <th scope="col">{$LANG.rtr.dns.ttl}</th>
            <th scope="col">{$LANG.rtr.dns.prio}</th>
        </tr>
        </thead>
        <tbody>

        {if $dnsrecords['zones']}
            {foreach $dnsrecords['zones'] as $zone}
                {if is_array($zone)}
                    <!-- {$zone@index} --> {* this needs to be here for the counter in item-row.tpl*}
                    {include './dns/item-row.tpl' }
                {/if}
            {/foreach}
        {else}
{*             If there isn't a line yet, we add the first, to help our users*}
            {include './dns/item-row.tpl' }
        {/if}
        </tbody>
    </table>
    <button type="button" class="btn btn-success" id="add-row-btn"><i class="fa fa-plus"></i> {$LANG.rtr.dns.add_new_row}</button>
    <button type="submit" class="btn btn-primary float-right">{$LANG.rtr.dns.save}</button>
</form>

<script src="{assetPath file='overview.js'}"></script>
