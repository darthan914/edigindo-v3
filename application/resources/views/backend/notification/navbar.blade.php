@forelse ($index as $list)
<li>
    <a href="{{ route('backend.notification.view', [ $list->id ]) }}">
        <span>
            <span><b>{{ strtoupper( $list->data['from'] )}}</b>  {{ ($list->read_at == null ? '[Unread]' : '' ) }}</span>
        </span>
        <span class="message">{!! $list->data['messages'] !!}</span>
    </a>
</li>
@empty
<li>
    <a href="#">
        No Notification
    </a>
</li>
@endforelse

<li>
    <div class="text-center">
        <a href="{{ route('backend.notification.index') }}">
            <strong>See All Notification</strong>
            <i class="fa fa-angle-right"></i>
        </a>
    </div>
</li>