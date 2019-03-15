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

<title>{!! str_replace('/', '-', $index->no_document) !!}</title>

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
					@if(strtoupper($index->pic->gender) == 'M')
						Bapak
					@elseif(strtoupper($index->pic->gender) == 'F')
						Ibu
					@else
						Bapak/Ibu
					@endif
					{!! $index->pic->fullname !!}
					<br>
					{!! $index->companies->name !!}
				</strong>
				<br>
					{!! $index->address !!}
			</td>
			<td width="39%" align="right" valign="top" ><table border="0" cellpadding="2" >
			<tr>
				<td valign="middle"><strong>Date</strong></td>
				<td valign="middle">:</td>
				<td valign="middle">
						{{date('d F Y', strtotime($index->date_offer))}}
				</td>
			</tr>
			<tr>
				<td valign="top"><strong>Handphone</strong></td>
				<td valign="top">:</td>
				<td valign="middle" >
					{!! $index->pic->pic_phone !!}
					<br>
					{!! $index->additional_phone !!}
				</td>
			</tr>
			<tr>
				<td valign="middle"><strong>Fax</strong></td>
				<td valign="middle">:</td>
				<td valign="middle">
					{!! $index->companies->fax	!!}
				</td>
			</tr>
			<tr>
				<td valign="middle"><strong>Project</strong></td>
				<td valign="middle">:</td>
				<td valign="middle">
					{!! $index->name !!}
				</td>
			</tr>
			@if($index->ppn != 0)
			<tr>
				<td valign="middle"><strong>Doc. No.</strong></td>
				<td valign="middle">:</td>
				<td valign="middle" >
					<strong>{!! $index->no_document !!}</strong>
				</td>
			</tr>
			@endif
			</table></td>
		</tr>
		</table>
		<br />
		<br />

		@if($index->total_price > 0)

			<div class="detail-table">
				@foreach($index->offer_details as $list)
					{!! $list->detail !!}
				@endforeach
			</div>
			
			

			<table width="100%" border="1" cellpadding="5" style="border:solid thin; border-collapse: collapse;">
				@if($request->option == 'total')
					@if($request->expo == 'on')
					<tr style="border:dotted thin">
						<td align="right"><strong>BARANG (92%)</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($index->total_price * 0.92) }}</td>
					</tr>
					<tr style="border:dotted thin">
						<td align="right"><strong>JASA (8%)</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($index->total_price * 0.08) }}</td>
					</tr>
					@endif
					<tr style="border:dotted thin">
						<td align="right"><strong>TOTAL</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($index->total_price) }}</td>
					</tr>
					@if($index->ppn)
						<tr style="border:dotted thin">
							<td align="right"><strong>PPN {!! $index->ppn !!}%</strong></td>
							<td align="right" nowrap="nowrap">Rp. {{ number_format($index->total_price * ($index->ppn / 100)) }}</td>
						</tr>
						<tr style="border:dotted thin">
							<td align="right"><strong>GRAND TOTAL</strong></td>
							<td align="right" nowrap="nowrap">Rp. {{ number_format($index->total_price * (1 + ($index->ppn / 100))) }}</td>
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
				@foreach($index->offer_details as $list)
				<tr style="border:dotted thin">
					<td class="cell-detail">
						<strong><span style="text-decoration:underline">{!! $list->name !!}</span></strong>
						{!! $list->detail !!}
						@if($list->photo)
						<br/>
						<img src="{!! asset($list->photo) !!}" align="absbottom" style="object-fit:contain;width: 200px">
						@endif
					</td>
					<td>{!! $list->quantity !!} {!! $list->unit !!}</td>
					<td align="right" nowrap="nowrap">Rp. {{ number_format($list->value) }}</td>
					<td align="right" nowrap="nowrap">
					Rp. {{ number_format($list->value * $list->quantity) }}
					@if($request->option == 'choice')
						@if($index->ppn)
							<br\>PPN ({!! $index->ppn !!} %): Rp. {{ number_format(($list->value * $list->quantity) * (1 + ($index->ppn / 100))) }}
						@endif
					@endif
					</td>
				</tr>
				@endforeach
				@if($request->option == 'total')
					@if($request->expo == 'on')
					<tr style="border:dotted thin">
						<td colspan="3" align="right"><strong>BARANG (92%)</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($index->offer_details()->sum(DB::raw('value * quantity')) * 0.92) }}</td>
					</tr>
					<tr style="border:dotted thin">
						<td colspan="3" align="right"><strong>JASA (8%)</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($index->offer_details()->sum(DB::raw('value * quantity')) * 0.08) }}</td>
					</tr>
					@endif
					<tr style="border:dotted thin">
						<td colspan="3" align="right"><strong>TOTAL</strong></td>
						<td align="right" nowrap="nowrap">Rp. {{ number_format($index->offer_details()->sum(DB::raw('value * quantity'))) }}</td>
					</tr>
					@if($index->ppn)
						<tr style="border:dotted thin">
							<td colspan="3" align="right"><strong>PPN {!! $index->ppn !!}%</strong></td>
							<td align="right" nowrap="nowrap">Rp. {{ number_format($index->offer_details()->sum(DB::raw('value * quantity')) * ($index->ppn / 100)) }}</td>
						</tr>
						<tr style="border:dotted thin">
							<td colspan="3" align="right"><strong>GRAND TOTAL</strong></td>
							<td align="right" nowrap="nowrap">Rp. {{ number_format($index->offer_details()->sum(DB::raw('value * quantity')) * (1 + ($index->ppn / 100))) }}</td>
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
				<td width="33%" align="center">Hormat Kami,</td>
				<td width="33%" align="center">&nbsp;</td>
				<td width="34%" align="center">Client Approval,</td>
			</tr>
			<tr>
				<td height="61" align="center">
					@if($index->sales->signature != '')
					<br >
					<img src="{{ asset($index->sales->signature) }}" align="absbottom"  style="object-fit:contain;height: 100px">
					@endif
				</td>
				<td align="center">&nbsp;</td>
				<td align="center">&nbsp;</td>
			</tr>
			<tr>
				<td align="center">({!! $index->sales->fullname !!})</td>
				<td align="center">&nbsp;</td>
				<td align="center">({!! $index->pic->fullname !!})</td>
			</tr>
		</table>
	</div>
</div>
