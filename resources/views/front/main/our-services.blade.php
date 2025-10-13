<div class="custom-container">
    <div style="margin-bottom: 40px" class="row">
        <div class="col-lg-4">
            <h1 style="font-weight: 500; font-size: 40px; color:#000; margin-bottom: 14px">
                <span style="color: #15549a">{{ __('front.services.title') }}</span>
                {{ __('front.services.title2') }}
            </h1>
        </div>
    </div>
    <div class="services-grid">
        @php

            $services = [
                       [
                           'title' => 'Türkiyədən çatdırılma',
                           'image' => asset('front/new/turkey.png'),
                           'description' => '“Aseshop” Türkiyədən onlayn sifariş edilən məhsulların və poçt bağlamalarının sürətli və etibarlı çatdırılmasını təmin edir.'
                       ],
                       [
                           'title' => 'Amerikadan çatdırılma',
                           'image' => asset('front/new/usa.png'),
                           'description' => '“Aseshop” ilə ABŞ-dan istədiyiniz məhsulları əldə edin və ən sərfəli qiymətlərlə Azərbaycana çatdırın.'
                       ],
                       [
                           'title' => 'Almaniyadan çatdırılma',
                           'image' => asset('front/new/germany.png'),
                           'description' => '“Aseshop” ilə Almaniyadan istədiyiniz məhsulları əldə edin və ən sərfəli qiymətlərlə Azərbaycana çatdırın.'
                       ],
                       [
                           'title' => 'İngiltərədən çatdırılma',
                           'image' => asset('front/new/united-kingdom.png'),
                           'description' => '“Aseshop” Böyük Britaniyadan onlayn sifariş edilən məhsulların və poçt bağlamalarının sürətli və etibarlı çatdırılmasını təmin edir.'
                       ]
                   ];
 @endphp
        @foreach(__('front.services_front') as $service)
            <div style="padding:32px; min-height:200px" class="rounded-esi bg-white blue_hover">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px">
                    <img style="width: 48px; height: 32px; object-fit: cover;" src="{{ $service['image'] }}" alt="{{ $service['title'] }}">
                    <span style="font-size: 18px; font-weight: 500; color: #000">{{ $service['title'] }}</span>
                </div>
                <p style="line-height: 1.5">{{ $service['description'] }}</p>
            </div>
        @endforeach
    </div>

</div>