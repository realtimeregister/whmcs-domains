<tr>
    {if $zone@index}
        {assign var=index value=$zone@index}
    {else}
        {assign var=index value=0}
    {/if}

    <td>
        <input type="text" class="form-control {if $errors['errors'][$index]['name']}is-invalid{/if}" placeholder="{$_lang['name']}" value="{$zone.name}" name="dns-item[{$index}][name]">
        {if $errors['errors'][$index]['name']}
            <div class="invalid-feedback">
                {foreach $errors['errors'][$index]['name'] as $key => $err}
                    {$errors['errors'][$index]['name'][$key]} {if not $err@last}<br>{/if}
                {/foreach}
            </div>
        {/if}
    </td>
    <td>
        <select class="form-control {if $errors['errors'][$index]['type']}is-invalid{/if}" name="dns-item[{$index}][type]">
            <option disabled="">{$_lang['nothing_selected']}</option>
            {foreach from=$typesOfRecords item=record}
                <option value="{$record}" {if $record eq $zone.type}selected{/if}>{$record}</option>
            {/foreach}
        </select>
        {if $errors['errors'][$index]['type']}
            <div class="invalid-feedback">
                {foreach $errors['errors'][$index]['type'] as $key => $err}
                    {$errors['errors'][$index]['type'][$key]} {if not $err@last}<br>{/if}
                {/foreach}
            </div>
        {/if}
    </td>
    <td>
        <input type="text" class="form-control {if $errors['errors'][$index]['content']}is-invalid{/if}" placeholder="{$_lang['content']}" required value="{$zone.content}" name="dns-item[{$index}][content]">
        {if $errors['errors'][$index]['content']}
            <div class="invalid-feedback">
                {foreach $errors['errors'][$index]['content'] as $key => $err}
                    {$errors['errors'][$index]['content'][$key]} {if not $err@last}<br>{/if}
                {/foreach}
            </div>
        {/if}
    </td>
    <td>
        <input type="number" class="form-control {if $errors['errors'][$index]['ttl']}is-invalid{/if}" placeholder="{$_lang['ttl']}" value="{$zone.ttl}" name="dns-item[{$index}][ttl]">
        {if $errors['errors'][$index]['ttl']}
            <div class="invalid-feedback">
                {foreach $errors['errors'][$index]['ttl'] as $key => $err}
                    {$errors['errors'][$index]['ttl'][$key]} {if not $err@last}<br>{/if}
                {/foreach}
            </div>
        {/if}
    </td>
    <td>
        <input type="number" class="form-control {if $errors['errors'][$index]['prio']}is-invalid{/if}" placeholder="{$_lang['prio']}" value="{$zone.prio}" name="dns-item[{$index}][prio]">
        {if $errors['errors'][$index]['prio']}
            <div class="invalid-feedback">
                {foreach $errors['errors'][$index]['prio'] as $key => $err}
                    {$errors['errors'][$index]['prio'][$key]} {if not $err@last}<br>{/if}
                {/foreach}
            </div>
        {/if}
    </td>
</tr>