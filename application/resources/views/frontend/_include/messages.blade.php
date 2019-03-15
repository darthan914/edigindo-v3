@if (Session::has('success'))

	<div class="alert alert-success fade in" role="alert">
		<strong>Success :</strong> {{ Session::get('success') }}
	</div>


@endif

@if (Session::has('failed'))


	<div class="alert alert-danger fade in" role="alert">
		<strong>Failed :</strong> {{ Session::get('failed') }}
	</div>

@endif

@if (Session::has('info'))


	<div class="alert alert-info fade in" role="alert">
		<strong>Info :</strong> {{ Session::get('info') }}
	</div>


@endif

@if (Session::has('warning'))


	<div class="alert alert-warning fade in" role="alert">
		<strong>Warning :</strong> {{ Session::get('warning') }}
	</div>


@endif