<table class="table table-bordered">
	<thead>
		<tr>
			<th>Account</th>
			<th>Account Price</th>

			<th>Item</th>
			<th>Price</th>
			<th>Quantity</th>

			<th>Discount</th>
			<th>PPn</th>
			<th>Total</th>

			<th>Note</th>
		</tr>
	</thead>
	<tbody>
		@foreach($detail as $list)
		<tr>
			<td>{{$list->account_lists->account_name}}</td>
			<td style="text-align: right">{{ number_format($list->account_lists->total_account_balance) }}</td>

			<td style="">{{$list->item}}</td>
			<td style="text-align: right">{{ number_format($list->price) }}</td>
			<td style="text-align: right">{{ number_format($list->qty) }}</td>

			<td style="text-align: right">{{ number_format($list->discount) }}</td>
			<td style="text-align: right">{{ number_format($list->ppn) }}</td>
			<td style="text-align: right">{{ number_format((($list->price * $list->qty) * (1 - ($list->discount / 100))) * (1 + ($list->ppn / 100))) }}</td>

			<td>{{$list->note}}</td>
		</tr>
		@endforeach
	</tbody>
</table>