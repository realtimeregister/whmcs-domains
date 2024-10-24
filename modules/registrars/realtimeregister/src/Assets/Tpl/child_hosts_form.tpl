<!--suppress ALL, HtmlFormInputWithoutLabel -->
<h2 style="text-align: center;margin: 30px 0 0;">{$LANG.rtr.childhostmanagement}</h2>
<h3 style="text-align: center;margin: 0 0 30px">{$domainName}</h3>
{if $error}
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <strong>{$LANG.rtr.something_went_wrong}</strong> {$error}
    </div>
{/if}

{if $saved}
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <strong>{$LANG.rtr.saved}</strong>
    </div>
{/if}

<ul class="list-group child-host mb-3">
    {foreach from=$hosts key=i item=host}
        <li class="list-group-item d-flex justify-content-between lh-condensed">
            <div class="spinner">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
            <div>
                <h6 class="my-0" style="font-weight: bold; padding-bottom: 15px; border-bottom: 1px solid whitesmoke;">{$host->hostName}
                    <a href="#" data-ns="{$host->hostName}" style="float: right;" class="btn btn-danger btn-xs delete-ns"><i
                                class="text-danger fas fa-trash fa-white"></i> {$LANG.rtr.deletehost}</a>
                </h6>
            </div>

            <form method="POST" class="text-muted">
                <input type='hidden' name='totalIPS' value="{count($host->addresses)}">
                <input type='hidden' name='hostName' value="{$host->hostName}">
                <input type="hidden" name="hostAction" value="update"/>
                <table class="child-host__table">
                    <tr>
                        <th>
                            {$LANG.rtr.ipaddress}
                        </th>
                        <th style="width: 100px; padding-left: 10px;">
                            {$LANG.rtr.version}
                        </th>
                        <th style="width: 20px;"></th>
                    </tr>
                    {foreach from=$host->addresses key=i item=address}
                        <tr>
                            <td class="ip">
                                <label>
                                    <input type="text" name="host[{$i}]" value="{$address.address}"/>
                                </label>
                            </td>
                            <td class="version">
                                <label>
                                    <select class="ip-version" name="ipVersion[{$i}]">
                                        <option value="V4" {if $address.ipVersion=='V4'} selected="selected"{/if}>V4</option>
                                        <option value="V6" {if $address.ipVersion=='V6'} selected="selected"{/if}>V6</option>
                                    </select>
                                </label>
                            </td>
                            <td class='remove'>
                                {if $i}
                                    <a href="#" class="text-danger remove-ip"><i class="fas fa-trash"></i></a>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                    <tr>
                        <td>
                            {if $totalIPS < 13}
                                <button type="button" class="btn btn-default add-ip">{$LANG.rtr.add_ip}</button>
                            {/if}
                        </td>
                    </tr>
                </table>
                <div class="buttons text-right" style="margin-bottom: 20px;">
                    <input class="btn btn-primary" type="submit" value="{$LANG.rtr.save}"/>
                </div>
            </form>
        </li>
    {/foreach}
</ul>


<div class="list-group child-host">
    <div class="list-group-item">
        <h2 class="text-center" style="margin: 30px 0 10px">{$LANG.rtr.addnew}</h2>
        <form method="POST" class="text-muted">
            <input type="hidden" name="hostAction" value="create"/>
            <table class="child-host__table">
                <tr>
                    <th style="vertical-align: top">
                        {$LANG.rtr.uniquenameserver}
                    </th>
                    <th style="padding-left: 10px;">
                        {$LANG.rtr.ipaddress}
                    </th>
                    <th style="width: 120px; padding-left: 10px;">
                        {$LANG.rtr.version}
                    </th>
                </tr>
                <tr>
                    <td>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="input-group">
                                <input type="text" name="hostName" value=""/>
                                <div class="input-group-addon" style="font-size: 12px;">.{$domainName}</div>
                            </div>
                        </div>

                    </td>
                    <td class="v4" style="padding-left: 10px;">
                        <label>
                            <input type="text" name="ipAddress" value=""/>
                        </label>
                    </td>
                    <td class="version" style="padding-right: 20px;">
                        <label>
                            <select class="ip-version" name="ipVersion">
                                <option value="V4">V4</option>
                                <option value="V6">V6</option>
                            </select>
                        </label>
                    </td>
                </tr>
            </table>
            <div class="buttons text-right" style="margin-bottom: 20px;">
                <input class="btn btn-primary" type="submit" value="{$LANG.rtr.save}"/>
            </div>
        </form>
    </div>
</div>
