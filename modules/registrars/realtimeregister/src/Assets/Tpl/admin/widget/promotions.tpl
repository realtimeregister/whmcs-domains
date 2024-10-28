{if $promotions|@count gt 0}
    <div class="widget-content-padded">
        <div class='col-12'>
            <div class="alert alert-info" role="alert">{$LANG.rtr.promotions.explanation}:</div>
            {foreach $promotions as $item}
                <div class="panel panel-default" style="padding: 5px;">
                    <div class="panel-heading"><h4>{$LANG.rtr.promotions.product}: {$item->product}</h4></div>
                    <div class="panel-body">
                        <div class="widget-content-padded">
                            <table class="table">
                                <tr>
                                    <th>{$LANG.rtr.action}</th>
                                    <td>{$item->action}</td>
                                </tr>
                                <tr>
                                    <th>{$LANG.rtr.promotions.price}</th>
                                    <td>{$item->currency} {($item->price/100)|number_format:2}</td>
                                </tr>
                                <tr>
                                    <th>{$LANG.rtr.promotions.from_date}</th>
                                    <td>{$item->fromDate|date_format:"%D"}</td>
                                </tr>
                                <tr>
                                    <th>{$LANG.rtr.promotions.end_date}</th>
                                    <td>{$item->endDate|date_format:"%D"}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{else}
    <div class='col-12' style='padding: 15px'>{$LANG.rtr.promotions.none}</div>
{/if}
