<div class="continer-fluid">

	<div class="row">
		<div class="col-md-2 col-xs-2 col-sm-2 text-right">
			Asal :
		</div>
		<div class="col-md-10 col-xs-10 col-sm-10">
			({{ $index->name }} - {{ $index->user_phone }})<br/>
			Ambil  :  {{ $index->get_from }}<br/>
			Dibuat :  {{ date('d M Y H:i:s', strtotime($index->created_at)) }}<br/>
			Tiba   :  {{ date('d M Y H:i:s', strtotime($index->datetime_send)) }}
		</div>
	</div>
	<div class="row">
		<div class="col-md-2 col-xs-2 col-sm-2 text-right">
			Tujuan :
		</div>
		<div class="col-md-10 col-xs-10 col-sm-10">
			({{ $index->pic_name }} - {{ $index->company }})<br/>
			{{ $index->pic_phone }}<br/>
			{{ $index->address }}<br/>
		</div>
	</div>
	<div class="row">
		<div class="col-md-2 col-xs-2 col-sm-2 text-right">
			Peta :
		</div>
		<div class="col-md-10 col-xs-10 col-sm-10">
			{{-- <img src="https://maps.googleapis.com/maps/api/staticmap?center={{$index->latitude}},{{$index->longitude}}&zoom=15&size=600x300&maptype=roadmap&key={{ env('GOOGLE_MAPS_API') }}&markers=color:red%7Clabel:Location%7C{{$index->latitude}},{{$index->longitude}}" style="width: 32em" /> --}}

			<img src="https://api.tomtom.com/map/1/staticimage?layer=basic&style=main&format=png&zoom=16&center={{$index->longitude}},{{$index->latitude}}&width=1024&height=512&view=Unified&key={{ env('TOMTOM_MAPS_API') }}" style="width: 32em" />
		</div>
	</div>
	<div class="row">
		<div class="col-md-2 col-xs-2 col-sm-2 text-right">
			Keterangan :
		</div>
		<div class="col-md-10 col-xs-10 col-sm-10">
			{{ $index->note }}
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 col-xs-12 col-sm-12 text-right">
			Barang :
		</div>
		<div class="col-md-12 col-xs-12 col-sm-12">
			{!! $index->detail !!}
		</div>
	</div>
</div>