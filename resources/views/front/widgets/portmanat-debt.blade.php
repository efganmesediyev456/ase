
{{--@if((auth()->check() && auth()->user()->id == 31548))--}}
    <form action="{{ route('kapital.new.payment','track_debt') }}" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="item_id" value="{{ $track->id }}">
        <h3 class="repayment" style="@if(!$cd && !$track) display: none; @endif padding: 10px 5px 10px 10px;margin: 0px;"
            id="pkg_amount_cap">{{ trans('front.debt_price') }}</h3>
        <h3 class="repayment" style="@if(!$cd && !$track) display: none;@endif padding: 0px 15px 10px 5px;margin-top: 0px"
            id="pkg_amount_lbl">@if($cd)
                {{$cd->debt_price}} AZN
            @endif @if($track)
                {{$track->debt_price}} AZN
            @endif</h3>
        <button class="btn btn-primary btn-sm" type='submit' id="pkg_pay_btn"
                @if( !$cd && !$track)disabled @endif>{{ trans('front.pay') }}</button>
    </form>
{{--@endif--}}

{{--<form action="" method="POST" id="portmanat_payment_form" data-action-local="{{ trim($action_local_adr) }}"--}}
{{--      data-action-portmanat="{{ trim($action_portmanat_adr) }}" "@if($cd || $track)--}}
{{--    style="display: flex;"--}}
{{--@endif>--}}
{{--{!! implode("\n", $args_array) !!}--}}
{{--<h3 class="repayment" style="@if(!$cd && !$track) display: none; @endif padding: 10px 5px 10px 10px"--}}
{{--    id="pkg_amount_cap">{{ trans('front.pay_amount') }}</h3> <h3 class="repayment"--}}
{{--                                                                 style="@if(!$cd && !$track) display: none;@endif padding: 10px 15px 10px 5px"--}}
{{--                                                                 id="pkg_amount_lbl">@if($cd)--}}
{{--        {{$cd->debt_price}} AZN--}}
{{--    @endif @if($track)--}}
{{--        {{$track->debt_price}} AZN--}}
{{--    @endif</h3>--}}
{{--<button class="btn btn-primary btn-sm" type='submit' id="pkg_pay_btn"--}}
{{--        @if( !$cd && !$track)disabled @endif>{{ trans('front.pay') }}</button>--}}
{{--</form>--}}
