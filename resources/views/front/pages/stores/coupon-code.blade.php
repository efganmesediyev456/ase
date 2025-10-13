@if (! $coupon)
    <div class="alert alert-warning">
        {{ __('front.coupon_not_found') }}
    </div>
@else
    <div class="code-content">
        <a target="_blank" href="{{ $coupon->url }}" class="store-logo">
            <img src="{{ $coupon->store->logo }}" />
        </a>
        <div class="title">
            {!! $coupon->translateOrDefault($_lang)->name !!}
        </div>
        <div class="description">
            {!! $coupon->translateOrDefault($_lang)->description !!}
        </div>

        <div class="code">
            <p>{{ __('front.code_info') }}</p>
            <div class="code-label">
                {{ $coupon->code }}
            </div>
        </div>

        <div class="store-link">
            <a  class="btn btn-secondary btn-block" target="_blank" href="{{ $coupon->url }}">{{ __('front.visit_our_store') }}</a>
        </div>
    </div>
@endif
