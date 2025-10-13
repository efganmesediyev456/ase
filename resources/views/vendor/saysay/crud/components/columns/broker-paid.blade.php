@if(!$item->paid_broker)
    @if(
     (!Auth::user()->can('change-post_terminal') && $item->broker_paid_by == 'POST_TERMINAL')
    )
        {{ $item->paid_with_label }}
    @else
        @include('crud::components.columns.select-editable' )

    @endif

@endif