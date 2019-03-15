<h2>PIC</h2>
<ul>
	@foreach($index->pic as $list)
	<li>{{$list->fullname}} | Phone : {{$list->phone}} | Email : {{$list->email}}</li>
	@endforeach
</ul>

<h2>Brand</h2>
<ul>
	@foreach($index->brands as $list)
	<li>{{$list->name}}</li>
	@endforeach
</ul>

<h2>Address</h2>
<ul>
	@foreach($index->addresses as $list)
	<li>{{$list->address}}</li>
	@endforeach
</ul>