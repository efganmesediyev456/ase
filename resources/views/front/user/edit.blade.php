@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

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
                @foreach($yenipoctOffices as $af)
                @if(!empty($af->city_id))
        if (!cities2[{{$af->city_id}}])
            cities2[{{$af->city_id}}] = [];
        cities2[{{$af->city_id}}].push('yp_{{$af->id}}');
        @endif
                @endforeach
                @foreach($kargomatOffices as $af)
                @if(!empty($af->city_id))
        if (!cities2[{{$af->city_id}}])
            cities2[{{$af->city_id}}] = [];
        cities2[{{$af->city_id}}].push('kargomat_{{$af->id}}');
        @endif
                @endforeach
                @foreach($suratOffices as $af)
                @if(!empty($af->city_id))
        if (!cities2[{{$af->city_id}}])
            cities2[{{$af->city_id}}] = [];
        cities2[{{$af->city_id}}].push('surat_{{$af->id}}');
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
        var azexpr_contacts = [];
        @foreach($deliverypoints as $deliverypoint)
            azexpr_addresses['ase_{{$deliverypoint->id}}'] = '{{$deliverypoint->address}}';
        azexpr_contacts['ase_{{$deliverypoint->id}}'] = '{{$deliverypoint->contact_name ? " kontakt adı: ".$deliverypoint->contact_name."  " : ""}}' + '{{$deliverypoint->contact_phone ? "telefon: ".$deliverypoint->contact_phone : ""}}';
        @endforeach
                @foreach($suratOffices as $suratOffice)
            azexpr_addresses['surat_{{$suratOffice->id}}'] = '{{$suratOffice->address}}';
        azexpr_contacts['surat_{{$suratOffice->id}}'] = '{{$suratOffice->contact_name ? " kontakt adı: ".$suratOffice->contact_name."  " : ""}}' + '{{$suratOffice->contact_phone ? "telefon: ".$suratOffice->contact_phone : ""}}';
        @endforeach
                @foreach($yenipoctOffices as $yenipoctOffice)
            azexpr_addresses['yp_{{$yenipoctOffice->id}}'] = '{{$yenipoctOffice->address}}';
        azexpr_contacts['yp_{{$yenipoctOffice->id}}'] = '{{$yenipoctOffice->contact_name ? " kontakt adı: ".$yenipoctOffice->contact_name."  " : ""}}' + '{{$yenipoctOffice->contact_phone ? "telefon: ".$yenipoctOffice->contact_phone : ""}}';
        @endforeach
                @foreach($kargomatOffices as $kargomatOffice)
            azexpr_addresses['kargomat_{{$kargomatOffice->id}}'] = '{{$kargomatOffice->address}}';
        azexpr_contacts['kargomat_{{$kargomatOffice->id}}'] = '{{$kargomatOffice->contact_name ? " kontakt adı: ".$kargomatOffice->contact_name."  " : ""}}' + '{{$kargomatOffice->contact_phone ? "telefon: ".$kargomatOffice->contact_phone : ""}}';
        @endforeach
                @foreach($azeriexpressoffices as $azeriexpressoffice)
            azexpr_addresses[{{$azeriexpressoffice->id}}] = '{{$azeriexpressoffice->address}}';
        azexpr_contacts[{{$azeriexpressoffice->id}}] = '{{$azeriexpressoffice->contact_name ? " kontakt adı: ".$azeriexpressoffice->contact_name."  " : ""}}' + '{{$azeriexpressoffice->contact_phone ? "telefon: ".$azeriexpressoffice->contact_phone : ""}}';
        @endforeach
                @foreach($zipcities as $zipcity)
            azexpr_addresses['zip_{{$zipcity->name}}'] = '{{$zipcity->address}}';
        azexpr_contacts['zip_{{$zipcity->name}}'] = '{{$zipcity->contact_name ? " kontakt adı: ".$zipcity->contact_name."  " : ""}}' + '{{$zipcity->contact_phone ? "telefon: ".$zipcity->contact_phone : ""}}';
        @endforeach
        function changeSel(sel) {
            let azexpr_address = '';
            if (sel.value != '') {
                azexpr_address = azexpr_addresses[sel.value];
            }
            $("#azexpr_address").text(azexpr_address);
            let azexpr_contact = '';
            if (sel.value != '') {
                azexpr_contact = azexpr_contacts[sel.value];
            }
            $("#azexpr_contact").text(azexpr_contact);
        }

        function changeSel2(sel) {
            var azp_select = $('select[name="azeri_express_office_id"]');
            let azexpr_address = '';
            if (sel != '') {
                azexpr_address = azexpr_addresses[sel];
            }
            if (azp_select.prop("disabled")) azexpr_address = '';
            $("#azexpr_address").text(azexpr_address);
            let azexpr_contact = '';
            if (sel != '') {
                azexpr_contact = azexpr_contacts[sel];
            }
            if (azp_select.prop("disabled")) azexpr_contact = '';
            $("#azexpr_contact").text(azexpr_contact);
        }
    </script>
    <div class=" ">
        <!-- content start -->
        <div class="custom-container section-space140">
            <div class="row">
                <div class="col-md-12">
                    <div style="padding: 30px" class="wrapper-content  bg-white box-shadow-esi rounded-esi">
                        <div class="contact-form mb60">
                            <div class=" ">
                                <div >
                                    <div class="mb40  section-title text-left  ">
                                        <h1 style="font-size: 32px; font-weight: 500; color: #000; letter-spacing: .14rem" id="info_section">{{ __('front.editing_member_profile') }}</h1>
{{--                                        <div class="trendyol btn btn-success">Trendyol təsdiq</div>--}}
                                        @if (session('success'))
                                            <div class="alert alert-success"
                                                 role="alert">{{ __('front.profile_was_updated') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    {!! Form::open(['class' => 'contact-us', 'route' => 'update', 'files' => true]) !!}
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['key' => 'name', 'label' => trans('front.name'), 'options' => ['class' => 'form-control']])
                                        </div>
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['key' => 'surname', 'label' => trans('front.surname'), 'options' => ['class' => 'form-control']])
                                        </div>
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['key' => 'company', 'label' => trans('front.company'), 'options' => ['class' => 'form-control']])
                                        </div>
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['key' => 'email', 'label' => trans('front.email'), 'options' => ['class' => 'form-control', 'disabled' => 'disabled']])
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            <div class="has-feedback has-feedback-left {{ $errors->has('passport_number')||$errors->has('passport_number') ? 'has-error' : '' }}">

                                                <div class=" ">

                                                    <div class="col-lg-3 div-input-login4 ">
                                                        <label>{{ trans('front.passport') }}</label>
                                                        <select title="passport prefix" name="passport_prefix"
                                                                id="passport_prefix"
                                                                class="form-control">
                                                            <option @if (old('passport_prefix', $item->pre_passport) == 'AZE') selected
                                                                    @endif value="AZE">
                                                                AZE
                                                            </option>
                                                            <option @if (old('passport_prefix', $item->pre_passport) == 'AA') selected
                                                                    @endif value="AA">AA
                                                            </option>
                                                            <option @if (old('passport_prefix', $item->pre_passport) == 'DYI') selected
                                                                    @endif value="DYI">DYI
                                                            </option>
                                                            <option @if (old('passport_prefix', $item->pre_passport) == 'MYI') selected
                                                                    @endif value="MYI">MYI
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-9 div-input-login4 ">
                                                        <label>{{ trans('front.passport') }}</label>
                                                        <input style="text-transform:uppercase" placeholder="12345678"
                                                               id="passport"
                                                               type="text" class="form-control" name="passport_number"
                                                               value="{{ old('passport_number', $item->pos_passport) }}"
                                                               required>
                                                        <div class="form-control-feedback">
                                                            <i class="icon-credit-card text-muted"></i>
                                                        </div>
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
                                        </div>
                                        <div class="col-md-6 col-sm-12 col-xs-12 ">

                                            <div>
                                                <div data-popup="popover" title="FIN" data-placement="top"
                                                     data-trigger="hover"
                                                     data-html="true"
                                                     data-content="<img style='width: 100%' src='{{ asset('front/images/fin.png') }}'/>"
                                                     class="form-group has-feedback has-feedback-left div-input-login4 {{ ($errors->has('fin') || (isset($nulled) && ! is_null($nulled))) ? 'has-error' : '' }}">
                                                    <label>Fin</label>
                                                    <input placeholder="FİN" id="fin" type="text"
                                                           class="form-control" name="fin"
                                                           value="{{ old('fin', $item->fin) }}"
                                                           @if($checkDeclarations > 0) style="background: #5e595954;" readonly @else required @endif> </div>
                                            </div>
                                            <div class="form-control-feedback">
                                                <i class="icon-mobile text-muted"></i>
                                            </div>
                                            @if ($errors->has('fin'))
                                                <label id="phone-error" class="validation-error-label"
                                                       for="fin">{{ $errors->first('fin') }}</label>
                                            @endif


                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['key' => 'phone', 'label' => trans('front.phone'), 'options' => ['class' => 'form-control', 'disabled' => 'disabled']])
                                        </div>
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['key' => 'birthday', 'label' => trans('front.birthday'), 'options' => ['min' => "1950-01-01", 'max' => "2020-01-01", 'class' => 'form-control changeTypeToDate']])
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['type' => 'text', 'key' => 'address', 'label' => trans('front.address'), 'options' => ['class' => 'form-control', 'rows' => 4]])
                                        </div>
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['key' => 'promo', 'label' => trans('front.promo_code'), 'options' => ['class' => 'form-control']])
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['type' => 'select', 'key' => 'city_id', 'label' => trans('front.city'), 'selects' => $cities, 'options' => ['class' => 'form-control']])
                                        </div>
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <h3 id='azexpr_address' style="margin-top: 25px">
                                                    <?php $address_set = false; ?>
                                                @foreach($azeriexpressoffices as $azeriexpressoffice)
                                                    @if(old('azeri_express_office_id') == $azeriexpressoffice->id || (empty(old('azeri_express_office_id')) && $item->azeri_express_use && $item->azeri_express_office_id==$azeriexpressoffice->id))
                                                            <?php $address_set = true; ?>
                                                        {{$azeriexpressoffice->address}}
                                                    @endif
                                                @endforeach
                                                @if(!$address_set)
                                                    @foreach($zipcities as $zipcity)
                                                        @if(old('zip_code') == $zipcity->name || (empty(old('zip_code')) && $item->azerpoct_send && $item->zip_code==$zipcity->name))
                                                                <?php $address_set = true; ?>
                                                            {{$zipcity->address}}
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if(!$address_set)
                                                    @foreach($deliverypoints as $deliverypoint)
                                                        @if(old('store_status') == $deliverypoint->id || (empty(old('store_status')) && !$item->azerpoct_send && !$item->azeri_express_use && $item->store_status==$deliverypoint->id))
                                                                <?php $address_set = true; ?>
                                                            {{$deliverypoint->address}}
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if(!$address_set)
                                                    @foreach($suratOffices as $suratOffice)
                                                        @if(old('surat_office_id') == $suratOffice->id || (empty(old('surat_office_id')) && !$item->azerpoct_send && !$item->azeri_express_use && $item->surat_office_id==$suratOffice->id))
                                                                <?php $address_set = true; ?>
                                                            {{$suratOffice->address}}
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if(!$address_set)
                                                    @foreach($yenipoctOffices as $yenipoctOffice)
                                                        @if(old('yenipoct_office_id') == $yenipoctOffice->id || (empty(old('yenipoct_office_id')) && !$item->azerpoct_send && !$item->azeri_express_use && $item->yenipoct_office_id==$yenipoctOffice->id))
                                                                <?php $address_set = true; ?>
                                                            {{$yenipoctOffice->address}}
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if(!$address_set)
                                                    @foreach($kargomatOffices as $kargomatOffice)
                                                        @if(old('kargomat_office_id') == $kargomatOffice->id || (empty(old('kargomat_office_id')) && !$item->azerpoct_send && !$item->azeri_express_use && $item->kargomat_office_id==$kargomatOffice->id))
                                                                <?php $address_set = true; ?>
                                                            {{$kargomatOffice->address}}
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </h3>
                                            <h4 id='azexpr_contact' style="margin-top: -5px">
                                                    <?php $kontakt_set = false; ?>
                                                @foreach($azeriexpressoffices as $azeriexpressoffice)
                                                    @if(old('azeri_express_office_id') == $azeriexpressoffice->id || (empty(old('azeri_express_office_id')) && $item->azeri_express_use && $item->azeri_express_office_id==$azeriexpressoffice->id))
                                                        @if($azeriexpressoffice->contact_name && $azeriexpressoffice->contact_phone)
                                                                <?php $kontakt_set = true; ?>
                                                            kontakt adı: {{$azeriexpressoffice->contact_name}}
                                                            telefon: {{$azeriexpressoffice->contact_phone}}
                                                        @endif
                                                    @endif
                                                @endforeach
                                                @if(!$kontakt_set)
                                                    @foreach($deliverypoints as $deliverypoint)
                                                        @if(old('store_status') == $deliverypoint->id || (empty(old('store_status')) && !$item->azeri_express_use && $item->store_status==$deliverypoint->id))
                                                            @if($deliverypoint->contact_name && $deliverypoint->contact_phone)
                                                                    <?php $kontakt_set = true; ?>
                                                                kontakt adı: {{$deliverypoint->contact_name}}
                                                                telefon: {{$deliverypoint->contact_phone}}
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if(!$kontakt_set)
                                                    @foreach($yenipoctOffices as $yenipoctOffice)
                                                        @if(old('yenipoct_office_id') == $yenipoctOffice->id || (empty(old('yenipoct_office_id')) && !$item->azeri_express_use && $item->yenipoct_office_id==$yenipoctOffice->id))
                                                            @if($yenipoctOffice->contact_name && $yenipoctOffice->contact_phone)
                                                                    <?php $kontakt_set = true; ?>
                                                                kontakt adı: {{$yenipoctOffice->contact_name}}
                                                                telefon: {{$yenipoctOffice->contact_phone}}
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if(!$kontakt_set)
                                                    @foreach($kargomatOffices as $kargomatOffice)
                                                        @if(old('kargomat_office_id') == $kargomatOffice->id || (empty(old('kargomat_office_id')) && !$item->azeri_express_use && $item->kargomat_office_id==$kargomatOffice->id))
                                                            @if($kargomatOffice->contact_name && $kargomatOffice->contact_phone)
                                                                    <?php $kontakt_set = true; ?>
                                                                kontakt adı: {{$kargomatOffice->contact_name}}
                                                                telefon: {{$kargomatOffice->contact_phone}}
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if(!$kontakt_set)
                                                    @foreach($suratOffices as $suratOffice)
                                                        @if(old('surat_office_id') == $suratOffice->id || (empty(old('surat_office_id')) && !$item->azeri_express_use && $item->surat_office_id==$suratOffice->id))
                                                            @if($suratOffice->contact_name && $suratOffice->contact_phone)
                                                                    <?php $kontakt_set = true; ?>
                                                                kontakt adı: {{$suratOffice->contact_name}}
                                                                telefon: {{$suratOffice->contact_phone}}
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </h4>
                                        </div>
                                        <div class="col-lg-12 col-sm-12 col-xs-12">
                                            <div class="form-group has-feedback has-feedback-left m-0 {{ $errors->has('city') ? 'has-error' : '' }}">
                                                <div class="">
                                                    <div class="div-input-login4">
                                                        <Label>Karqomatda bağlamanız yalnız 24 saat saxlana bilər!</Label>
                                                        <select title="kargomat_id" class="form-control" disabled>
                                                            <option value="">
                                                                {{ __('front.select_kargomat') }}
                                                            </option>
                                                        </select>

                                                        <div class="form-control-feedback">
                                                            <i class="icon-location4 text-muted"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class=" col-xs-12">
                                        <div class="mt20 mt40  section-title text-left  ">
                                            <h1>{{ __('front.changing_password') }}</h1>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div  class=" col-md-6 col-sm-12 col-xs-12 rounded-esi3">
                                            @include('front.form.group',
                                             ['type' => 'password', 'key' => 'password', 'label' => trans('front.password'),
                                             'options' => ['placeholder' => trans('front.enter_new_password'),
                                             'class' => 'form-control','autocomplete'=>'new-password',
                                             ]])
                                        </div>
                                        <!-- Text input-->
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            @include('front.form.group', ['type' => 'password', 'key' => 'password_confirmation', 'label' => trans('front.password_confirmation'), 'options' => ['placeholder' => trans('front.enter_password_confirmation'),'class' => 'form-control']])
                                        </div>
                                    </div>
                                    <div class="row mt20">
                                        <!-- Button -->
                                        <div class="col-md-12 col-xs-12 text-center">
                                            <button style="" type="submit"
                                                    class="button-blue-esi rounded-esi1 width-360">{{ __('front.save') }}</button>
                                        </div>
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                            <!-- /.section title start-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="phoneModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nömrəni təsdiqlə</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="text" id="phone" class="form-control" placeholder="Telefon nömrəsi" value="{{ auth()->user()->phone }}">

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Ləğv et</button>
                    <button class="btn btn-primary" id="confirmPhone">Təsdiqlə</button>
                </div>
            </div>
        </div>
    </div>


@endsection


@push('js')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"
            defer></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        function indexChange() {
            let city_id = $('select[name="city_id"]').val();
            let html = '';
            let selectedSel = '';
            if (city_id > 0 && !(typeof cities2[city_id] === 'undefined')) {
                let indexes = cities2[city_id];
                let firstSel = '';
                let address_set = false;
                for (let i = 0; i <= indexes.length - 1; i++) {
                    html = html + '<option value="' + indexes[i] + '"';
                    if ("{{old('azeri_express_office_id',$item->azeri_express_office_id)}}" == indexes[i] && !address_set) {
                        selectedSel = indexes[i];
                        html = html + ' selected';
                        address_set = true;
                    }
                    if (firstSel == '')
                        firstSel = indexes[i];
                    html = html + '>' + filials[indexes[i]] + '</option>';
                }
                if (selectedSel == '') selectedSel = firstSel;
            }
            $('select[name="azeri_express_office_id"]').html(html);
            changeSel2(selectedSel);
        }

        $('select[name="city_id"]').on('change', indexChange);
        indexChange();
        $(document).ready(function () {

            $('.select').select2();
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

            $('[data-popup="popover"]').popover();

        });


        //trendyol
    </script>


    <script>
        $(document).ready(function () {

            $('.trendyol').on('click', function () {
                $('#phoneModal').modal('show');
            });

            $('#confirmPhone').on('click', function () {
                let phone = $('#phone').val().trim();

                if (phone === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Xəta',
                        text: 'Telefon nömrəsi boş ola bilməz'
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route('trendyol.assigned') }}',
                    method: 'POST',
                    data: {
                        phone: phone,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                        $('#confirmPhone').prop('disabled', true);
                    },
                    success: function (res) {
                        $('#phoneModal').modal('hide');
                        $('#confirmPhone').prop('disabled', false);

                        Swal.fire({
                            icon: 'success',
                            title: res.title,
                            text: res.text,
                            showConfirmButton: true,
                            timerProgressBar: false,
                            position: 'center',
                        });

                    },
                    error: function (xhr) {
                        $('#confirmPhone').prop('disabled', false);

                        let errMsg = 'Xəta baş verdi!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Xəta',
                            text: errMsg
                        });
                    }
                });
            });

        });

    </script>

@endpush
