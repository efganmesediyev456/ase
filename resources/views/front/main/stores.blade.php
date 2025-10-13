<div>
    <div class="custom-container">
        <div style="margin-bottom: 50px" class="row">
            <div class="col-lg-4">
                <div>
                    <h1 style="font-weight: 500; font-size: 40px; color:#0E1B2F; margin-bottom: 14px">{{ __('front.shop.title') }}</h1>
                    <p style="font-weight: 400; font-size: 16px; line-height: 24px; color: #1a1a1e">{{ __('front.shop.sub_title') }}</p>
                </div>
            </div>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between" class="form-row floating-stores">
            @foreach($stores as $key=> $store)
                <div class="store-card-div">
                    <a style="background: #fff; display: flex; justify-content: center; align-items: center; border-radius: 10px; width: 110px; height: 100px; overflow: hidden"
                       class="store-card {{ $key % 2 == 0 ? 'even-card' : 'odd-card' }}"
                       title="{{ $store->name }}-dən sifariş və Azərbaycana çatdırılma"
                       target="_blank"
                       href="{{ $store->cashback_link }}">
                        <img style="object-fit: cover" src="{{ $store->logo }}" alt="{{ $store->name }}-dən sifariş və Azərbaycana çatdırılma">
                    </a>
                </div>
            @endforeach
            <div style="display: flex; width: 100%; align-items: center; justify-content: center; margin-top: 50px" class="col-lg-12 col-md-12 col-12 text-center">
                <a style="background: #15549A; font-size: 16px; font-weight: 500; color: #fff ; text-transform: capitalize" title="{{ __('front.menu.see_all') }}" href="{{ route('shop') }}" class="btn rounded-esi3">{{ __('front.menu.see_all') }}</a>
            </div>
        </div>
    </div>
</div>

