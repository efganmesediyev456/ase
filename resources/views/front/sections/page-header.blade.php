<div  style="min-height: 170px; background:#4274ac !important; box-sizing: border-box; margin-top: 30px">
    <div style="padding-block: 24px " class="custom-container">
        <div >
            <div >
                <div class="page-breadcrumb">
                    <ol class="breadcrumb">
                        <li ><a style="color: #c1c2c5; font-weight: 500; font-size: 12px" href="{{ route('home') }}">{{ __('front.menu.home') }}</a></li>
                        <li class="active-breadcrumb" >{{ $breadTitle or '404' }}</li>
                    </ol>
                </div>
            </div>
            @if(Auth::check() && strpos(url()->current(), '/user') != false)
                <div style="background: #fff;padding-top: 30px;overflow: hidden" class="rounded-esi" >
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-inline: 24px">
                        <div >
                            <h1 class="title-blue">{{ Auth::user()->full_name }}</h1>
                            <div style="font-size: 16px; color: #bfbfbf;" >{{ Auth::user()->customer_id }}</div>
                        </div>
                        <div class="text-right">
                            <a
                                    class="rounded-esi pink-buttton"
                                    href="{{ route('my-packages', ['id' => 3]) }}?last_30_days=true">
                                @if (isset($spending) && $spending)
                                    <span style="color: #fff;" class="rate-number">{{ $spending['sum'] }}$
                                            / {{ trans('front.num_orders', ['orders' => $spending['total']]) }}</span>
                                @else
                                    <span style="color: #fff;" class="rate-number">0$
                                            / {{ trans('front.num_orders', ['orders' => 0]) }}</span>
                                @endif
                            </a>
                            <div style="color: #bfbfbf; font-size: 18px">{{ trans('front.last_30_days') }}</div>
                        </div>
                    </div>
                    @if(isset($showSubButtons))

                        <div id="sub-nav-sticky-wrapper" class="sticky-wrapper">
                            <div class="sub-nav" id="sub-nav">
                                <ul class="nav nav-justified">
                                    @if (isset($showSubButtons) and is_array($showSubButtons))
                                        @foreach($showSubButtons as $subButton)
                                            <li {!! classActiveRoute($subButton['route']) !!}
                                                class="@if(classActiveRoute($subButton['route']))
                                                active-esi @else not-active-esi @endif"
                                            >
                                                <a href="{{ route($subButton['route']) }}">{{ __($subButton['label']) }}</a>
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div >
                    <h1 style="color: #fff; text-transform: capitalize !important;" class="page-title">{{ $title or __('front.page_not_found') }}</h1>
                </div>

            @endif
        </div>
    </div>
</div>
