@include('crud::components.columns.select-editable' )
@if(!$item->filial)
    &nbsp;&nbsp;
    <a href="#!" class="btn btn-warning btn-xs legitRipple text-white"
    data-ajax-request="{{ route('tracks.auto_filial', $item->id) }}">Auto</a>
@endif
