<table class="table table-bordered" style="color: #73879c">

	@if($index->quantity > $index->totalPoQty && Auth::user()->can('addPo-pr') && ($index->purchasing_id == Auth::id() || Auth::user()->can('allPurchasing-pr') ))
		
	<tr>
		<th colspan="12">
			<button class="btn btn-primary btn-xs btn-block add-poProject" data-toggle="modal" data-target="#add-poProject" data-id="{{ $index->id }}">Add PO</button>
		</th>
	</tr>
	@endif

	<tr>
		<th>Quantity</th>
		<th>No PO</th>
		<th>Date</th>
		<th>Type</th>
		<th>Name Supplier</th>

		<th>No Rekening</th>
		<th>Value</th>
		<th>Check Audit</th>
		<th>Check Finance</th>
		<th>Note Audit</th>

		<th>Action</th>

		<th>Status</th>
	</tr>
		

	@foreach ($index->po as $list)
		
	<tr class="{{ ($list->status_received == 'CONFIRMED' ? 'alert-success' : ($list->status_received == 'COMPLAIN' || ($index->date_request < date('Y-m-d H:i:s') && $list->status_received == 'WAITING') ? 'alert-danger' : '')) }}">
		<td>{{ $list->quantity }}</td>
		<td>{{ $list->no_po }}</td>
		<td>{{ date('d/m/Y', strtotime($list->date_po)) }}</td>
		<td nowrap>{{ $list->type }}</td>
		<td nowrap>{{ $list->name_supplier }}</td>

		<td>{{ $list->no_rekening }}</td>
		<td nowrap>Rp. {{ number_format($list->value) }}</td>
		<td>
		@if(Auth::user()->can('checkAudit-pr'))
			<input type="checkbox" data-id="{{ $list->id }}" value="1" name="check_audit" {{ ($list->check_audit ? 'checked' : '') }}>
		@else
			@if($list->check_audit) <i class="fa fa-check" aria-hidden="true"></i> @endif
		@endif
		</td>
		<td>
		@if(Auth::user()->can('checkFinance-pr'))
			<input type="checkbox" data-id="{{ $list->id }}" value="1" name="check_finance" {{ ($list->check_finance ? 'checked' : '') }}>
		@else
			@if($list->check_finance) <i class="fa fa-check" aria-hidden="true"></i> @endif
		@endif
		</td>
		<td>
		@if(Auth::user()->can('noteAudit-pr'))
			<textarea class="note_audit form-control" data-id="{{ $list->id }}" name="note_audit">{{ $list->note_audit }}</textarea>
		@else
			{{ $list->note_audit }}
		@endif
		</td>
		<td>
		@if(Auth::user()->can('editPo-pr') && $list->check_audit == 0 && $list->check_finance == 0)
			<button class="btn btn-warning btn-xs btn-block edit-poProject" data-toggle="modal" data-target="#edit-poProject"
				data-id="{{ $list->id }}"
				data-quantity="{{ $list->quantity }}"
				data-no_po="{{ $list->no_po }}"
				data-date_po="{{ ($list->date_po ? date('d F Y', strtotime($list->date_po)) : '') }}"
				data-type="{{ $list->type }}"
				data-supplier_id="{{ ($supplier->where('no_rekening', $list->no_rekening)->first()->id ?? 0) }}"
				data-name_supplier="{{ $list->name_supplier }}"
				data-value="{{ $list->value }}"
			><i class="fa fa-pencil" aria-hidden="true"></i></button>
		@endif
		
		@if(Auth::user()->can('deletePo-pr') && $list->check_audit == 0 && $list->check_finance == 0)
			<button class="btn btn-danger btn-xs btn-block delete-po" data-toggle="modal" data-target="#delete-po" data-id="{{ $list->id }}"><i class="fa fa-trash" aria-hidden="true"></i></button>
		@endif
		</td>
		<td>
		@if($list->status_received == "CONFIRMED")
			Confirmed, Date Received : {{ date('d/m/Y', strtotime($index->date_received)) }}
		@elseif($list->status_received == "COMPLAIN")
			Complain, Reason : {{ $index->note_received }}
		@else
			Item on process
		@endif
		</td>
	</tr>
	@endforeach
</table>