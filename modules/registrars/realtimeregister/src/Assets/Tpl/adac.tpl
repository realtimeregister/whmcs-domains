<div class="adac-js">
	<div class="adac-js__checker">
		<div class="domain-checker-container">
			<div class="domain-checker-bg clearfix">
				<div class="row">
					<div class="col-md-8 col-md-offset-2 offset-md-2 col-xs-10 col-xs-offset-1 col-10 offset-1">
						<div class="input-group input-group-lg input-group-box">
							<input class="form-control" type="text" placeholder="{$LANG.findyourdomain}" value="{$lookupTerm}" id="adac-js-domain-input">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="adac-js__results">
		<div id="adac-js-categories"></div>
		<div id="adac-js-domain-results"></div>
		<div id="adac-js-suggestions"></div>
	</div>
</div>

{if !empty($adacApiKey) && $adacTldToken}
	<script>
		jQuery(document).ready(function () {
			let API_KEY = '{$adacApiKey}';
			let API_TOKEN = '{$adacTldToken}';
			{literal}
			adac.initialize(API_KEY, {PRIORITY_LIST_TOKEN: API_TOKEN});
			{/literal}

			updateCurrencyFormAction();
			jQuery("#adac-js-domain-input").on("input", updateCurrencyFormAction);
		});

		function updateCurrencyFormAction() {
			let $currencyForm = jQuery("select[name=currency]").parent("form");
			if (!$currencyForm.length) {
				return;
			}
			let parts = $currencyForm.attr("action").split("?");
			let params = new URLSearchParams(parts[1]);
			params.set("query", jQuery("#adac-js-domain-input").val());
			$currencyForm.attr("action", parts[0] + "?" + params.toString());
		}
	</script>
{/if}
