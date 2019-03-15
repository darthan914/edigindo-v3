@extends('backend.layout.master')

@section('title')
	Inbox
@endsection

@section('script')

@endsection

@section('content')

	<h1>Inbox</h1>
	<div class="x_panel">
		<form class="form-horizontal form-label-left" action="" method="get" enctype="multipart/form-data">

			<div class="form-group">
				<label for="name" class="control-label col-md-3 col-sm-3 col-xs-12">Nama
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="name" class="form-control" readonly value="{{ $index->name }}">
				</div>
			</div>
			<div class="form-group">
				<label for="email" class="control-label col-md-3 col-sm-3 col-xs-12">Email
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="email" class="form-control" readonly value="{{ $index->email }}">
				</div>
			</div>
			<div class="form-group">
				<label for="phone" class="control-label col-md-3 col-sm-3 col-xs-12">Phone
				</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<input type="text" id="phone" class="form-control" readonly value="{{ $index->phone }}">
				</div>
			</div>
			<div class="form-group">
				<label for="messages" class="control-label col-md-3 col-sm-3 col-xs-12">Short Description</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
					<textarea class="form-control" id="messages" readonly rows="5">{{ $index->messages }}</textarea>
				</div>
			</div>
			
			<div class="ln_solid"></div>
			<div class="form-group">
				<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
					<a class="btn btn-primary" href="{{ route('admin.inbox') }}">Kembali</a>
					<a href="{{ route('admin.inbox.delete', ['id' => $index->id]) }}" class="btn btn-danger" onclick="return confirm('Hapus Data?')">Hapus <i class="fa fa-trash"></i></a>
				</div>
			</div>

		</form>
	</div>
	

@endsection