@php
	$sum_price = $index->account_banking_details()->sum(DB::raw('price'));
	$discount = 0;
	$total = $sum_price - $discount;
	$sum_ppn = $index->account_banking_details()->sum(DB::raw('price * (ppn /100)'));
@endphp

<style type="text/css">
	*{
		font-size: 14px;
	}

	table, th, td {
		border: none;
	}

	table.collapse
	{
		border-collapse: collapse;
	}

	table.full
	{
		width: 100% !important;
	}

	.bordered *, table.bordered *
	{
		border: 1px solid black !important;
	}

	span.tab1
	{
		display: inline-block;
		width: 6em;
	}

	.ln_solid {
	    border-top: 1px solid black;
	    color: #fff;
	    background-color: #fff;
	    height: 1px;
	    margin: 0px 0;
	}

</style>

<title>{{ date('d/m/Y', strtotime($index->date)) }}</title>

<div>
	<table class="full collapse">
		<tr>
			<td width="67%" style="text-align: center;"><b>PERBANKAN</b></td>
			<td width="33%" class="bordered"><span class="tab1">Tanggal</span> {{ date('d/m/Y', strtotime($index->date)) }}</td>
		</tr>
	</table>
	
	<p>
		@if($sum_ppn > 0)
		PT. DIGITAL INDONESIA<br/>
		@endif
		Kav. BNI 46 Blok TT No. 23 RT 008 RW 004, Wijaya Kusuma Grogol Petamburan<br/>
		Jakarta Barat, DKI Jakarta 11460
		@if($sum_ppn > 0)
			<br/>
			Telp : 021-56950088<br/>
			Fax : 021-56982270
		@endif
	</p>

	<br/>
	
	<table class="full collapse">
		<tr class="bordered">
			<th colspan="2">HARGA SATUAN</th>
			<th width="">HARGA</th>
		</tr>
		@foreach($index->account_banking_details as $list)
		<tr class="bordered">
			<td colspan="2" style="">{{ $list->account_lists->account_name }}</td>
			<td style="text-align: right;">Rp. {{ number_format($list->price, 2) }}</td>
		</tr>
		@endforeach
		<tr>
			<td rowspan="5" colspan="1" valign="top">
				Syarat Pembayaran :  {{ $request->syarat_pembayaran }}<br/>
				

				Terbilang : {{ ucwords(terbilang($total + $sum_ppn)) }}
			</td>
			<td>Jumlah</td>
			<td style="text-align: right;">Rp. {{ number_format($sum_price, 2) }}</td>
		</tr>
		@if($sum_ppn > 0)
		<tr>
			<td>PPN</td>
			<td style="text-align: right;">Rp. {{ number_format($sum_ppn, 2) }}</td>
		</tr>
		<tr>
			<td>Total</td>
			<td style="text-align: right;">Rp. {{ number_format($total + $sum_ppn, 2) }}</td>
		</tr>
		@endif
	</table>
	<br/>
	<table class="collapse full">
		<tr>
			<td width="67%" rowspan="3">
				@if($sum_ppn > 0)
				NOTE:<br/>
				TRANSFER ke Rek. 528 030 3331<br/>
				BCA Cab. Jelambar<br/>
				a/n PT. DIGITAL INDONESIA<br/>
				
				@else
				NOTE:<br/>
				TRANSFER ke Rek. 528 033 2625<br/>
				BCA Cab. Jelambar<br/>
				a/n Hirman<br/>

				@endif
				<br/>
				Mohon menncatumkan berita No. Invoice yang dilunasi
			</td>
			<td width="33%" style="text-align: center;">
				@if($sum_ppn > 0)
				PT. DIGITAL INDONESIA
				@else
				Hormat Kami,
				@endif
			</td>
		</tr>
		<tr>
			<td style="height: 4em">&nbsp;</td>
		</tr>
		<tr>
			<td style="text-align: center;">
				HIRMAN
				<div class="ln_solid"></div>
				@if($sum_ppn > 0)
				DIREKTUR
				@endif
			</td>
		</tr>
	</table>
</div>