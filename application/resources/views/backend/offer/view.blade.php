<div class="container">
	<div class="row">
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-4">
					<b>No Document</b>
				</div>
				<div class="col-md-8">
					{{$index->no_document}}
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<b>Name</b>
				</div>
				<div class="col-md-8">
					{{$index->name}}
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<b>Division</b>
				</div>
				<div class="col-md-8">
					{{$index->divisions->name}}
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<b>Sales</b>
				</div>
				<div class="col-md-8">
					{{$index->sales->fullname}}
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<b>Date</b>
				</div>
				<div class="col-md-8">
					{{$index->date_offer_readable}}
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-4">
					<b>Company</b>
				</div>
				<div class="col-md-8">
					{{$index->companies->name}}
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<b>Brand</b>
				</div>
				<div class="col-md-8">
					{{$index->brands->name ?? ''}}
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<b>Address</b>
				</div>
				<div class="col-md-8">
					{{$index->address}}
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<b>PIC</b>
				</div>
				<div class="col-md-8">
					{{$index->pic->fullname}}
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<b>Additional Phone PIC</b>
				</div>
				<div class="col-md-8">
					{{$index->additional_phone}}
				</div>
			</div>
		</div>
	</div>

	<div class="ln_solid"></div>

	<div class="row">
		<div class="col-md-4"><b>PPn</b></div>
		<div class="col-md-8">{{($index->ppn ? '10%' : '0%')}}</div>
	</div>
	<div class="row">
		<div class="col-md-4"><b>Note</b></div>
		<div class="col-md-8">{!! $index->note !!}</div>
	</div>

	@if(in_array($index->division_id, getConfigValue('division_expo', true)))
	<div class="row">
		<div class="col-md-4"><b>Total Price</b></div>
		<div class="col-md-8">Rp. {{ number_format($index->total_price)}}</div>
	</div>
	@endif

	<div class="ln_solid"></div>

	<table class="table table-bordered">
		<tr>
			<th width="30%">Information</th>
			<th width="50%">Detail</th>
			<th width="20%">Status</th>
		</tr>
		@foreach($index->offer_details as $list)
		<tr>
			<td>
				<b>Name :</b> {{$list->name}}<br/>
				@if(!in_array($index->division_id, getConfigValue('division_expo', true)))
				<b>Quantity :</b> {{number_format($list->quantity)}} {{ $list->unit }}<br/>
				<b>Price :</b> {{number_format($list->value)}}<br/>
				@endif

			</td>
			<td>
				{!! $list->detail !!}
				@if($list->photo)
				<br/>
				<img src="{{ asset($index->photo) }})" style="width: 75px; height: 75px;object-fit: contain;">
				@endif
			</td>
			<td>
				@if (ucwords($list->status) == 'SUCCESS')
	                <strong>Success</strong>
	            @elseif (ucwords($list->status) == 'CANCEL') 
	                <strong>Cancel</strong>

	                </br>
	                {{$list->note_other}}
	            @elseif (ucwords($list->status) == 'FAILED') 
	                <strong>Failed</strong>

	                </br>

	                @if (ucwords($list->reason) === 'PRICING') 
	                    Failed Because Pricing<br>
	                    {{$list->note_other}}
	                @elseif (ucwords($list->reason) === 'TIMELINE') 
	                    Failed Because Timeline<br>
	                    {{$list->note_other}}
	                @else 
	                    {{$list->note_other}}
	                @endif
	            @else 
	                <strong>Waiting</strong>
	            @endif
			</td>
		</tr>
		@endforeach
	</table>
</div>