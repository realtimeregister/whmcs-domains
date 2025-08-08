{if $problems|@count gt 0}
    <div class="widget-content-padded">
        <div class='col-12'>
            <div class="alert alert-info" role="alert">{$LANG.rtr.problematic_domains.explanation}:</div>
                <div class="list-group">
                {foreach $problems as $item}
                    <a href="{$baseLink}{$item.domain_name}" class="list-group-item list-group-item-action" target="_blank">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">{$item.domain_name}</h5>
                            <small>{$LANG.rtr.problematic_domains.since} {$item.since|date_format:"%D"}</small>
                        </div>
                    </a>
                {/foreach}
            </div>
        </div>
    </div>
{else}
    <div class='col-12' style='padding: 15px'>{$LANG.rtr.problematic_domains.none}</div>
{/if}
