<div class="col-md-4 col-sm-6 col-xs-12">
    <div class="service-block-v3 product">
        <div class="icon mb20">
            <a target="_blank" href="{{ $item->url }}"> <img
                        src="{{ $item->image }} " alt="{{ $item->name }}"></a>
        </div>
        <div class="service-content">
            <h2 class="service-title"><a target="_blank" href="{{ $item->url }}"
                                         class="title">{{ $item->translateOrDefault($_lang)->name }}</a></h2>
        </div>

        <div class="lender-rate-box">
            <div class="lender-ads-rate">
                <small>{{ __('front.shop.sale') }}:</small>
                <h3 class="lender-rate-value">{{ $item->sale }}</h3>
            </div>
            <div class="lender-compare-rate">
                <small>{{ __('front.shop.price') }}</small>
                <h3 class="lender-rate-value">{{ $item->price }}</h3>
            </div>
        </div>
        <a target="_blank" href="{{ $item->url }}"
           class="btn btn-secondary btn-block">{{ __('front.shop.buy_now') }}</a>
    </div>
</div>