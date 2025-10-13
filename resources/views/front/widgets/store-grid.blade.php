@if($stores->count())
<div class="section-space80">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8 col-sm-12 col-xs-12">
                <div class="mb60 text-center section-title">
                    <!-- section title start-->
                    <h1>{{ __('front.shop.title') }}</h1>
                    <p>{{ __('front.shop.sub_title') }}</p>
                </div>
                <!-- /.section title start-->
            </div>
        </div>
        @foreach($stores->chunk(4) as $items)
            <div class="row">
                <div class="" id="service">
                    @foreach($items as $store)
                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <div class="service-block-v3">
                                <div class="icon mb20">
                                    <a target="_blank" href="{{ $store->url }}"> <img src="{{ $store->logo }} " alt="{{ $store->translateOrDefault($_lang)->name }}"></a>
                                </div>
                                <div class="service-content">
                                    <h2 class="service-title"><a target="_blank" href="{{ $store->url }}" class="title">{{ $store->translateOrDefault($_lang)->name }}</a></h2>
                                </div>
                                <div class="service-rate-block">
                                    <h3 class="product-rate">{{ $store->sale }}%</h3>
                                    <p class="rate-text">{{ __('front.shop.discount') }}</p>
                                </div>
                                <a target="_blank" href="{{ $store->url }}" class="btn btn-secondary btn-block">{{ __('front.shop.button') }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif