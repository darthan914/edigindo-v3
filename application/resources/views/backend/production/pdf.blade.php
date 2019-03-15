<style type="text/css">
	.cell-detail > table
	{
		width: 100% !important;
		border-collapse: collapse;
	}
</style>

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
					<td>{!! $index->no_spk or '-' !!} </td>
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

			</tr>
		</thead>
		<tbody>
			@foreach($index->productions as $list)
				<tr style="background: #EDEDED">
					<td>{!! $list->name or '-' !!}</td>
					<td>{!! $list->quantity or '-' !!}</td>
					<td>{!! $list->divisions->name or '-' !!}</td>
					<td>{!! $list->source or '-' !!}</td>
					<td>{!! date('d F Y', strtotime($list->deadline)) !!}</td>
				</tr>
				<tr>
					<td colspan="5" class="cell-detail">{!! $list->detail or '-' !!}</td>
				</tr>
			@endforeach
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
				@if($index->sales->signature != '')
					<br >
					<img src="{!! $index->sales->signature !!}" height="100" align="absbottom">
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
