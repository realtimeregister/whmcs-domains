<tr>
    {if $zone@index}
        {assign var=index value=$zone@index}
    {else}
        {assign var=index value=0}
    {/if}

    <td>
        <label class="sr-only" for="dns-items-{$index}-name">{$_lang['name']}</label>
        <input type="text" class="form-control {if isset($dnsrecords.formerrors[$index]['name'])}is-invalid{/if}"
               placeholder="{$_lang['name']}" value="{$zone['name']}" id="dns-items-{$index}-name" name="dns-items[{$index}][name]">
        {if isset($dnsrecords.formerrors[$index]['name'])}
            <div class="invalid-feedback">
                {foreach $dnsrecords.formerrors[$index]['name'] as $key => $err}
                    {$dnsrecords.formerrors[$index]['name'][$key]} {if not $err@last}<br>{/if}
                {/foreach}
            </div>
        {/if}
    </td>
    <td>
        <label class="sr-only" for="dns-items-{$index}-type">{$_lang['type']}</label>
        <select class="form-control {if isset($dnsrecords.formerrors[$index]['type'])}is-invalid{/if}" id="dns-items-{$index}-type" name="dns-items[{$index}][type]">
            <option disabled="">{$_lang['nothing_selected']}</option>
            {foreach from=$typesOfRecords item=record}
                <option value="{$record}" {if $record eq $zone['type']}selected{/if}>{$record}</option>
            {/foreach}
            {if isset($dnsrecords.formerrors[$index]['type'])}
                <div class="invalid-feedback">
                    {foreach $dnsrecords.formerrors[$index]['type'] as $key => $err}
                        {$dnsrecords.formerrors[$index]['type'][$key]} {if not $err@last}<br>{/if}
                    {/foreach}
                </div>
            {/if}
    </td>
    <td>
        <label class="sr-only" for="dns-items-{$index}-content">{$_lang['content']}</label>
        <input type="text" class="form-control {if isset($dnsrecords.formerrors[$index]['content'])}is-invalid{/if}"
               placeholder="{$_lang['content']}" required value="{$zone['content']}" id="dns-items-{$index}-content" name="dns-items[{$index}][content]">
        {if isset($dnsrecords.formerrors[$index]['content'])}
            <div class="invalid-feedback">
                {foreach $dnsrecords.formerrors[$index]['content'] as $key => $err}
                    {$dnsrecords.formerrors[$index]['content'][$key]} {if not $err@last}<br>{/if}
                {/foreach}
            </div>
        {/if}
    </td>
    <td>
        <label class="sr-only" for="dns-items-{$index}-ttl">{$_lang['ttl']}</label>
        <input type="number" class="form-control {if isset($dnsrecords.formerrors[$index]['ttl'])}is-invalid{/if}"
               placeholder="{$_lang['ttl']}" value="{$zone['ttl']}" id="dns-items-{$index}-ttl" name="dns-items[{$index}][ttl]">
        {if isset($dnsrecords.formerrors[$index]['ttl'])}
            <div class="invalid-feedback">
                {foreach $dnsrecords.formerrors[$index]['ttl'] as $key => $err}
                    {$dnsrecords.formerrors[$index]['ttl'][$key]} {if not $err@last}<br>{/if}
                {/foreach}
            </div>
        {/if}
    </td>
    <td>
        <label class="sr-only" for="dns-items-{$index}-prio">{$_lang['prio']}</label>
        <input type="number" class="form-control {if isset($dnsrecords.formerrors[$index]['prio'])}is-invalid{/if}"
               placeholder="{$_lang['prio']}" value="{$zone['prio']}" id="dns-items-{$index}-prio" name="dns-items[{$index}][prio]">
        {if isset($dnsrecords.formerrors[$index]['prio'])}
            <div class="invalid-feedback">
                {foreach $dnsrecords.formerrors[$index]['prio'] as $key => $err}
                    {$dnsrecords.formerrors[$index]['prio'][$key]} {if not $err@last}<br>{/if}
                {/foreach}
            </div>
        {/if}
    </td>
    <td>
        <button type="button" class="btn btn-outline-danger delete-row-btn"><i class="fas fa-trash-alt"></i></button>
    </td>
</tr>