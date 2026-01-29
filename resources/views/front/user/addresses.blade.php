@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    <style>
        .accordion-icon {
            transition: transform 0.3s ease-in-out;
            transform: rotate(0deg);
        }

        .panel-title a[aria-expanded="true"] + div .accordion-icon {
            transform: rotate(180deg);
        }
        @media(min-width: 900px){
            .trendyol{
                display:none;
            }
        }
    </style>
    <div class=" ">
        <div class="custom-container section-space60">
            <div class="">
                <div class="">
                    <div class="">
                        <div class="">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="st-tabs addresses">
                                        <ul style="margin-bottom: 60px" class="nav nav-tabs nav-justified"
                                            role="tablist">
                                            @foreach($countries as $key => $country)
                                                <li role="presentation" @if(!$key) class="active" @endif>
                                                    <a
                                                            style="display: block; display: flex; align-items: center; padding:17px 34px !important;"
                                                            href="#country_{{ $country->id }}"
                                                            aria-controls="country_{{ $country->id }}" role="tab"
                                                            data-toggle="tab">
                                                        <img src="{{ $country->flag }}"
                                                             style="width: 48px; height: 48px; margin-right: 14px"/>
                                                        <span style="color: #000;  font-size: 24px ; font-weight: 400">{{ str_limit($country->translateOrDefault($_lang)->name, 25) }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div style="background: none; border: none; padding: 0 " class="tab-content">
                                            @foreach($countries as $key => $country)
                                                <div style="background: none" role="tabpanel"
                                                     class="tab-pane fade in @if(!$key) active @endif"
                                                     id="country_{{ $country->id }}">
                                                    <div style="margin-bottom: 40px">
                                                        <h1 class="title-blue">{{ __('front.about_country_address', ['country' => $country->translateOrDefault($_lang)->name1]) }}</h1>
                                                    </div>
                                                    @foreach($country->warehouses as $warehouse)
                                                        @if($warehouse->addresses->count() && $warehouse->id != 12)
                                                            <div class="row mb40">
                                                                <div class="col-lg-12">
                                                                    <div class="fee-charges-table">
                                                                        @foreach($warehouse->addresses as $addresses)
                                                                            <ul style="border-radius: 0;"
                                                                                class="list-group">
                                                                                <li style="margin-bottom: 24px; padding: 16px; border-radius: 0"
                                                                                    class="list-group-item active">
                                                                                    <div class="row">
                                                                                        @if ($addresses->title)
                                                                                            <div class="col-lg-12"
                                                                                                 style="text-align: center">
                                                                                                <b>{{ $addresses->title }}</b>
                                                                                            </div>
                                                                                        @else
                                                                                            <div style="text-align: center; color: #15549A; font-size: 20px">
                                                                                                <b>{{ __('front.required_information') }}</b>
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                </li>
                                                                                <li class="list-group-item">
                                                                                    <div class="row">
                                                                                        <div class="col-lg-4">
                                                                                            <b class="list-1">{{ __('front.contact_name') }}</b>
                                                                                        </div>
                                                                                        <div class="col-lg-8 list-2">{{ $addresses->contact_name or Auth::user()->full_name }}</div>
                                                                                    </div>
                                                                                </li>
                                                                                <li class="list-group-item">
                                                                                    @if (!$country->customer_id_in_address)
                                                                                        <div class="row">
                                                                                            <div class="col-lg-4">
                                                                                                <b class="list-1">{{ __('front.address_line_1') }}</b>
                                                                                            </div>
                                                                                            <div class="col-lg-8 list-2">{{ Auth::user()->customer_id }}
                                                                                                - {{  $addresses->address_line_1 }}</div>
                                                                                        </div>
                                                                                    @else
                                                                                        <div class="row">
                                                                                            <div class="col-lg-4">
                                                                                                <b class="list-1">{{ __('front.address_line_1') }}</b>
                                                                                            </div>
                                                                                            <div class="col-lg-8 list-2">{{ $addresses->address_line_1 }}
                                                                                                - {{  Auth::user()->customer_id }}</div>
                                                                                        </div>
                                                                                    @endif
                                                                                </li>
                                                                                @if ($addresses->address_line_2)
                                                                                    <li class="list-group-item">
                                                                                        <div class="row">
                                                                                            <div class="col-lg-4">
                                                                                                <b class="list-1">{{ __('front.address_line_2') }}</b>
                                                                                            </div>
                                                                                            <div class="col-lg-8 list-2">{{ $addresses->address_line_2 }}</div>
                                                                                        </div>
                                                                                    </li>
                                                                                @endif
                                                                                @if($addresses->city)
                                                                                    <li class="list-group-item">
                                                                                        <div class="row">
                                                                                            <div class="col-lg-4">
                                                                                                <b class="list-1">{{ __('front.city') }}</b>
                                                                                            </div>
                                                                                            <div class="col-lg-8 list-2">{{ $addresses->city }}</div>
                                                                                        </div>
                                                                                    </li>
                                                                                @endif
                                                                                @if($addresses->state)
                                                                                    <li class="list-group-item">
                                                                                        <div class="row">
                                                                                            <div class="col-lg-4">
                                                                                                <b class="list-1">{{ __('front.state') }}</b>
                                                                                            </div>
                                                                                            <div class="col-lg-8 list-2">{{ $addresses->state }}</div>
                                                                                        </div>
                                                                                    </li>
                                                                                @endif
                                                                                @if($addresses->region)
                                                                                    <li class="list-group-item">
                                                                                        <div class="row">
                                                                                            <div class="col-lg-4">
                                                                                                <b class="list-1">{{ __('front.region') }}</b>
                                                                                            </div>
                                                                                            <div class="col-lg-8 list-2">{{ $addresses->region }}</div>
                                                                                        </div>
                                                                                    </li>
                                                                                @endif
                                                                                @if($addresses->zip_code)
                                                                                    <li class="list-group-item">
                                                                                        <div class="row">
                                                                                            <div class="col-lg-4">
                                                                                                <b class="list-1">{{ __('front.zip_code') }}</b>
                                                                                            </div>
                                                                                            <div class="col-lg-8 list-2">{{ $addresses->zip_code }}</div>
                                                                                        </div>
                                                                                    </li>
                                                                                @endif
                                                                                @if($addresses->phone)
                                                                                    <li class="list-group-item">
                                                                                        <div class="row">
                                                                                            <div class="col-lg-4">
                                                                                                <b class="list-1">{{ __('front.phone') }}</b>
                                                                                            </div>
                                                                                            <div class="col-lg-8 list-2">{{ $addresses->phone }}</div>
                                                                                        </div>
                                                                                    </li>
                                                                                @endif
                                                                                @if($addresses->passport)
                                                                                    <li class="list-group-item">
                                                                                        <div class="row">
                                                                                            <div class="col-lg-4">
                                                                                                <b class="list-1">{{ __('front.passport') }}</b>
                                                                                            </div>
                                                                                            <div class="col-lg-8 list-2">{{ $addresses->passport }}</div>
                                                                                        </div>
                                                                                    </li>
                                                                                @endif
                                                                                @if($warehouse->id == 2)
                                                                                    <li class="list-group-item">
                                                                                        @php
                                                                                            $checked = "";
                                                                                                if($user->sms_verification_code_queried_at !== null || strtotime($user->sms_verification_code_queried_at) + 180 > time()){
                                                                                                    $checked = "checked=\"checked\"";
                                                                                                }
                                                                                        @endphp

                                                                                        <div class="row">
                                                                                            <div class="col-lg-4">
                                                                                                <div class="trendyol btn btn-success">Trendyol təsdiq</div>
                                                                                            </div>
                                                                                            <div class="col-lg-8">
                                                                                                <div class="weather">
{{--                                                                                                    <label class="switch">--}}
{{--                                                                                                        <input type="checkbox"--}}
{{--                                                                                                               id="verification_input"--}}
{{--                                                                                                               {{$checked}} @if($checked) disabled @endif>--}}
{{--                                                                                                        <span class="slider round"></span>--}}
{{--                                                                                                    </label>--}}
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </li>
                                                                                    <li class="list-group-item">
                                                                                        <span class="content-section_span_info list-2">Trendyol.com qeydiyyatı üçün Xarici ünvanlar bölümündə qeyd olunan nömrəni istifadə edə bilərsiniz, təsdiq kodu profilinizdə qeyd olunan nömrəyə göndəriləcək.</span>
                                                                                        <span class="content-section_span list-2"
                                                                                              style="display:none">Hal-hazırda digər istifadəçi kod üçün müraciət edib. Zəhmət olmasa <p>x</p> ərzində yenidən cəhd edin.</span>
                                                                                    </li>
                                                                                @endif
                                                                            </ul>
                                                                        @endforeach
                                                                        <div class=" st-accordion ">
                                                                            <div class="panel-group" id="accordion"
                                                                                 role="tablist"
                                                                                 aria-multiselectable="true">
                                                                                <div style="border-radius: 8px !important; overflow: hidden; "
                                                                                     class="panel panel-default ">
                                                                                    <div style=" background: #15549A; padding: 16px 24px ; "
                                                                                         class="panel-heading2 "
                                                                                         role="tab"
                                                                                         id="heading_{{ $key }}">
                                                                                        <div style=""
                                                                                             class="panel-title">
                                                                                            <a style="color:#fff ; font-size: 20px; font-weight: 500;display: flex; justify-content: space-between; align-items: center "
                                                                                               role="button"
                                                                                               data-toggle="collapse"
                                                                                               data-parent="#accordion"
                                                                                               href="#collapse_{{ $key }}"
                                                                                               aria-expanded="true"
                                                                                               aria-controls="collapse_{{ $key }}">
                                                                                                <div style="display: flex; justify-content: center; align-items: center; gap: 24px">
                                                                                                    <svg width="32"
                                                                                                         height="32"
                                                                                                         viewBox="0 0 32 32">
                                                                                                        <rect width="32"
                                                                                                              height="32"
                                                                                                              fill="none"
                                                                                                              rx="1.33"/>
                                                                                                        <circle cx="16"
                                                                                                                cy="16"
                                                                                                                r="12"
                                                                                                                fill="none"
                                                                                                                stroke="#fff"
                                                                                                                stroke-width="2"/>
                                                                                                        <g fill="#fff">
                                                                                                            <circle cx="16"
                                                                                                                    cy="12"
                                                                                                                    r="1.33"/>
                                                                                                            <rect x="14.67"
                                                                                                                  y="14.67"
                                                                                                                  width="2.67"
                                                                                                                  height="5.33"
                                                                                                                  rx="1.33"/>
                                                                                                        </g>
                                                                                                    </svg>{{ __('front.more_info') }}
                                                                                                </div>
                                                                                                <div style="display: flex; align-items: center">
                                                                                                    <svg class="accordion-icon"
                                                                                                         width="24"
                                                                                                         height="24"
                                                                                                         viewBox="0 0 24 24"
                                                                                                         fill="none"
                                                                                                         xmlns="http://www.w3.org/2000/svg">
                                                                                                        <path d="M7 10L12 15L17 10"
                                                                                                              stroke="#fff"
                                                                                                              stroke-width="2"
                                                                                                              stroke-linecap="round"
                                                                                                              stroke-linejoin="round"/>
                                                                                                    </svg>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div style="background: #15549A"
                                                                                         id="collapse_{{ $key }}"
                                                                                         class="panel-collapse @if(!$key) in @endif"
                                                                                         role="tabpanel"
                                                                                         aria-labelledby="heading_{{ $key }}">
                                                                                        <div class="panel-body">
                                                                                            @if($addresses->translateOrDefault($_lang)->attention)
                                                                                                <div>
                                                                                                    <div class="row">
                                                                                                        <div style="color: #fff; line-height: 43px; font-size: 18px;font-weight: 400"
                                                                                                             class="col-lg-12">{!! $addresses->translateOrDefault($_lang)->attention !!}</div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            @endif

                                                                                            @if($country->pages->count())
                                                                                                @foreach($country->pages as $countryPage)
                                                                                                    <div>
                                                                                                        <a style="color: #f51f8a; line-height: 43px; font-size: 18px;font-weight: 400"
                                                                                                           target="_blank"
                                                                                                           href="{{ route('pages.show', $countryPage->translateOrDefault($_lang)->slug) }}">
                                                                                                            {{ $countryPage->translateOrDefault($_lang)->title }}
                                                                                                        </a>
                                                                                                    </div>
                                                                                                @endforeach
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        @endif
                                                    @endforeach
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
        {{--document.getElementById("verification_input").addEventListener('change', function (e) {--}}
        {{--    const API_URL = "{{ route('sms-verification-code') }}";--}}
        {{--    if (e.target.checked === true) {--}}
        {{--        fetch(API_URL, {--}}
        {{--            method: "POST",--}}
        {{--            headers: {--}}
        {{--                'Content-Type': 'application/json',--}}
        {{--                'Accept': 'application/json',--}}
        {{--            },--}}
        {{--            body: JSON.stringify({--}}
        {{--                status: e.target.checked--}}
        {{--            })--}}
        {{--        })--}}
        {{--            .then(response => response.json())--}}
        {{--            .then(data => {--}}
        {{--                if (!data.success) {--}}
        {{--                    console.log("Falseee");--}}
        {{--                } else {--}}
        {{--                    alert(data.data);--}}
        {{--                }--}}
        {{--            })--}}
        {{--            .catch(error => {--}}
        {{--                if (error.response && error.response.status !== 403) {--}}
        {{--                    error.response.json().then(msg => {--}}
        {{--                        document.querySelector(".content-section_span p").textContent = msg.data;--}}
        {{--                        document.querySelector(".content-section_span").style.display = 'flex';--}}
        {{--                    });--}}
        {{--                }--}}
        {{--            });--}}
        {{--    }--}}
        {{--});--}}
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
