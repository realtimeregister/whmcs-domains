<table class="table table-hover align_top">
	<tbody>
		<tr>
			<th>{$LANG.rtr.date}</th>
			<th>{$LANG.rtr.action}</th>
			<th>{$LANG.rtr.status}</th>
		</tr>
		{if $processes}
			{foreach from=$processes item=process}
				<tr>
					<td class="nowrap">{$process.createdDate|date_format:"l Y-m-d H:i:s"}</td>
					<td>{$process.action}</td>
					<td>{$process.status}</td>
				</tr>
			{/foreach}
		{/if}
	</tbody>
</table>