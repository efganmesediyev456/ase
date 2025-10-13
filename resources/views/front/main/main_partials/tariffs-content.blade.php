<h4>
    {{ __('front.tariff.number_of_flies') }}-
    {{ __('front.tariff.fly_per_week', ['w' => $country->warehouse->flies_week,'n' => $country->warehouse->flies_per_week]) }}</h4>
<div>
    <ul class="tarif-list">
        @if(count($country->warehouse->active_tariffs)>0)
                <?php $user = Auth::user(); ?>
            @foreach([[0,0.1,0.05],[0.1,0.2,0.15],[0.2,0.5,0.3],[0.5,0.75,0.6],[0.75,1,0.8],[1,10,5],[10,0,15]] as $weight)
                    <?php $warehouse = $country->warehouse; ?>
                @foreach($warehouse->active_tariffs[0]->tariff_weights as $tariffWeight)
                    @if(
                        ($tariffWeight->from_weight == $weight[0] && $tariffWeight->to_weight == $weight[1])||
                        (($tariffWeight->from_weight <= $weight[2] || $tariffWeight->from_weight == 0)&&
                        ($tariffWeight->to_weight > $weight[2] || $tariffWeight->to_weight == 0))
                        )
                        @if($user && $user->azerpoct_send)
                                <?php $found1 = false; ?>
                            @foreach($tariffWeight->azerpoct_tariff_prices as $tariffPrice)
                                @if($tariffPrice->city_id && $user->city_id == $tariffPrice->city_id)
                                        <?php $found1 = true; ?>
                                    <li>
                                        {{AZNWithLabel($tariffPrice->price, $warehouse->currency)}}
                                        <span>{{$tariffWeight->translateOrDefault($_lang)->name}}a</span>
                                    </li>
                                @endif
                            @endforeach
                            @if(!$found1)
                                @foreach($tariffWeight->azerpoct_tariff_prices as $tariffPrice)

                                    @if(!$tariffPrice->city_id)
                                        <li>
                                            {{AZNWithLabel($tariffPrice->price, $warehouse->currency)}}
                                            <span>{{$tariffWeight->translateOrDefault($_lang)->name}}b</span>
                                        </li>
                                    @endif

                                @endforeach
                            @endif
                        @else
                            <li>
                                {{AZNWithLabel($tariffWeight->non_azerpoct_tariff_prices[0]->price, $warehouse->currency)}}
                                <span>{{$tariffWeight->translateOrDefault($_lang)->name}}</span>
                            </li>
                        @endif
                        @break
                    @endif
                @endforeach
            @endforeach
        @else
            <li>
                {{ $country->warehouse->to_100g ?? $country->warehouse->half_kg }} {{ $country->warehouse->currency_with_label }}
                <span>{{ __('front.tariff.weight_ranges.0_to_100g') }}</span>
            </li>
            <li>
                {{ $country->warehouse->from_500g_to_750g ?? $country->warehouse->per_kg }} {{ $country->warehouse->currency_with_label }}
                <span>{{ __('front.tariff.weight_ranges.100g_to_200g') }}</span>
            </li>
            <li>
                {{ $country->warehouse->from_100g_to_200g ?? $country->warehouse->half_kg }} {{ $country->warehouse->currency_with_label }}
                <span>{{ __('front.tariff.weight_ranges.200g_to_500g') }}</span>
            </li>
            <li>
                {{ $country->warehouse->from_500g_to_750g ?? $country->warehouse->per_kg }} {{ $country->warehouse->currency_with_label }}
                <span>{{ __('front.tariff.weight_ranges.500g_to_750g') }}</span>
            </li>
            <li>
                {{ $country->warehouse->from_750g_to_1kq ?? $country->warehouse->per_kg }} {{ $country->warehouse->currency_with_label }}
                <span>{{ __('front.tariff.weight_ranges.750g_to_1kg') }}</span>
            </li>
            <li>
                {{ $country->warehouse->per_kg }} {{ $country->warehouse->currency_with_label }}
                <span>{{ __('front.tariff.weight_ranges.above_1kg') }}</span>
            </li>
        @endif
    </ul>
</div>