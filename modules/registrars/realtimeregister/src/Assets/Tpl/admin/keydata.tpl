<table class="table table-hover align_top">
    <tbody>
        <tr>
            <th>Flags</th>
            <th>Protocol</th>
            <th>Algorithm</th>
            <th>Public key</th>
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
