<table class="table table-hover align_top">
	<tbody>
		<tr>
			<th>Date</th>
			<th>Type</th>
			<th>Status</th>
			<th>Message</th>
		</tr>

		{if $logs}
			{foreach from=$logs item=log}
				<tr>
					<td class="nowrap">{$log->date|date_format:"l Y-m-d H:i:s"}</td>
					<td>{$type}</td>
					<td>{$log->status}</td>
					<td>
						{$log->message}
					</td>
				</tr>
			{/foreach}
		{/if}
	</tbody>
</table>