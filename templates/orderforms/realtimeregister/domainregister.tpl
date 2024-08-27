{include file="orderforms/standard_cart/common.tpl"}

<div id="order-standard_cart">
    <div class="row">
        <div class="cart-sidebar">
            {include file="orderforms/standard_cart/sidebar-categories.tpl"}
        </div>
        <div class="cart-body">
            <div class="header-lined">
                <h1 class="font-size-36">
                    {$LANG.registerdomain}
                </h1>
            </div>
            {include file="orderforms/standard_cart/sidebar-categories-collapsed.tpl"}

            <p>{$LANG.orderForm.findNewDomain}</p>

            {if !empty($adacApiKey) && $adacTldToken}
                {include file="../../../modules/registrars/realtimeregister/src/Assets/Tpl/adac.tpl"}
            {else}
                <div class="domain-checker-container">
                    <div class="domain-checker-bg clearfix">
                        <form method="post" action="{$WEB_ROOT}/cart.php" id="frmDomainChecker">
                            <input type="hidden" name="a" value="checkDomain">
                            <div class="row">
                                <div class="col-md-8 col-md-offset-2 offset-md-2 col-xs-10 col-xs-offset-1 col-10 offset-1">
                                    <div class="input-group input-group-lg input-group-box">
                                        <input type="text" name="domain" class="form-control" placeholder="{$LANG.findyourdomain}" value="{$lookupTerm}" id="inputDomain" data-toggle="tooltip" data-placement="left" data-trigger="manual" title="{lang key='orderForm.domainOrKeyword'}" />
                                        <span class="input-group-btn input-group-append">
                                            <button type="submit" id="btnCheckAvailability" class="btn btn-primary domain-check-availability{$captcha->getButtonClass($captchaForm)}">{$LANG.search}</button>
                                        </span>
                                    </div>
                                </div>

                                {if $captcha->isEnabled() && $captcha->isEnabledForForm($captchaForm) && !$captcha->recaptcha->isInvisible()}
                                    <div class="col-md-8 col-md-offset-2 offset-md-2 col-xs-10 col-xs-offset-1 col-10 offset-1">
                                        <div class="captcha-container" id="captchaContainer">
                                            {if $captcha == "recaptcha"}
                                                <br>
                                                <div class="text-center">
                                                    <div class="form-group recaptcha-container"></div>
                                                </div>
                                            {elseif $captcha != "recaptcha"}
                                                <div class="default-captcha default-captcha-register-margin">
                                                    <p>{lang key="cartSimpleCaptcha"}</p>
                                                    <div>
                                                        <img id="inputCaptchaImage" src="{$systemurl}includes/verifyimage.php" align="middle" />
                                                        <input id="inputCaptcha" type="text" name="code" maxlength="6" class="form-control input-sm" data-toggle="tooltip" data-placement="right" data-trigger="manual" title="{lang key='orderForm.required'}" />
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                {/if}
                            </div>
                        </form>
                    </div>
                </div>
            {/if}

            {if !empty($adacApiKey) && $adacTldToken}

            {else}
                <div class="domain-pricing">
                    {if $featuredTlds}
                        <div class="featured-tlds-container">
                            <div class="row">
                                {foreach $featuredTlds as $num => $tldinfo}
                                    {if $num % 3 == 0 && (count($featuredTlds) - $num < 3)}
                                        {if count($featuredTlds) - $num == 2}
                                            <div class="col-sm-2"></div>
                                        {else}
                                            <div class="col-sm-4"></div>
                                        {/if}
                                    {/if}
                                    <div class="col-sm-4 col-xs-6">
                                        <div class="featured-tld">
                                            <div class="img-container">
                                                <img src="{$BASE_PATH_IMG}/tld_logos/{$tldinfo.tldNoDots}.png">
                                            </div>
                                            <div class="price {$tldinfo.tldNoDots}">
                                                {if is_object($tldinfo.register)}
                                                    {$tldinfo.register->toPrefixed()}{if $tldinfo.period > 1}{lang key="orderForm.shortPerYears" years={$tldinfo.period}}{else}{lang key="orderForm.shortPerYear" years=''}{/if}
                                                {else}
                                                    {lang key="domainregnotavailable"}
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    {/if}

                    <h4 class="font-size-18">{lang key='pricing.browseExtByCategory'}</h4>

                    <div class="tld-filters">
                        {foreach $categoriesWithCounts as $category => $count}
                            <a href="#" data-category="{$category}" class="badge badge-secondary">{lang key="domainTldCategory.$category" defaultValue=$category} ({$count})</a>
                        {/foreach}
                    </div>

                    <div class="bg-white">
                        <div class="row tld-pricing-header text-center">
                            <div class="col-md-4 tld-column">{lang key='orderdomain'}</div>
                            <div class="col-sm-8">
                                <div class="row no-gutters">
                                    <div class="col-xs-4 col-4">{lang key='pricing.register'}</div>
                                    <div class="col-xs-4 col-4">{lang key='pricing.transfer'}</div>
                                    <div class="col-xs-4 col-4">{lang key='pricing.renewal'}</div>
                                </div>
                            </div>
                        </div>
                        {foreach $pricing['pricing'] as $tld => $price}
                            <div class="row no-gutters tld-row" data-category="{foreach $price.categories as $category}|{$category}|{/foreach}">
                                <div class="col-md-4 two-row-center px-4">
                                    <strong>.{$tld}</strong>
                                    {if $price.group}
                                        <span class="tld-sale-group tld-sale-group-{$price.group}">
                                            {lang key='domainCheckerSalesGroup.'|cat:$price.group}
                                        </span>
                                    {/if}
                                </div>
                                <div class="col-sm-8">
                                    <div class="row">
                                        <div class="col-xs-4 col-4 text-center">
                                            {if current($price.register) >= 0}
                                                {current($price.register)}<br>
                                                <small>{key($price.register)} {if key($price.register) > 1}{lang key="orderForm.years"}{else}{lang key="orderForm.year"}{/if}</small>
                                            {elseif isset($price.register) && current($price.register) == 0}
                                                <small>{lang key='orderfree'}</small>
                                            {else}
                                                <small>{lang key='na'}</small>
                                            {/if}
                                        </div>
                                        <div class="col-xs-4 col-4 text-center">
                                            {if current($price.transfer) > 0}
                                                {current($price.transfer)}<br>
                                                <small>{key($price.transfer)} {if key($price.register) > 1}{lang key="orderForm.years"}{else}{lang key="orderForm.year"}{/if}</small>
                                            {elseif isset($price.transfer) && current($price.transfer) == 0}
                                                <small>{lang key='orderfree'}</small>
                                            {else}
                                                <small>{lang key='na'}</small>
                                            {/if}
                                        </div>
                                        <div class="col-xs-4 col-4 text-center">
                                            {if current($price.renew) > 0}
                                                {current($price.renew)}<br>
                                                <small>{key($price.renew)} {if key($price.register) > 1}{lang key="orderForm.years"}{else}{lang key="orderForm.year"}{/if}</small>
                                            {elseif isset($price.renew) && current($price.renew) == 0}
                                                <small>{lang key='orderfree'}</small>
                                            {else}
                                                <small>{lang key='na'}</small>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                        <div class="row tld-row no-tlds">
                            <div class="col-xs-12 col-12 text-center">
                                <br>
                                {lang key='pricing.selectExtCategory'}
                                <br><br>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}

            <div class="row">
                <div class="{if $domainTransferEnabled}col-md-6{else}col-md-8 col-md-offset-2 offset-md-2{/if}">
                    <div class="domain-promo-box">

                        <div class="clearfix">
                            <i class="fas fa-server fa-4x"></i>
                            <h3 class="font-size-24 no-wrap">{lang key='orderForm.addHosting'}</h3>
                            <p class="font-bold text-warning">{lang key='orderForm.chooseFromRange'}</p>
                        </div>

                        <p>{lang key='orderForm.packagesForBudget'}</p>

                        <a href="{$WEB_ROOT}/cart.php" class="btn btn-warning">
                            {lang key='orderForm.exploreNow'}
                        </a>
                    </div>
                </div>
                {if $domainTransferEnabled}
                    <div class="col-md-6">
                        <div class="domain-promo-box">

                            <div class="clearfix">
                                <i class="fas fa-globe fa-4x"></i>
                                <h3 class="font-size-22">{lang key='orderForm.transferToUs'}</h3>
                                <p class="font-bold text-primary">{lang key='orderForm.transferExtend'}*</p>
                            </div>

                            <a href="{$WEB_ROOT}/cart.php?a=add&domain=transfer" class="btn btn-primary">
                                {lang key='orderForm.transferDomain'}
                            </a>

                            <p class="small">* {lang key='orderForm.extendExclusions'}</p>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function() {
        jQuery('.tld-filters a:first-child').click();
        {if $lookupTerm && !$captchaError}
        jQuery('#btnCheckAvailability').click();
        {/if}
    });
</script>
