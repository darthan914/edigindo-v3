<style type="text/css">
	.cell-detail > table
	{
		width: 100% !important;
		border-collapse: collapse;
	}
	.detail-table table
	{
		width: 100% !important;
	}
</style>

<title>{!! str_replace('/', '-', $offer->no_document) !!}</title>

<div style="font-size: 12px;">
	@if($request->header == 'on')
	<div>
		<img src="{{asset('frontend/digindo-logo.png')}}" alt="DIGINDO" height="30" align="absbottom"><br/>
		<strong>PT DIGITAL INDONESIA</strong><br>
		Jl.Pangeran Tubagus Angke, Jakarta Barat – Indonesia<br>
		Telp. (021) 56950088 Fax. (021) 56982270<br>
		Komplek BNI blok TT no.23-23A<br>
		www.klikdigindo.com
	</div>
	<br />
	<br />
	@endif
	<div>
		<table width="100%" border="0" cellpadding="0">
		<tr>
			<td width="61%" valign="top">Kepada Yth,<br>
				<strong>
					@if(strtoupper($offer->pic->gender) == 'M')
						Bapak
					@elseif(strtoupper($offer->pic->gender) == 'F')
						Ibu
					@else
						Bapak/Ibu
					@endif
					{!! $offer->pic->fullname !!}
					<br>
					{!! $offer->company->name !!}
				</strong>
				<br>
					{!! $offer->address !!}
			</td>
			<td width="39%" align="right" valign="top" ><table border="0" cellpadding="2" >
			<tr>
				<td valign="middle"><strong>Date</strong></td>
				<td valign="middle">:</td>
				<td valign="middle">
						{{date('d F Y', strtotime($index->date))}}
				</td>
			</tr>
			<tr>
				<td valign="top"><strong>Handphone</strong></td>
				<td valign="top">:</td>
				<td valign="middle" >
					{!! $offer->pic->pic_phone !!}
					<br>
					{!! $offer->second_phone !!}
				</td>
			</tr>
			<tr>
				<td valign="middle"><strong>Fax</strong></td>
				<td valign="middle">:</td>
				<td valign="middle">
					{!! $offer->company->fax	!!}
				</td>
			</tr>
			<tr>
				<td valign="middle"><strong>Project</strong></td>
				<td valign="middle">:</td>
				<td valign="middle">
					{!! $offer->name !!}
				</td>
			</tr>
			@if($offer->ppn != 0)
			<tr>
				<td valign="middle"><strong>Doc. No.</strong></td>
				<td valign="middle">:</td>
				<td valign="middle" >
					<strong>{!! $offer->no_document !!}</strong>
				</td>
			</tr>
			@endif
			</table></td>
		</tr>
		<tr>
			<td colspan="2">
				<strong style="text-decoration: underline;">CONTRACT</strong><br/>
				<strong>{{ $index->no_contract }}</strong>
			</td>
		</tr>
		</table>
		<br />
		<br />

		@if($offer->total_price > 0)

			<div class="detail-table">
				@foreach($offer->offerList as $list)
					{!! $list->detail !!}
				@endforeach
			</div>
			
			

			<table width="100%" border="1" cellpadding="5" style="border:solid thin; border-collapse: collapse;">
				@if($request->option == 'total')
					<tr style="border:dotted thin">
						<td align="right"><strong>MATERIAL</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($offer->total_price * ( $index->material / 100) ) }}</td>
					</tr>
					<tr style="border:dotted thin">
						<td align="right"><strong>JASA</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($offer->total_price * ( $index->services / 100)) }}</td>
					</tr>
					<tr style="border:dotted thin">
						<td align="right"><strong>TOTAL</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($offer->total_price) }}</td>
					</tr>
					@if($offer->ppn)
						<tr style="border:dotted thin">
							<td align="right"><strong>PPN {!! $offer->ppn !!}%</strong></td>
							<td align="right" nowrap="nowrap">Rp. {{ number_format($offer->total_price * ($offer->ppn / 100)) }}</td>
						</tr>
						<tr style="border:dotted thin">
							<td align="right"><strong>GRAND TOTAL</strong></td>
							<td align="right" nowrap="nowrap">Rp. {{ number_format($offer->total_price * (1 + ($offer->ppn / 100))) }}</td>
						</tr>
					@endif
				@endif
			</table>

			Note:<br />
			{!! $index->note !!}
		@else
			<table width="100%" border="1" cellpadding="5" style="border:solid thin; border-collapse: collapse;">
				<tr style="background:orange;color:white;border:white">
					<th>Spesification</th>
					<th>Quantity</th>
					<th>Rate (Rp)</th>
					<th>Amount (Rp)</th>
				</tr>
				@foreach($offer->offerList as $list)
				<tr style="border:dotted thin">
					<td class="cell-detail">
						<strong><span style="text-decoration:underline">{!! $list->name !!}</span></strong>
						{!! $list->detail !!}
						@if($list->photo)
						<br/>
						<img src="{!! asset($list->photo) !!}" align="absbottom"  style="object-fit:contain;width: 200px">
						@endif
					</td>
					<td>{!! $list->quantity !!} {!! $list->units !!}</td>
					<td align="right" nowrap="nowrap">Rp. {{ number_format($list->price) }}</td>
					<td align="right" nowrap="nowrap">
					Rp. {{ number_format($list->price * $list->quantity) }}
					@if($request->option == 'choice')
						@if($offer->ppn)
							<br\>PPN ({!! $offer->ppn !!} %): Rp. {{ number_format(($list->price * $list->quantity) * (1 + ($offer->ppn / 100))) }}
						@endif
					@endif
					</td>
				</tr>
				@endforeach
				@if($request->option == 'total')
					<tr style="border:dotted thin">
						<td colspan="3" align="right"><strong>MATERIAL ( {{$index->material}} % )</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($offer->offerList()->sum(DB::raw('price * quantity'))  * ( $index->material / 100)) }}</td>
					</tr>
					<tr style="border:dotted thin">
						<td colspan="3" align="right"><strong>JASA ( {{$index->services}} % )</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($offer->offerList()->sum(DB::raw('price * quantity')) * ( $index->services / 100)) }}</td>
					</tr>
					<tr style="border:dotted thin">
						<td colspan="3" align="right"><strong>TOTAL</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($offer->offerList()->sum(DB::raw('price * quantity'))) }}</td>
					</tr>
					@if($offer->ppn)
						<tr style="border:dotted thin">
							<td colspan="3" align="right"><strong>PPN {!! $offer->ppn !!}%</strong></td>
							<td align="right" nowrap="nowrap">Rp. {{ number_format($offer->offerList()->sum(DB::raw('price * quantity')) * ($offer->ppn / 100)) }}</td>
						</tr>
						<tr style="border:dotted thin">
							<td colspan="3" align="right"><strong>GRAND TOTAL</strong></td>
							<td align="right" nowrap="nowrap">Rp. {{ number_format($offer->offerList()->sum(DB::raw('price * quantity')) * (1 + ($offer->ppn / 100))) }}</td>
						</tr>
					@endif
				@endif
				<tr style="border:dotted thin">
					<td colspan="4" class="cell-detail">
						Note:<br />
						{!! $index->note !!}
					</td>
				</tr>

			</table>
		@endif
			

		<table width="100%" border="0" cellpadding="0">
			<tr>
				<td width="33%" align="center">
					Hormat Kami,<br/>
					Direktur Marketing
				</td>
				<td width="33%" align="center">&nbsp;</td>
				<td width="34%" align="center">Client Approval,</td>
			</tr>
			<tr>
				<td height="61" align="center">
					@if($offer->sales->signature != '')
					<br >
					{{-- <img src="{{ asset($offer->sales->signature) }}" align="absbottom" style="object-fit:contain;height: 100px"> --}}
					@endif
				</td>
				<td align="center">&nbsp;</td>
				<td align="center">&nbsp;</td>
			</tr>
			<tr>
				<td align="center">
					({!! $index->director !!})<br/>
					({!! $offer->sales->no_ae !!}/{!! $offer->sales->nickname !!})
				</td>
				<td align="center">&nbsp;</td>
				<td align="center">({!! $index->client !!})</td>
			</tr>
		</table>
	</div>
</div>
