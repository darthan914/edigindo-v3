<ul>
	@foreach($index->estimator_details as $list)
	<li style="padding-bottom: 1em;">
		<b>Name</b> : {{$list->item}}<br/>
		<b>Value</b> : Rp. {{number_format($list->value)}}<br/>
		<b>Note</b> : {{$list->note}}
	</li>
	@endforeach
</ul>
