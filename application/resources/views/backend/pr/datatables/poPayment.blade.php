<table class="table table-bordered" style="color: #73879c">

	@if($index->quantity > $index->totalPoQty && Auth::user()->can('addPo-pr') && ($index->purchasing_id == Auth::id() || Auth::user()->can('allPurchasing-pr') ))
		
	<tr>
		<th>
			<button class="btn btn-primary btn-xs btn-block add-poPayment" data-toggle="modal" data-target="#add-poPayment" data-id="{{ $index->id }}" data-value="{{ $index->value }}">Add Payment</button>
		</th>
	</tr>
	@endif

	@foreach ($index->po as $list)
	<tr>
		<td>Date Pay</td>
		<th>Check Audit</th>
		<th>Note Audit</th>
	</tr>
	
	<tr>
		
		<td>
			{{ date('d/m/Y', strtotime($list->date_po)) }}
			@if(Auth::user()->can('editPo-pr') && $list->check_audit == 0 && $list->check_finance == 0)
				<button class="btn btn-warning btn-xs btn-block edit-poPayment" data-toggle="modal" data-target="#edit-poPayment"
					data-id="{{ $list->id }}"
					data-date_po="{{ ($list->date_po ? date('d F Y', strtotime($list->date_po)) : '') }}"
					data-value="{{ $list->value }}"
				>
						<i class="fa fa-pencil" aria-hidden="true"></i>
				</button>
			@endif
			
		</td>

		<td>
		@if(Auth::user()->can('checkAudit-pr'))
			<input type="checkbox" data-id="{{ $list->id }}" value="1" name="check_audit" {{ ($list->check_audit ? 'checked' : '') }}>
		@else
			@if($list->check_audit) <i class="fa fa-check" aria-hidden="true"></i> @endif
		@endif
		</td>

		<td>
		@if(Auth::user()->can('noteAudit-pr'))
			<textarea class="note_audit form-control" data-id="{{ $list->id }}" name="note_audit">{{ $list->note_audit }}</textarea>
		@else
			{{ $list->note_audit }}
		@endif
		</td>
	</tr>
	@endforeach
</table>