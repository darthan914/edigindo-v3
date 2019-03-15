@foreach($preview as $list)
<div style="width: 5em; height: 5em;background-image: url('{{ asset($list->image_preview) }}');background-size: cover;background-origin: center;display: inline-block;" data-image_preview="{{ asset($list->image_preview) }}" data-toggle="modal" data-target="#preview-designRequest" class="preview-designRequest">
</div>
@endforeach