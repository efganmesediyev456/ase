<div class="section-space80">
    <div class="container">
        <div class="row">
            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                <div class="text-center">
                    <div class="icon">
                        <img alt="{{ __('front.tariff.number_of_flies') }}" src="/front/new/flight.png"/>
                        <h1 class="big-title mb-0">{{ $mainWarehouse->flies_per_week }}+</h1>
                        <div class="small-title">{{ __('front.tariff.number_of_flies') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                <div class="text-center">
                    <div class="icon">
                        <img alt="{{ __('front.tariff.main') }}" src="/front/new/scale.png"/>
                        <h1 class="big-title mb-0">100 gr = {{ $mainWarehouse->to_100g ? $mainWarehouse->to_100g : $mainWarehouse->half_kg }} {{ $mainWarehouse->currency_with_label }}</h1>
                        <div class="small-title">{{ __('front.tariff.main') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                <div class="text-center">
                    <div class="icon">
                        <img alt="{{ __('front.tariff.customer_satisfaction') }}" src="/front/new/feedback.png"/>
                        <div class="icon icon-1x big-title rate-done mb-0">
                            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i
                                    class="fa fa-star"></i><i class="fa fa-star"></i>
                        </div>
                        <div class="small-title">{{ __('front.tariff.customer_satisfaction') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>