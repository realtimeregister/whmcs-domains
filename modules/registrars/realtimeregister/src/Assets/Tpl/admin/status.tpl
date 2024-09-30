<table class="table table-hover align_top">
    <tbody>
    {if $status}
        {foreach from=$status item=ds}
            <tr>
                <td class="nowrap">{$ds}</td>
            </tr>
        {/foreach}
    {/if}

    </tbody>
</table>
