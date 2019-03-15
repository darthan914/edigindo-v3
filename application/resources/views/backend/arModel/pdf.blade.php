<style type="text/css">
	*{
		width: 100%;
		padding: 0;
	}
</style>

<title>{!! $index->name !!}</title>

<div style="font-size: 12px; ">
	<div style="text-align: center;font-size: 20px">
		<p><b>{!! $index->name !!}</b></p>
	</div>

	<div style="text-align: center;">
		<img src="{{asset('frontend/ar.png')}}" style="width: 20cm; height: 20cm;">
	</div>

	<div style="position: relative;">
		<div style="text-align: center;font-size: 20px;float: left;width: 50%;">
			<p><b>Scan Here!! ----></b></p>
		</div>

		<div style="text-align: center;float: left;width: 50%;">
			<img src="data:image/png;base64, {!! base64_encode(QRCode::format('png')->size(500)->errorCorrection('H')->generate($index->token)) !!} " style="width: 5cm; height: 5cm;">
		</div>
	</div>

	
</div>
