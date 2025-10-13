@if(
     (!Auth::user()->can('change-post_terminal') && $item->broker_paid_by == 'POST_TERMINAL')
    )
    {{ $item->paid_with_label }}

@else
    @include('crud::components.columns.select-editable' )
    @if($item->paid_broker && $item->broker_price > 0)
        {{$item->broker_price }} AZN
    @endif
@endif