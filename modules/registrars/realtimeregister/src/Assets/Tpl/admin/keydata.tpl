<table class="table table-hover align_top">
    <tbody>
        <tr>
            <th>{$LANG.rtr.flags}</th>
            <th>{$LANG.rtr.protocol}</th>
            <th>{$LANG.rtr.algorithm}</th>
            <th>{$LANG.rtr.public_key}</th>
        </tr>

        {if $keyData}
            {foreach from=$keyData item=ds}
                <tr>
                    <td class="nowrap">{$ds->flags}</td>
                    <td>{$ds->protocol}</td>
                    <td>{$ds->algorithm}</td>
                    <td>{$ds->publicKey}</td>
                </tr>
            {/foreach}
        {/if}

    </tbody>
</table>
