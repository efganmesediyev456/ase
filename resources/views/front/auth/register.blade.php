@extends('front.layout')
@section('content')
    {{ Form::open(['method' => 'post', 'route' => 'auth.register']) }}

    <script type="text/javascript">
        var cities = [];
        var cities2 = [];
        var filials = [];
        @foreach($filials as $key => $value)
            filials['{{$key}}'] = '{{$value}}';
        @endforeach
                @foreach($deliverypoints as $af)
                @if(!empty($af->city_id))
        if (!cities2[{{$af->city_id}}])
            cities2[{{$af->city_id}}] = [];
        cities2[{{$af->city_id}}].push('ase_{{$af->id}}');
        @endif
                @endforeach
                @foreach($azeriexpressoffices as $af)
                @if(!empty($af->city_id))
        if (!cities2[{{$af->city_id}}])
            cities2[{{$af->city_id}}] = [];
        cities2[{{$af->city_id}}].push('{{$af->id}}');
        @endif
                @endforeach
                @foreach($suratOffices as $af)
                @if(!empty($af->city_id))
        if (!cities2[{{$af->city_id}}])
            cities2[{{$af->city_id}}] = [];
        cities2[{{$af->city_id}}].push('surat_{{$af->id}}');
        @endif
                @endforeach
                @foreach($yenipoctOffices as $af)
                @if(!empty($af->city_id))
        if (!cities2[{{$af->city_id}}])
            cities2[{{$af->city_id}}] = [];
        cities2[{{$af->city_id}}].push('yp_{{$af->id}}');
        @endif
                @endforeach
                @foreach($zipcities as $zipcity)
                @if(!empty($zipcity->city_id))
        if (!cities2[{{$zipcity->city_id}}])
            cities2[{{$zipcity->city_id}}] = [];
        cities2[{{$zipcity->city_id}}].push('zip_{{$zipcity->name}}');
        @endif
        @endforeach
    </script>
    <script type="text/javascript">
        var azexpr_addresses = [];
        @foreach($azeriexpressoffices as $azeriexpressoffice)
            azexpr_addresses[{{$azeriexpressoffice->id}}] = '{{$azeriexpressoffice->address}}';
        @endforeach
                @foreach($deliverypoints as $deliverypoint)
            azexpr_addresses['ase_{{$deliverypoint->id}}'] = '{{$deliverypoint->address}}';
        @endforeach
                @foreach($suratOffices as $suratOffice)
            azexpr_addresses['surat_{{$suratOffice->id}}'] = '{{$suratOffice->address}}';
        @endforeach
                @foreach($yenipoctOffices as $yenipoctOffice)
            azexpr_addresses['yp_{{$yenipoctOffice->id}}'] = '{{$yenipoctOffice->address}}';
        @endforeach
                @foreach($zipcities as $zipcity)
            azexpr_addresses['zip_{{$zipcity->name}}'] = '{{$zipcity->address}}';
        @endforeach
        function changeSel(sel) {
            let azexpr_address = '';
            if (sel.value != '') {
                azexpr_address = azexpr_addresses[sel.value];
            }
            $("#azexpr_address").text(azexpr_address);
        }

        function changeSel2(sel) {

            var azp_select = $('select[name="azeri_express_office_id"]');
            let azexpr_address = '';
            if (sel != '') {
                azexpr_address = azexpr_addresses[sel];
            }
            if (azp_select.prop("disabled")) azexpr_address = '';
            $("#azexpr_address").text(azexpr_address);
        }
    </script>

    {!! NoCaptcha::renderJs() !!}

    <div class=" ">
        <!-- content start -->
        <div class="container section-space40">
            <div class="contact-form">
                <div class="text-center ">
                    <img style="width:150px " src="{{ asset('front/new/logo_login.png') }}"  alt="fly"/>
                    <h1 style="font-weight: 600; font-size: 36px;color: #15549A; margin-top: 24px">{{ __('front.create_account') }}</h1>
                </div>
                <div style="margin-top: 60px" class="register-esi">
                    <div>
                        <div class=" div-input-login3 rounded-esi3   has-feedback has-feedback-left {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" >{{ __('front.name') }}</label>
                            <input placeholder="{{ __('front.name') }}" id="name" type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required>
                        </div>
                        <div class="form-control-feedback">
                            <i class="icon-user text-muted"></i>
                        </div>
                        @if ($errors->has('name'))
                            <label id="name-error" class="validation-error-label"
                                   for="name">{{ $errors->first('name') }}
                            </label>
                        @endif
                    </div>
                    <div>
                        <div class=" div-input-login3 rounded-esi3    has-feedback has-feedback-left {{ $errors->has('surname') ? 'has-error' : '' }}">
                            <label for="surname" >{{ __('front.surname') }}</label>
                            <input placeholder="{{ __('front.surname') }}" id="surname" type="text"
                                   name="surname"
                                   value="{{ old('surname') }}"
                                   required>
                        </div>
                        <div class="form-control-feedback">
                            <i class="icon-user-check text-muted"></i>
                        </div>
                        @if ($errors->has('surname'))
                            <label id="surname-error" class="validation-error-label"
                                   for="surname">{{ $errors->first('surname') }}</label>
                        @endif
                    </div>
                    <div class="">
                        <div data-popup="popover" title="FIN" data-placement="top" data-trigger="hover"
                             data-html="true"
                             data-content="<img style='width: 100%' src='{{ asset('front/images/fin.png') }}'/>"
                             class="div-input-login3 rounded-esi3 has-feedback has-feedback-left {{ $errors->has('fin') ? 'has-error' : '' }}">
                            <label for="fin" >FİN</label>
                            <input data-inputmask="'mask': '*******'" placeholder="0000000" id="fin" type="text"
                                   name="fin"
                                   value="{{ old('fin') }}"
                                   required>
                            <div class="form-control-feedback">
                                <i class="icon-mobile text-muted"></i>
                            </div>
                            @if ($errors->has('fin'))
                                <label id="phone-error" class="validation-error-label"
                                       for="fin">{{ $errors->first('fin') }}</label>
                            @endif

                        </div>
                    </div>
                    <div class="">
                        <div class="div-input-login3 rounded-esi3 has-feedback has-feedback-left {{ $errors->has('phone') ? 'has-error' : '' }}">
                            <label for="phone" >Telefon №</label>
                            <input data-inputmask="'mask': '999-999-99-99'" placeholder="050-500-00-00"
                                   id="phone"
                                   type="text"  name="phone"
                                   value="{{ old('phone') }}"
                                   required>
                        </div>
                        <div class="form-control-feedback">
                            <i class="icon-mobile text-muted"></i>
                        </div>
                        @if ($errors->has('phone'))
                            <label id="phone-error" class="validation-error-label"
                                   for="phone">{{ $errors->first('phone') }}</label>
                        @endif

                    </div>
                    <div style="display: flex; gap: 20px; justify-content: center; align-items: center">
                        <div style="width: 30%; position: relative;">
                            <label for="passport_prefix" style="position: absolute; top: 16px; left: 24px; font-size: 12px; color: #000; font-weight: 600;  z-index: 1;">Seriya nömrəsi</label>
                            <select style="border-radius: 8px; width: 100%; height: 86px; padding: 32px 24px 16px 24px; margin: 0 !important; appearance: none;outline: none; box-shadow: none"
                                    title="passport prefix"
                                    name="passport_prefix"
                                    id="passport_prefix"
                                    class="form-control">
{{--                                <option value="" disabled selected hidden>Seriya nömrəsi</option>--}}
                                <option @if (old('passport_prefix') == 'AZE') selected @endif value="AZE">AZE</option>
                                <option @if (old('passport_prefix') == 'AA') selected @endif value="AA">AA</option>
                                <option @if (old('passport_prefix') == 'AB') selected @endif value="AB">AB</option>
                            </select>
                        </div>
                        <div style="width: 70%" class="div-input-login3 rounded-esi1">
                            <label for="passport">№</label>
                            <input style="text-transform:uppercase" placeholder="12345678"
                                   id="passport"
                                   type="text" name="passport_number"
                                   value="{{ old('passport_number') }}"
                                   required>
                            <div class="form-control-feedback">
                                <i class="icon-credit-card text-muted"></i>
                            </div>
                        </div>
                        @if ($errors->has('passport_number'))
                            <label id="passport-error" class="validation-error-label"
                                   for="passport">{{ $errors->first('passport_number') }}</label>
                        @endif
                        @if ($errors->has('passport'))
                            <label id="passport-error" class="validation-error-label"
                                   for="passport">{{ $errors->first('passport') }}</label>
                        @endif
                    </div>
                    <div >
                        <div style="position: relative;" class=" has-feedback has-feedback-left {{ $errors->has('city') ? 'has-error' : '' }}">
                            <label for="passport_prefix" style="position: absolute; top: 16px; left: 24px; font-size: 12px; color: #000; font-weight: 600;  z-index: 1;"> {{ __('front.city') }}</label>
                            <select title="city" name="city" id="city"
                                    style="border-radius: 8px; width: 100%; height: 86px; padding: 32px 0 16px 24px;  margin: 0 !important; appearance: none; border:1px solid #dedede;outline: none; box-shadow: none"
                                    class="form-control">
                                <option value="">
                                    {{ __('front.city') }}
                                </option>
                                @foreach($cities as $city)
                                    <option @if (old('city') == $city->id) selected
                                            @endif value="{{ $city->id }}">
                                        {{ $city->translateOrDefault($_lang)->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control-feedback">
                            <i class="icon-location4 text-muted"></i>
                        </div>
                        @if ($errors->has('city'))
                            <label id="city-error" class="validation-error-label"
                                   for="address">{{ $errors->first('city') }}</label>
                        @endif
                    </div>
                    <div class="">
                        <div class=" div-input-login3 rounded-esi3 has-feedback has-feedback-left {{ $errors->has('email') ? 'has-error' : '' }}">
                            <label for="email" >{{ __('front.email') }}</label>
                            <input placeholder="{{ __('front.email') }}" id="email" type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required>
                        </div>
                        <div class="form-control-feedback">
                            <i class="icon-envelop text-muted"></i>
                        </div>
                        @if ($errors->has('email'))
                            <label id="email-error" class="validation-error-label"
                                   for="email">{{ $errors->first('email') }}</label>
                        @endif
                    </div>
                    <div class="">
                        <div style="position: relative" class=" div-input-login3 rounded-esi3  has-feedback has-feedback-left {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label for="password" >{{ __('front.password') }}</label>
                            <input placeholder="{{ __('front.password') }}" id="password" type="password"
                                   name="password" autocomplete='new-password' required>
                            <span class="toggle-password" style="position: absolute; right: 24px; bottom: 24px;  cursor: pointer;">
                                            <i class="icon-eye-blocked text-muted show-password-icon">
                                                <svg class="show-password-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                                </svg>
                                            </i>
                                            <i class="icon-eye text-muted hide-password-icon" style="display: none;">
                                               <svg class="hide-password-icon"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                               </svg>
                                            </i>
                                        </span>

                        </div>
                        <div class="form-control-feedback">
                            <i class="icon-lock2 text-muted"></i>
                        </div>
                        @if ($errors->has('password'))
                            <label id="password-error" class="validation-error-label"
                                   for="email">{{ $errors->first('password') }}</label>
                        @endif

                    </div>
                    <div>
                        <div class="div-input-login3 rounded-esi3 has-feedback has-feedback-left {{ $errors->has('address') ? 'has-error' : '' }}">
                            <label for="address" >{{ __('front.address') }}</label>
                            <input placeholder="{{ __('front.address') }}" id="address" type="text"
                                   name="address"
                                   value="{{ old('address') }}">
                        </div>
                        <div class="form-control-feedback">
                            <i class="icon-map4 text-muted"></i>
                        </div>
                        @if ($errors->has('address'))
                            <label id="address-error" class="validation-error-label"
                                   for="address">{{ $errors->first('address') }}</label>
                        @endif
                    </div>
                    <div>
                        <div style="position: relative;" class=" has-feedback has-feedback-left {{ $errors->has('city') ? 'has-error' : '' }}">
                            <label for="passport_prefix" style="position: absolute; top: 16px; left: 24px; font-size: 12px; color: #000; font-weight: 600;  z-index: 1;">{{ __('front.azeri_express_office') }}</label>
                            <select
                                    style="border-radius: 8px; width: 100%; height: 86px; padding: 32px 0 16px 24px;  margin: 0 !important; appearance: none; border:1px solid #dedede;outline: none; box-shadow: none"
                                    name="azeri_express_office_id" id="azeri_express_office_id"
                                    onchange="changeSel(this)"
                                    class="form-control">
                                @foreach($deliverypoints as $deliverypoint)
                                    <option @if (old('azeri_express_office_id') == 'ase_'.$deliverypoint->id) selected
                                            @endif value="ase_{{ $deliverypoint->id }}">
                                        {{ $deliverypoint->description}}
                                    </option>
                                @endforeach
                                @foreach($azeriexpressoffices as $azeri_express_office)
                                    <option @if (old('azeri_express_office_id') == $azeri_express_office->id) selected
                                            @endif value="{{ $azeri_express_office->id }}">
                                        {{ $azeri_express_office->description}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control-feedback">
                            <i class="icon-envelop text-muted"></i>
                        </div>
                        @if ($errors->has('azeri_express_office_id'))
                            <label id="email-error" class="validation-error-label"
                                   for="azeri_express_office_id">{{ $errors->first('azeri_azpress_office_id') }}</label>
                        @endif
                        <h3 style="font-size: 14px; font-weight: 500; color: black; padding-: 14px 10px 10px 10px ; border-bottom: 1px solid #15549a; display: inline-block ; margin: 0"  id='azexpr_address'>
                            <?php $address_set = false; ?>
                            @foreach($deliverypoints as $deliverypoint)
                                @if(old('azeri_express_office_id') == 'ase_'.$deliverypoint->id && !$address_set)
                                    {{$deliverypoint->address}}
                                        <?php $address_set = true; ?>
                                @endif
                            @endforeach
                            @if(!$address_set)
                                @foreach($zipcities as $zipcity)
                                    @if(old('zip_code') == $zipcity->name && !$address_set)
                                            <?php $address_set = true; ?>
                                        {{$zipcity->address}}
                                    @endif
                                @endforeach
                            @endif
                            @if(!$address_set)
                                @foreach($azeriexpressoffices as $azeriexpressoffice)
                                    @if(old('azeri_express_office_id') == $azeriexpressoffice->id && !$address_set)
                                        {{$azeriexpressoffice->address}}
                                            <?php $address_set = true; ?>
                                    @endif
                                @endforeach
                            @endif
                            @if(!$address_set)
                                @foreach($suratOffices as $suratOffice)
                                    @if(old('surat_office_id') == $suratOffice->id && !$address_set)
                                        {{$suratOffice->address}}
                                            <?php $address_set = true; ?>
                                    @endif
                                @endforeach
                            @endif
                            @if(!$address_set)
                                @foreach($yenipoctOffices as $yenipoctOffice)
                                    @if(old('yenipoct_office_id') == $yenipoctOffice->id && !$address_set)
                                        {{$yenipoctOffice->address}}
                                            <?php $address_set = true; ?>
                                    @endif
                                @endforeach
                            @endif

                        </h3>

                    </div>


                    <div  class="col-2-services1" style="display: flex; flex-direction: column;">

                        <div >
                            <div class="has-feedback has-feedback-left {{ $errors->has('city') ? 'has-error' : '' }}">
                                <div style="position: relative;" class=" has-feedback has-feedback-left {{ $errors->has('city') ? 'has-error' : '' }}">
                                    <label for="passport_prefix" style="position: absolute; top: 16px; left: 24px; font-size: 12px; color: #000; font-weight: 600;  z-index: 1;">
                                        {{ __('front.select_kargomat') }}
                                        <span>(Karqomatda bağlamanız yalnız 24 saat saxlana bilər!)</span>
                                    </label>
                                    <select
                                            style="border-radius: 8px; width: 100%; height: 86px; padding: 32px 0 16px 24px;  margin: 0 !important; appearance: none; border:1px solid #dedede;outline: none; box-shadow: none"
                                            title="kargomat_id" name="kargomat_id" id="kargomat_id"
                                            class="form-control">
                                        <option value="">
                                            {{ __('front.select_kargomat') }}
                                        </option>
                                        @foreach($kargomatOffices as $kargomat)
                                            <option @if (old('kargomat_id') == $kargomat->id) selected
                                                    @endif value="kargomat_{{ $kargomat->id }}" data-name="{{ $kargomat->name.' - '.$kargomat->address }}">
                                                {{ $kargomat->name }}
                                            </option>
                                        @endforeach


                                    </select>
                                </div>
                                <div class="form-control-feedback">
                                    <i class="icon-location4 text-muted"></i>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-2-services1" >
                        <div  class="{{ $errors->has('g-recaptcha-response') ? ' has-error' : '' }}">
                            <div >
                                {!! NoCaptcha::display() !!}

                                @if ($errors->has('g-recaptcha-response'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <button style="text-transform: capitalize;  display: inline-block; height: 56px;"  type="submit" class="btn btn-primary rounded-esi1">{{ __('front.menu.sign_up') }}<i
                                class="icon-circle-right2 position-right"></i></button>
                    <a style="border: 1px solid #15549a; color: #15549A; background: none; display: inline-block; height: 56px" href="{{ route('auth.login') }}"
                       class="btn btn-default btn-block content-group rounded-esi1">{{ __('front.menu.sign_in') }}</a>
                </div>
            </div>
        </div>
    </div>
    </div>
    {{ Form::close() }}
@endsection



@push('js')

    <script>
        function indexChange() {
            let city_id = $('select[name="city"]').val();
            let html = '<option value="" selected>Filial Seçin</option>';
            let selectedSel = '';
            if (city_id > 0 && !(typeof cities2[city_id] === 'undefined')) {
                let indexes = cities2[city_id];
                let lastSel = '';
                let address_set = false;
                for (let i = 0; i <= indexes.length - 1; i++) {
                    html = html + '<option value="' + indexes[i] + '"';
                    if ("{{old('azeri_express_office_id')}}" == "" || "{{old('azeri_express_office_id')}}" == indexes[i] && !address_set) {
                        selectedSel = indexes[''];
                        html = html;
                        address_set = true;
                    }
                    lastSel = indexes[i];
                    html = html + '>' + filials[indexes[i]] + '</option>';
                }
                if (selectedSel == '') selectedSel = lastSel;
            }
            console.log(html);
            $('select[name="azeri_express_office_id"]').prop('required', true).html(html);
            changeSel2(selectedSel);
        }

        $('select[name="city"]').on('change', indexChange);
        indexChange();
    </script>
    {{--    <script>--}}
    {{--        function indexChange() {--}}
    {{--                let city_id = $('select[name="city"]').val();--}}
    {{--                let html = '';--}}
    {{--                let selectedSel='';--}}
    {{--                if(city_id>0 && !(typeof cities2[city_id] === 'undefined')) {--}}
    {{--                   let indexes=cities2[city_id];--}}
    {{--                   let lastSel='';--}}
    {{--		   let address_set=false;--}}
    {{--                   for(let i=0;i<=indexes.length-1;i++) {--}}
    {{--                        html = html + '<option value="' + indexes[i] +'"';--}}
    {{--                        if("{{old('azeri_express_office_id')}}" == "" || "{{old('azeri_express_office_id')}}"==indexes[i] && !address_set) {--}}
    {{--                            selectedSel=indexes[i];--}}
    {{--                            html = html + ' selected';--}}
    {{--			    address_set=true;--}}
    {{--                        }--}}
    {{--                        lastSel=indexes[i];--}}
    {{--                        html = html + '>' + filials[indexes[i]] + '</option>';--}}
    {{--                   }--}}
    {{--                   if(selectedSel=='') selectedSel=lastSel;--}}
    {{--                }--}}
    {{--                console.log(html);--}}
    {{--                $('select[name="azeri_express_office_id"]').html(html);--}}
    {{--                changeSel2(selectedSel);--}}
    {{--	}--}}
    {{--	$('select[name="city"]').on('change',indexChange);--}}
    {{--	indexChange();--}}
    {{--    </script>--}}
    <script>

        $(document).ready(function () {
            var _passport = $("#passport");
            var _passport_profix = $("#passport_prefix");

            changeMask();

            _passport_profix.on('change', function () {
                changeMask();
            });

            function changeMask() {
                var valueSelected = _passport_profix.val();

                if ('AA' == valueSelected) {
                    $(_passport).inputmask("9999999");
                    $(_passport).attr('placeholder', "1234567");
                } else {
                    $(_passport).inputmask("99999999");
                    $(_passport).attr('placeholder', "12345678");
                }
            }

            // Popover
            $('[data-popup="popover"]').popover();
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function updateSelectStates() {
                const kargomat = document.getElementById("kargomat_id");
                const office = document.getElementById("azeri_express_office_id");
                const city = document.getElementById("city");

                const isKargomatSelected = kargomat.value !== "";
                const isOfficeOrCitySelected = office.value !== "" || city.value !== "";

                // Kargomat seçilibsə, filial və şəhəri disable et
                if (isKargomatSelected) {
                    office.disabled = true;
                    office.required = false;
                    city.disabled = true;
                    city.required = false;
                    kargomat.disabled = false;
                    kargomat.required = true;
                }
                // Filial və ya şəhər seçilibsə, kargomatı disable et
                else if (isOfficeOrCitySelected) {
                    kargomat.disabled = true;
                    kargomat.required = false;
                    office.disabled = false;
                    office.required = true;
                    city.disabled = false;
                    city.required = true;
                }
                // Heç biri seçilməyibsə, hamısını aktiv et
                else {
                    kargomat.disabled = false;
                    kargomat.required = false;
                    office.disabled = false;
                    office.required = true;
                    city.disabled = false;
                    city.required = true;
                }
            }

            // İlk yükləndikdə və dəyişiklik olduqda funksiyanı işə sal
            updateSelectStates();
            document.getElementById("kargomat_id").addEventListener("change", updateSelectStates);
            document.getElementById("azeri_express_office_id").addEventListener("change", updateSelectStates);
            document.getElementById("city").addEventListener("change", updateSelectStates);
        });

    </script>

@endpush
