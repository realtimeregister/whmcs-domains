<p>
	{$LANG.rtr.process.info}
</p>
<table class="table table-hover align_top">
	<tbody>
		<tr>
			<th>{$LANG.rtr.date}</th>
			<th>{$LANG.rtr.type}</th>
			<th>{$LANG.rtr.action}</th>
			<th>{$LANG.rtr.status}</th>
		</tr>
		{if $processes}
			{foreach from=$processes item=process}
				<tr class="clickable-row" data-href="{$process.link}">
					<td class="nowrap">{$process.createdDate|date_format:"l Y-m-d H:i:s"}</td>
					<td>{$process.type}</td>
					<td>{$process.action}</td>
					<td>{$process.status}</td>
				</tr>
			{/foreach}
		{/if}
	</tbody>
</table>

<script>
	$(function () {
		$(".clickable-row").click(function() {
			window.open($(this).data("href"), '_blank')
		});
	})
</script>

<style>
	.clickable-row:hover {
		cursor: pointer;
	}
</style>