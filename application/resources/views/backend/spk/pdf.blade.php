<style type="text/css">
	.cell-detail > table
	{
		width: 100% !important;
		border-collapse: collapse;
	}
</style>

<title>{!! $index->spk or '-' !!} - {!! $index->name or '-' !!}</title>

<div style="font-size: 12px">
	<img src="{{asset('frontend/digindo-logo.png')}}" alt="DIGINDO" height="30" align="absbottom">
	<table width="100%" border="0" cellpadding="10">
		<tr>
			<th colspan="2" align="center"><h2>SURAT PERINTAH KERJA</h2></th>
		</tr>
		<tr>
			<td width="50%" valign="top">
				<table width="100%" border="0" cellpadding="0">
					<tr>
						<td width="20%" valign="top">Proyek</td>
						<td width="2%" valign="top">:</td>
						<td width="78%">{!! $index->name or '-' !!} </td>
					</tr>
					<tr>
						<td width="20%" valign="top">Divisi</td>
						<td width="2%" valign="top">:</td>
						<td width="78%">{!! $index->divisions->name or '-' !!} </td>
					</tr>
					<tr>
						<td width="20%" valign="top">Perusahaan</td>
						<td valign="top">:</td>
						<td>{!! $index->companies->name or '-' !!}</td>
					</tr>
					<tr>
						<td width="20%" valign="top">Brand</td>
						<td valign="top">:</td>
						<td>{!! $index->brands->name or '-' !!}</td>
					</tr>
					@if($request->hide_client == 'off')
					<tr>
						<td width="20%" valign="top">PIC</td>
						<td valign="top">:</td>
						<td>{!! $index->pic->fullname or '-' !!}</td>
					</tr>
					<tr>
						<td width="20%" valign="top"></td>
						<td></td>
						<td></td>
					</tr>
					
					<tr>
						<td width="20%" valign="top">Alamat</td>
						<td valign="top">:</td>
						<td>{!! $index->address or '-' !!}</td>
					</tr>
					@endif
				</table>
			</td>
			<td width="50%" valign="top">
			<table width="100%" border="0" cellpadding="0">
				<tr>
					<td width="20%" valign="top">Tanggal</td>
					<td width="2%">:</td>
					<td width="78%">{{date('d F Y', strtotime($index->date_spk))}}</td>
				</tr>
				<tr>
					<td width="20%" valign="top">SPK</td>
					<td valign="top">:</td>
					<td>{!! $index->no_spk or '-' !!}</td>
				</tr>
				<tr>
					<td width="20%" valign="top">&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>

				<tr>
					<td width="20%" valign="top">Sales</td>
					<td valign="top">:</td>
					<td>{!! $index->sales->fullname or '-' !!}</td>
				</tr>
				<tr>
					<td>Sales Phone</td>
					<td valign="top">:</td>
					<td>{!! $index->sales->phone or '-' !!}</td>
				</tr>
				<tr>
					<td width="20%" valign="top">Note</td>
					<td valign="top">:</td>
					<td>{!! $index->note or '-' !!}</td>
				</tr>
			</table></td>
		</tr>
	</table>
	<br>

	<table width="100%" border="1" style="border-collapse: collapse;">
		<thead>
			<tr style="background: #FF8400;color: white;border: black thin solid;">
				<th>Nama</th>
				<th>Qty</th>
				<th>Divisi</th>
				<th>Source</th>
				<th>Deadline</th>
				@if($request->type == 'purchasing')
					<th>HM</th>
					<th>HJ</th>
				@endif
			</tr>
		</thead>
		<tbody>
			@php $total_hm = $total_hj = 0; @endphp
			@foreach($index->productions as $list)
				<tr style="background: #EDEDED">
					<td>{!! $list->name or '-' !!}</td>
					<td>{!! $list->quantity or '-' !!}</td>
					<td>{!! $list->divisions->name or '-' !!}</td>
					<td>{!! $list->source or '-' !!}</td>
					<td>{!! date('d F Y', strtotime($list->deadline)) !!}</td>
					@if($request->type == 'purchasing')
						<td rowspan="2" align="right" valign="top">
							Rp. {{number_format($list->hm)}}<br>
							Total: Rp. {{number_format($list->hm * $list->quantity)}}
							@php $total_hm += $list->hm * $list->quantity; @endphp
						</td>
						<td rowspan="2" align="right" valign="top">
							Rp. {{number_format($list->hj)}}<br>
							Total: Rp. {{number_format($list->hj * $list->quantity)}}
							@php $total_hj += $list->hj * $list->quantity; @endphp
						</td>
					@endif
				</tr>
				<tr>
					<td colspan="5" class="cell-detail">{!! $list->detail or '-' !!}</td>
				</tr>
			@endforeach
				@if($request->type == 'purchasing')
					<tr>
						<td colspan="5" align="right">Barang (92%)</td>
						<td align="right">Rp. {{ number_format($total_hm * 0.92) }}</td>
						
						<td align="right">Rp. {{ number_format($total_hj * 0.92) }}</td>
						
					</tr>
					<tr>
						<td colspan="5" align="right">Jasa (8%)</td>
						<td align="right">Rp. {{ number_format($total_hm * 0.08) }}</td>
						
						<td align="right">Rp. {{ number_format($total_hj * 0.08) }}</td>
						
					</tr>
					<tr>
						<td colspan="5" align="right">Total</td>
						<td align="right">Rp. {{ number_format($total_hm) }}</td>
						
						<td align="right">Rp. {{ number_format($total_hj) }}</td>
						
					</tr>
					<tr>
						<td colspan="5" align="right">PPN</td>
						<td align="right">{!! $index->ppn !!}%</td>
						<td align="right">Rp. {{number_format($total_hj * (1 + ($index->ppn / 100)))}}</td>
					</tr>
				@endif
		</tbody>
	</table>

	<table width="100%" border="0" cellpadding="0" class="footer">
		<tr>
			<td width="33%" align="center">TTD,</td>
			<td width="33%" align="center">&nbsp;</td>
			<td width="34%" align="center"></td>
		</tr>
		<tr>
			<td height="61" align="center">
				@if($index->sales->signature)
					<br >
					<img src="{{ asset($index->sales->signature) }}" height="100" align="absbottom">
				@endif
			</td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
		</tr>
		<tr>
			<td align="center">({!! $index->sales->fullname or '-' !!})</td>
			<td align="center">&nbsp;</td>
			<td align="center"></td>
		</tr>
	</table>
</div>
