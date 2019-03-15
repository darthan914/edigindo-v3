<table class="table table-striped">

	@if(Auth::user()->can('create-invoice', $index))
		<tr><td colspan="9">
			<button class="btn btn-primary btn-xs btn-block add-document" data-toggle="modal" data-target="#add-document" data-id="{{ $index->id }}">Add Document</button>
		</td></tr>
	@endif

	
		<tr>
			
			<th nowrap="nowrap">No Inv</th>
			
			<th nowrap="nowrap">Send</th>

			<th nowrap="nowrap">Chk Fin</th>

			<th nowrap="nowrap">Date Cplt</th>

			<th nowrap="nowrap">Date Rec</th>

			<th nowrap="nowrap">Action</th>
		</tr>
	

	@foreach($index->invoices as $list)
		<tr>
			<td nowrap="nowrap">
				@if($list->no_invoice)
					<b>No Inv : </b>{{ $list->no_invoice }} <br/>
					<b>Val : </b>Rp. {{ number_format($list->value_invoice) }}<br/> 
					<b>Dt send : </b>{{ date('d-m-Y', strtotime($list->date_faktur)) }}

				
						@if(Auth::user()->can('update-invoice', $list))
							<br/>
							<button class="btn btn-xs btn-warning update-invoice" data-toggle="modal" data-target="#update-invoice" 
								data-id="{{ $list->id }}"
								data-no_invoice="{{ $list->no_invoice }}"
								data-value_invoice="{{ $list->value_invoice }}"
								data-date_faktur="{{ date('d F Y', strtotime($list->date_faktur)) }}"
							><i class="fa fa-edit" aria-hidden="true"></i> Edit</button>
							
						@endif

						@if(Auth::user()->can('undo-invoice', $list))
							<br/>
							<button class="btn btn-xs btn-default undo-invoice" data-toggle="modal" data-target="#undo-invoice" data-id="{{ $list->id }}"><i class="fa fa-undo" aria-hidden="true"></i> Undo</button>
						@endif

					</td>
				@elseif(Auth::user()->can('update-invoice', $list))
						<button class="btn btn-xs btn-primary btn-block update-invoice" data-toggle="modal" data-target="#update-invoice"
								data-id="{{ $list->id }}"
								data-no_invoice="{{ $list->no_invoice }}"
								data-value_invoice="{{ $list->value_invoice }}"
								data-date_faktur="{{ date('d F Y', strtotime($list->date_faktur)) }}"
							>Add Invoice</button>
				@endif
			</td>

			<td nowrap="nowrap">
				@if($list->no_sending)
					<b>No Send : </b>{{ $list->no_sending }}<br/>
					<b>Dt send : </b>{{ date('d-m-y H:i:s', strtotime($list->datetime_add_sending)) }}<br/>

					<br/>
					@if(Auth::user()->can('update-invoice', $list))
						<button class="btn btn-xs btn-warning add-send" data-toggle="modal" data-target="#add-send" 
							data-id="{{ $list->id }}"
							data-no_sending="{{ $list->no_sending }}"
						><i class="fa fa-edit" aria-hidden="true"></i> Edit</button>
					@endif

					<br/>
					@if(Auth::user()->can('undo-invoice', $list))
						<button class="btn btn-xs btn-default undo-send" data-toggle="modal" data-target="#undo-send" data-id="{{ $list->id }}"><i class="fa fa-undo" aria-hidden="true"></i></button>
						
					@endif
					
				@elseif(Auth::user()->can('update-invoice', $list))
						<button class="btn btn-xs btn-primary btn-block add-send" data-toggle="modal" data-target="#add-send"
							data-id="{{ $list->id }}"
							data-no_sending="{{ $list->no_sending }}"
						>Add Send</button>
				@endif
			</td>

			<td nowrap="nowrap">
				@if(Auth::user()->can('checkFinance-invoice', $list))
					<input type="checkbox" data-id="{{ $list->id }}" value="1" name="check_finance" {{ ($list->check_finance ? 'checked' : '') }}>
				@elseif($list->check_finance == 1)
					<i class="fa fa-check" aria-hidden="true"></i>
				@endif
			</td>

			<td nowrap="nowrap">
				@if($list->datetime_add_complete)
					{{ date('d-m-y H:i:s', strtotime($list->datetime_add_complete)) }}
					
					@if(Auth::user()->can('undo-invoice', $list))
						<br/>
						<button class="btn btn-xs btn-default undo-document" data-toggle="modal" data-target="#undo-document" data-id="{{ $list->id }}"><i class="fa fa-undo" aria-hidden="true"></i></button>
					@endif
					
				@elseif(Auth::user()->can('update-invoice', $list))
					<button class="btn btn-xs btn-primary btn-block redo-document" data-toggle="modal" data-target="#redo-document" data-id="{{ $list->id }}">Add Complete</button>
				@endif
			</td>

			<td nowrap="nowrap">
				@if($list->date_received)
					{{ date('d-m-y', strtotime($list->date_received)) }}
					
					@if(Auth::user()->can('undo-invoice', $list))
						<br/>
						<button class="btn btn-xs btn-default undo-received" data-toggle="modal" data-target="#undo-received" data-id="{{ $list->id }}"><i class="fa fa-undo" aria-hidden="true"></i></button>
					@endif
					
				@elseif(Auth::user()->can('update-invoice', $list))
						<button class="btn btn-xs btn-primary btn-block add-received" data-toggle="modal" data-target="#add-received"
							data-id="{{ $list->id }}"
							data-no_invoice="{{ $list->no_invoice }}"
							data-value_invoice="{{ $list->value_invoice }}"
							data-date_faktur="{{ date('d F Y', strtotime($list->date_faktur)) }}"
						>Add Received</button>
				@endif
			</td>

			<td nowrap="nowrap">
				@if(Auth::user()->can('delete-invoice', $list))
					<button class="btn btn-xs btn-danger delete-invoice" data-toggle="modal" data-target="#delete-invoice" data-id="{{ $list->id }}"><i class="fa fa-trash"></i></button>
				@endif
			</td>
		</tr>

	@endforeach
</table>