<style type="text/css">
.tg  {
	border-collapse:collapse;
	border-spacing:0;
}
.tg td{
	font-family:Arial, sans-serif;
	font-size:14px;
	padding:3px 3px;
	border-style:solid;
	border-width:1px;
	overflow:hidden;
	word-break:normal;
	border-color:black;
	height: 35px;
}
.tg th{
	font-family:Arial, sans-serif;
	font-size:14px;
	font-weight:normal;
	padding:3px 3px;
	border-style:solid;
	border-width:1px;
	overflow:hidden;
	word-break:normal;
	border-color:black;
	height: 35px;
}
.tg .tg-yellow{
	font-weight:bold;
	background-color:#f8ff00;
	color:#000000;
	border-color:#000000;
	text-align:left;
	vertical-align:middle;
	height: 25px;
}
.tg .tg-plain{
	border-color:#000000;
	text-align:left;
	vertical-align:middle
}
.tg .tg-orange{
	background-color:#ffcb2f;
	border-color:#000000;
	text-align:left;
	vertical-align:middle;
	font-weight:bold;
}

.break-page { 
	border: thin solid black;
	margin-bottom: 20px !important;
    box-shadow: 5px 10px #888888;
}


@media print {
	.break-page { 
		page-break-before: always;
		border: none !important;
		margin-bottom: inherit !important;
		box-shadow: none !important;
	}
}
</style>

@forelse($collect as $list)

<div style="width: 210mm; height: 138mm; overflow: hidden; margin: auto;" class="break-page">
	<table class="tg" style="width: 100%;border: thick solid;">
		<colgroup>
			<col style="width: 37px">
			<col style="width: 180px">
			<col style="width: 380px">
			<col style="width: 265px">
		</colgroup>
		<tr>
			<th class="tg-plain" colspan="2">SPK NO : {{ $request->spk }}</th>
			<th class="tg-plain" rowspan="2" style="border-right: none;"><img src="{{asset('frontend/label.png')}}" style="object-fit:contain;width: 100%"></th>
			<th class="tg-plain" rowspan="2" style="border-left: none;text-align: center;">
				@if($request->with_logo)
				<img src="{{asset('frontend/digindo-logo.png')}}" style="object-fit:contain;width: 70%">
				@endif
			</th>
		</tr>
		<tr>
			<td class="tg-plain" colspan="2">PO NO : {{ $request->po }}</td>
		</tr>
		<tr>
			<td class="tg-yellow">NO</td>
			<td class="tg-yellow">DESKRIPSI</td>
			<td class="tg-yellow" colspan="2">KETERANGAN</td>
		</tr>
		<tr>
			<td class="tg-plain">1</td>
			<td class="tg-plain">NAMA PROJECT</td>
			<td class="tg-orange" colspan="2">{{ $request->name }} \ {{ $list['asal'] }}</td>
		</tr>
		<tr>
			<td class="tg-plain">2</td>
			<td class="tg-plain">JUMLAH</td>
			<td class="tg-plain" style="border-right: none;">{{ $list['jumlah_part'] }} {{ $list['unit'] }} DARI TOTAL</td>
			<td class="tg-plain" style="border-left: none;">{{ $list['jumlah'] }} {{ $list['unit'] }}</td>
		</tr>
		<tr>
			<td class="tg-plain" rowspan="3">3</td>
			<td class="tg-plain" rowspan="3">ALAMAT PENGIRIMAN</td>
			<td class="tg-plain" colspan="2" style="border-bottom: none;">{{ $list['nama_toko'] }}</td>
		</tr>
		<tr style="height: 84px;">
			<td class="tg-plain" colspan="2">{{ $list['alamat'] }}</td>
		</tr>
		<tr>
			<td class="tg-plain">{{ $list['nama_penerima'] }}</td>
			<td class="tg-plain">Telp : {{ $list['telpon'] }}</td>
		</tr>
		<tr style="height: 125px;">
			<td class="tg-plain">4</td>
			<td class="tg-plain">JENIS BARANG</td>
			<td class="tg-plain" style="border-top: none;border-right: none;text-align: center">@if($image)<img src="{{ asset($image) }}" style="object-fit: contain;width: 100%;height: 158px;">@endif</td>
			<td class="tg-plain" style="border-top: none;border-left: none;">{{ $request->image_name }}</td>
		</tr>
	</table>
</div>
@empty
<p>No Data</p>

@endforelse
