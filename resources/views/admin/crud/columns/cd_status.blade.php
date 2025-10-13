@if($item->courier_delivery)
    {{ $item->courier_delivery->statusWithLabel }} 
    @if($item->courier_delivery->not_delivered_status)
       ( {{ $item->courier_delivery->notDeliveredStatusWithLabel }} )
    @endif
    <a href="{{ route('courier_deliveries.index').'?id='.$item->courier_delivery->id }}" target="_blank"><i class="icon-arrow-right14"></i></a>
@endif
