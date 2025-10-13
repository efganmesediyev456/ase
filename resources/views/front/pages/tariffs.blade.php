@extends('front.layout')

@section('content')
    @include('front.sections.page-header')

    <div class=" ">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content bg-white pinside20">
                        <div class="">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="st-tabs">
                                        <!-- Nav tabs -->
                                        <ul class="nav nav-tabs nav-justified" role="tablist">
                                            @foreach($countries as $key => $country)
                                                <li role="presentation" @if(!$key) class="active" @endif>
                                                    <a href="#country_{{ $country->id }}"
                                                       aria-controls="country_{{ $country->id }}" role="tab"
                                                       data-toggle="tab">
                                                        <img src="{{ $country->flag }}"
                                                             style="width: 30px; margin-right: 7px"/>
                                                        {{ str_limit($country->translateOrDefault($_lang)->name, 25) }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <!-- Tab panes -->
                                        <div class="tab-content">
                                            @foreach($countries as $key => $country)
                                                    <?php $warehouse = $country->warehouse; ?>
                                                <div role="tabpanel" class="tab-pane fade in @if(!$key) active @endif"
                                                     id="country_{{ $country->id }}">
                                                    <table class="table table-bordered">
                                                        @if(count($warehouse->active_tariffs)>0)
                                                            <thead>
                                                            <tr>
                                                                <th>{{ __('front.tariff.title') }}</th>
                                                                @if(Auth::user() && Auth::user()->azerpoct_send)
                                                                    @foreach($warehouse->active_tariffs[0]->tariff_weights[0]->azerpoct_tariff_prices as $tariffPrice)
                                                                        <th>{{($tariffPrice->city_name=='-')?__('front.tariff.regions'):$tariffPrice->city->translateOrDefault($_lang)->name}}</th>
                                                                    @endforeach
                                                                @else
                                                                    @foreach($warehouse->active_tariffs[0]->tariff_weights[0]->non_azerpoct_tariff_prices as $tariffPrice)
                                                                        <th>{{($tariffPrice->city_name=='-')?__('front.tariff.description'):$tariffPrice->city->translateOrDefault($_lang)->name}}</th>
                                                                    @endforeach
                                                                @endif
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>{{ __('front.tariff.number_of_flies') }}</td>
                                                                <td colspan=2>{{ __('front.tariff.fly_per_week', ['w' => $warehouse->flies_week,'n' => $warehouse->flies_per_week]) }}</td>
                                                            </tr>
                                                            @foreach($warehouse->active_tariffs[0]->tariff_weights as $tariffWeight)
                                                                <tr>
                                                                    <td>{{ $tariffWeight->translateOrDefault($_lang)->name }}</td>
                                                                    @if(Auth::user() && Auth::user()->azerpoct_send)
                                                                        @foreach($tariffWeight->azerpoct_tariff_prices as $tariffPrice)
                                                                            <td>{{ AZNWithLabel($tariffPrice->price, $warehouse->currency) }}
                                                                                ({{ $tariffPrice->price }} {{ $warehouse->currency_with_label }})
                                                                            </td>
                                                                        @endforeach
                                                                    @else
                                                                        @foreach($tariffWeight->non_azerpoct_tariff_prices as $tariffPrice)
                                                                            <td>{{ AZNWithLabel($tariffPrice->price, $warehouse->currency) }}
                                                                                ({{ $tariffPrice->price }} {{ $warehouse->currency_with_label }})
                                                                            </td>
                                                                        @endforeach
                                                                    @endif
                                                                </tr>
                                                            @endforeach
                                                            @else
                                                                <thead>
                                                                <tr>
                                                                    <th>{{ __('front.tariff.title') }}</th>
                                                                    <th>{{ __('front.tariff.description') }}</th>
                                                                </tr>
                                                                </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>{{ __('front.tariff.number_of_flies') }}</td>
                                                                <td>{{ __('front.tariff.fly_per_week', ['w' => $warehouse->flies_week,'n' => $warehouse->flies_per_week]) }}</td>
                                                            </tr>
                                                            @if($warehouse->per_g)
                                                                @if($warehouse->half_kg)
                                                                    <tr>
                                                                        <td>{{ __('front.tariff.half_kg') }}</td>
                                                                        <td>{{ $warehouse->half_kg }} {{ $warehouse->currency_with_label }}</td>
                                                                    </tr>
                                                                @endif
                                                                <tr>
                                                                    <td>{{ __('front.tariff.per_g') }}</td>
                                                                    <td>{{ $warehouse->per_g }} {{ $warehouse->currency_with_label }}</td>
                                                                </tr>
                                                            @else
                                                                @if($warehouse->to_100g)
                                                                    <tr>
                                                                        <td>{{ __('front.tariff.to_100g') }}</td>
                                                                        <td>{{ $warehouse->to_100g }} {{ $warehouse->currency_with_label }}</td>
                                                                    </tr>
                                                                @endif

                                                                @if($warehouse->from_100g_to_200g)
                                                                    <tr>
                                                                        <td>{{ __('front.tariff.from_100g_to_200g') }}</td>
                                                                        <td>{{ $warehouse->from_100g_to_200g }} {{ $warehouse->currency_with_label }}</td>
                                                                    </tr>
                                                                @endif

                                                                @if($warehouse->from_200g_to_500g)
                                                                    <tr>
                                                                        <td>{{ __('front.tariff.from_200g_to_500g') }}</td>
                                                                        <td>{{ $warehouse->from_200g_to_500g }} {{ $warehouse->currency_with_label }}</td>
                                                                    </tr>
                                                                @endif

                                                                @if($warehouse->from_500g_to_750g)
                                                                    <tr>
                                                                        <td>{{ __('front.tariff.from_500g_to_750g') }}</td>
                                                                        <td>{{ $warehouse->from_500g_to_750g }} {{ $warehouse->currency_with_label }}</td>
                                                                    </tr>
                                                                @endif

                                                                @if($warehouse->from_750g_to_1kq)
                                                                    <tr>
                                                                        <td>{{ __('front.tariff.from_750g_to_1kq') }}</td>
                                                                        <td>{{ $warehouse->from_750g_to_1kq }} {{ $warehouse->currency_with_label }}</td>
                                                                    </tr>
                                                                @endif

                                                                @if($warehouse->half_kg)
                                                                    <tr>
                                                                        <td>{{ __('front.tariff.half_kg') }}</td>
                                                                        <td>{{ $warehouse->half_kg }} {{ $warehouse->currency_with_label }}</td>
                                                                    </tr>
                                                                @endif

                                                                <tr>
                                                                    <td>{{ $warehouse->from_500g_to_1kq ? __('front.tariff.per_kg_extra') : __('front.tariff.per_kg_extra') }}</td>
                                                                    <td>{{ $warehouse->per_kg }} {{ $warehouse->currency_with_label }}</td>
                                                                </tr>
                                                                @if($warehouse->per_kg != $warehouse->up_10_kg)
                                                                    <tr>
                                                                        <td>{{ __('front.tariff.per_10_kg') }}</td>
                                                                        <td>{{ $warehouse->up_10_kg }} {{ $warehouse->currency_with_label }}</td>
                                                                    </tr>
                                                                @endif
                                                            @endif
                                                            @endif
                                                            </tbody>
                                                    </table>
                                                    @if($country->pages->count())
                                                        <ul>
                                                            @foreach($country->pages as $countryPage)
                                                                <li>
                                                                    <a target="_blank"
                                                                       href="{{ route('pages.show', $countryPage->translateOrDefault($_lang)->slug) }}">
                                                                        {{ $countryPage->translateOrDefault($_lang)->title }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
