<style type="text/css">
	.cell-detail > table
	{
		width: 100% !important;
		border-collapse: collapse;
	}
</style>

<title>{!! $index->no_pr or '-' !!}</title>

<div style="font-size: 12px">
	@if($index->type == "PAYMENT")
	<h2 style="text-align: center;">PAYMENT REQUEST</h2>
	@else
	<h2 style="text-align: center;">PURCHASE REQUEST</h2>
	@endif

	<p style="text-align: right;">No PR: {!! $index->no_pr or '-' !!}</p>

	<table width="100%" border="0" cellpadding="10">
		<tr>
			<td width="50%">
				<table border="0" width="100%">
					<tr>
						<td>Nama</td>
						<td>:</td>
						<td>{!! $index->name !!}</td>
					</tr>
					<tr>
						<td>No. SPK</td>
						<td>:</td>
						<td>{!! $index->spk->spk ?? '-' !!}</td>
					</tr>
					<tr>
						<td>Tanggal Pesan</td>
						<td>:</td>
						<td>{!! $index->datetime_order !!}</td>
					</tr>
				</table>
			</td>
			<td width="50%">
				<table border="0" width="100%">
					<tr>
						<td>Deadline</td>
						<td>:</td>
						<td>{!! $index->deadline !!}</td>
					</tr>
					<tr>
						<td>Proyek</td>
						<td>:</td>
						<td>{!! $index->spk->name ?? '-' !!}</td>
					</tr>
					<tr>
						<td>Divisi</td>
						<td>:</td>
						<td>{!! $index->division !!}</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>

	<table width="100%" border="1" style="border-collapse: collapse;">
		<tr>
			<th>NAMA BARANG</th>
			<th width="5%">QTY</th>
			<th width="5%">SATUAN</th>
			<th width="5%">KETERANGAN</th>
			@if($index->type == "PAYMENT")
			<th width="5%">HARGA</th>
			@endif
		</tr>
		@foreach($index->prDetail()->where('confirm', 1)->get() as $list)
		<tr>
			<td>{!! $list->item !!}</td>
			<td>{!! $list->quantity !!}</td>
			<td>{!! $list->unit !!}</td>
			<td></td>
			@if($index->type == "PAYMENT")
			<td>{!! number_format( $list->value ) !!}</td>
			@endif
		</tr>
		@endforeach
	</table>

	<p>Barcode : {!! $index->barcode !!}</p>

	<table width="100%" border="0" style="border-collapse: collapse;">
		<tr>
			<td align="center" width="50%">
				Approval,
				<br>
				<br>
				<br>
				(____________________)
			</td>
			<td align="center" width="50%">
				Pemesan,
				<br>
				<br>
				<br>
				({!! $index->name !!})
			</td>
		</tr>
	</table>
</div>
