@if($item->paid_debt == 0 && $item->debt_price > 0)

        @include('crud::components.columns.select-editable' )

@endif