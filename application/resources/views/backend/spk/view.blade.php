<div class="container">
	<div class="row">
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-4">
					<b>SPK</b>
				</div>
				<div class="col-md-8">
					{{$index->no_spk}}
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
					<b>Main Division</b>
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
					{{$index->date_spk_readable}}
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
		<div class="col-md-4"><b>Do Transaction</b></div>
		<div class="col-md-8">{{($index->do_transaction ? 'Yes' : 'No')}}</div>
	</div>
	<div class="row">
		<div class="col-md-4"><b>Note</b></div>
		<div class="col-md-8">{{$index->note}}</div>
	</div>

	<div class="ln_solid"></div>

	<table class="table table-bordered">
		<tr>
			<th width="40%">Information</th>
			<th width="80%">Detail</th>
		</tr>
		@foreach($index->productions as $list)
		<tr>
			<td>
				<b>Name :</b> {{$list->name}} ({{$list->divisions->name}} | {{$list->source}})<br/>
				<b>Quantity :</b> {{number_format($list->quantity)}}<br/>
				<b>Modal :</b> {{number_format($list->hm)}} ({{number_format($list->quantity * $list->hm)}})<br/>
				@if(Auth::user()->can('editHE-spk'))
				<b>Expo :</b> {{number_format($list->he)}} ({{number_format($list->quantity * $list->he)}})<br/>
				@endif
				<b>Sell :</b> {{number_format($list->hj)}} ({{number_format($list->quantity * $list->hj)}})<br/>
				<b>Profitable : {{ ($list->profitable ? 'Yes' : 'No') }}</b>
			</td>
			<td>
				{!! $list->detail !!}
			</td>
		</tr>
		@endforeach
	</table>
</div>