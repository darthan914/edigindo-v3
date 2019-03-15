<!DOCTYPE html>
<html>
<head>
	<title>Comment</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-4 text-right">
				Name :
			</div>
			<div class="col-md-4 text-right">
				{!! $store->fullname !!}
			</div>
			<div class="col-md-4 text-right">
				Phone :
			</div>
			<div class="col-md-4 text-right">
				{!! $store->phone !!}
			</div>
			<div class="col-md-4 text-right">
				Email :
			</div>
			<div class="col-md-4 text-right">
				{!! $store->email !!}
			</div>
			<div class="col-md-4 text-right">
				Message :
			</div>
			<div class="col-md-4 text-right">
				{!! $store->messages !!}
			</div>

			<div class="col-md-4 text-right">
				Attachment :
			</div>
			<div class="col-md-4 text-right">
				<a href="{{ asset($store->attachment) }}">Download</a>
			</div>
		</div>
	</div>
</body>
</html>