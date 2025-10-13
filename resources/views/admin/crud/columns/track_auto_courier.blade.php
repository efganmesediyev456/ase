@include('crud::components.columns.select-editable' )
@if(!$item->courier_delivery)
    &nbsp;&nbsp;
    <a href="#!" class="btn btn-warning btn-xs legitRipple text-white"
    data-ajax-request="{{ route('tracks.auto_courier', $item->id) }}">Auto</a>
@endif
