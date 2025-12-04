<form action="{{ route('kapital.new.courier_deliveries.payment',['cd'=>$cd->id]) }}" method="POST">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    {!! implode("\n", $args_array) !!}
    <h3 class="repayment" style="@if(!$cd) display: none; @endif "
        id="pkg_amount_cap">{{ trans('front.pay_amount') }}</h3>
    <h3 class="repayment" style="@if(!$cd) display: none;@endif padding-bottom:20px;"
        id="pkg_amount_lbl">@if($cd) 3 AZN
        @endif</h3>
    <button class="btn btn-primary btn-sm" type='submit' id="pkg_pay_btn"
            @if( !$cd)disabled @endif>{{ trans('front.pay') }}</button>
</form>
