<table class="table table-bordered">
	<thead>
		<tr>
			<th>Account</th>
			<th>Debit</th>
			<th>Credit</th>
			<th>Note</th>
			<th>PPn</th>
		</tr>
	</thead>
	<tbody>
		@foreach($detail as $list)
		<tr>
			<td>{{ $list->account_lists->account_name }}</td>
			<td style="text-align: right">{{ number_format($list->debit, 2) }}</td>
			<td style="text-align: right">{{ number_format($list->credit, 2) }}</td>
			<td>{{ $list->note }}</td>
			<td>{{ $list->ppn ?? 0 }}</td>
		</tr>
		@endforeach
	</tbody>
</table>