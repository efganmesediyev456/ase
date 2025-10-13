@if($item->length >= 100 || $item->width >= 100 || $item->height >= 100)
<b style="color:red">{{$item->full_size}}</b>
@else
{{$item->full_size}}
@endif
