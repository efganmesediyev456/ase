@if($item->debt_price > 0)
    @if(!Auth::user()->can('change-post_terminal'))
        {{ $item->paid_debt_att_with_label }}
    @else
        @include('crud::components.columns.select-editable' )

    @endif

@endif