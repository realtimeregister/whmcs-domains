<!--suppress ALL, HtmlFormInputWithoutLabel -->
<h2 class="text-center" style="margin: 30px 0 0;">{$LANG.rtr.dnssecmanagement}</h2>
<h4 class="text-center" style="margin: 0 0 30px">{$domainName}</h4>
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

<form method="POST" class="text-muted">
    <input type='hidden' name='totalDNSsec' value="{count($keyData)}">
    <input type="hidden" name="dnssecAction" value="update" />
    <table class="table table-bordered bg-white">
        <thead>
        <tr>
            <th class="col-md-2" scope="col">{$LANG.rtr.flags}</th>
            <th class="col-md-1" scope="col">{$LANG.rtr.protocol}</th>
            <th class="col-md-3" scope="col">{$LANG.rtr.algorithm}</th>
            <th class="col-md-5" scope="col">{$LANG.rtr.public_key}</th>
            <th class="col-md-1">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        {if $keyData}
            {foreach from=$keyData key=i item=dns}
                <tr>
                    <td>
                        <select name="flags[{$i}]" class="form-control">
                            <option {if $dns->flags == '256'}selected{/if} value="256">256 (ZSK)</option>
                            <option {if $dns->flags == '257'}selected{/if} value="257">257 (KSK)</option>
                        </select>
                    </td>
                    <td>{$dns->protocol}</td>
                    <td>
                        <select name="algorithm[{$i}]" class="form-control">
                            <option {if $dns->algorithm == '3'}selected{/if} value="3">3 (DSA/SHA1)</option>
                            <option {if $dns->algorithm == '5'}selected{/if} value="5">5 (RSA/SHA-1)</option>
                            <option {if $dns->algorithm == '6'}selected{/if} value="6">6 (DSA-NSEC3-SHA1)</option>
                            <option {if $dns->algorithm == '7'}selected{/if} value="7">7 (RSASHA1-NSEC3-SHA1)</option>
                            <option {if $dns->algorithm == '8'}selected{/if} value="8">8 (RSA/SHA-256)</option>
                            <option {if $dns->algorithm == '10'}selected{/if} value="10">10 (RSA/SHA-512)</option>
                            <option {if $dns->algorithm == '12'}selected{/if} value="12">12 (GOST R 34.10-2001)</option>
                            <option {if $dns->algorithm == '13'}selected{/if} value="13">13 (ECDSA Curve P-256 with SHA-256)</option>
                            <option {if $dns->algorithm == '14'}selected{/if} value="14">14 (ECDSA Curve P-384 with SHA-384)</option>
                            <option {if $dns->algorithm == '15'}selected{/if} value="15">15 (Ed25519)</option>
                            <option {if $dns->algorithm == '16'}selected{/if} value="16">16 (Ed448)</option>
                            <option {if $dns->algorithm == '17'}selected{/if} value="17">17 (SM2 signing algorithm with SM3 hashing algorithm)</option>
                            <option {if $dns->algorithm == '23'}selected{/if} value="23">23 (GOST R 34.10.2012)</option>
                        </select>
                    </td>
                    <td>
                        <textarea name="publicKey[{$i}]" required="required" rows="6" class="form-control">{$dns->publicKey}</textarea>
                    </td>
                    <td class='remove'>
                        <a href="#" class="text-danger remove-key-data">
                            <i class="fas fa-trash fa-fw"></i>
                        </a>
                    </td>
                </tr>
            {/foreach}
        {/if}
        <tr>
            <td colspan="2">
                <a href="#" class="btn btn-default add-keydata">{$LANG.rtr.add_keydata}</a>
            </td>
            <td colspan="2">
                <div class="buttons text-center" style="margin-bottom: 20px;">
                    <input class="btn btn-primary" type="submit" value="{$LANG.rtr.save}" />
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>
