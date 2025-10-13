<div class="col-md-4 col-sm-6 col-xs-12 coupon-item">
    <div class="service-block-v3">
        <div class="icon mb20">
            <a target="_blank" href="{{ $item->url }}"> <img
                        src="{{ $item->image }} " alt="{{ $item->name }}"></a>
        </div>
        <div class="service-content">
            <h2 class="service-title">
                <a target="_blank" href="{{ $item->url }}"
                   class="title pink">{{ $item->translateOrDefault($_lang)->name }}</a>
            </h2>
        </div>
        <div class="service-rate-block">
            <p class="rate-text">{{ str_limit(strip_tags($item->translateOrDefault($_lang)->description), 75) }}</p>
        </div>

        <div class="expired">
            <i class="fa fa-clock-o"></i> {{ __('front.coupon.expired_on', ['date' => date('d/m/Y', strtotime($item->end_at))]) }}
        </div>

        @if ($item->type_id)
            <a target="_blank" href="{{ $item->url }}"
               class="btn btn-secondary btn-block">{{ __('front.shop.sale_button') }}</a>
        @else
            <a href="{{ route('coupon', $item->id) }}" data-name="{{ $item->translateOrDefault($_lang)->name }}" data-toggle="modal" data-target="#ajaxModal" data-remote="false"
               class="btn btn-secondary btn-block">{{ __('front.shop.coupon.button') }}</a>
        @endif
    </div>
</div>