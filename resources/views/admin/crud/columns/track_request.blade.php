@if($item->status == 16 || $item->status == 20)
    <a href="#!" class="btn btn-info btn-xs legitRipple text-white"
    data-ajax-request="{{ route('tracks.request', $item->id) }}">Request</a>
@endif
