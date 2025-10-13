@if (isset($setting))

    <style>
        .footer {
            background-color: #0e1b2f;
            padding-block: 72px  24px;
        }

        .footer-content {
            flex-direction: column;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
        }

        .footer-column2{
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap:  40px;
            place-content: center;
            place-items: center;
        }
        .footer-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }


        @media (min-width: 992px) {
            .footer-column {
                align-items: flex-start;
                text-align: left;
            }
            .footer-content {
                flex-direction: row;
            }
        }

        .footer-logo {
            margin-bottom: 16px;
        }

        .footer-text {
            font-size: 16px;
            line-height: 1.5;
            color: #778191;
            font-weight: 400;
        }

        .footer-heading {
            color: #778191;
            font-weight: 500;
            font-size: 18px;
            line-height: 1.5;
            margin-bottom: 14px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .footer-links a {
            color: #778191;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.2s ease-in-out;
            font-weight: 300;
        }

        .footer-links a:hover {
            color: #ffffff;
        }

        .app-badges {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }

        .app-badge-img {
            height: 40px;
        }

        .social-icons {
            display: flex;
            gap: 12px;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s ease-in-out;
        }

        .social-icon:hover {
            opacity: 0.8;
        }

        .social-icon img {
            height: 24px;
            width: 24px;
        }

        .footer-copyright{
            text-align: left;
            color: #778191;
            font-size: 16px;
            margin-top: 40px;
        }



    </style>
    <footer class="footer">

        <div class="custom-container">
            <div class="footer-content">
                <div class="footer-column footer-info">
                    <img src="{{asset('uploads/setting/59a01a371b301.png') }}" alt="ASE Shopping Logo" class="footer-logo" />
                    <a style="color: #778191" href="tel:{{ $setting->phone }}" class="footer-text">{{ $setting->phone }} </a>
                    <p style="max-width: 300px" class="footer-text">{{ $setting->address }}</p>
                </div>
                <div class="footer-column2">
                    <div class="footer-column">
                        <h3 class="footer-heading"> {{ __('front.footer.find') }}</h3>
                        <ul class="footer-links">
                            <li><a style="text-transform: uppercase" href="{{ route('shop') }}">{{ __('front.menu.shop') }}</a></li>
                            <li><a style="text-transform: uppercase" href="{{ route('tariffs') }}">{{ __('front.menu.tariffs') }}</a></li>
                            <li><a style="text-transform: uppercase" href="{{ route('calculator') }}">{{ __('front.menu.calculator') }}</a></li>
                            <li><a style="text-transform: uppercase" href="{{ route('news') }}">{{ __('front.menu.news') }}</a></li>
                        </ul>
                    </div>

                    <div class="footer-column">
                        <h3 class="footer-heading">{{ __('front.footer.other_links') }}</h3>
                        <ul class="footer-links">
                            <li><a style="text-transform: uppercase" href="{{ route('login') }}">{{ __('front.menu.sign_in') }}</a></li>
                            <li><a style="text-transform: uppercase" href="{{ route('register') }}">{{ __('front.menu.sign_up') }}</a></li>
                            <li><a style="text-transform: uppercase" href="">{{ __('front.menu.vacancy') }}</a></li>
                            <li><a style="text-transform: uppercase" href="{{ route('faq') }}">{{ __('front.menu.faq') }}</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Column 4: Mobile App and Social Media -->
                <div class="footer-column">
                    <h3 class="footer-heading">{{ __('front.app.title') }}</h3>
                    <div class="app-badges">
                        <a href="https://play.google.com/store/apps/details?id=az.com.aseshop.mobile" target="_blank" aria-label="Get it on Google Play">
                            <img src="{{asset('front/new/app-store-img.png')}}" alt="Get it on Google Play" class="app-badge-img" />
                        </a>
                        <a href="https://apps.apple.com/us/app/aseshop-mobile/id6443923436" target="_blank"  aria-label="Download on the App Store">
                            <img src="{{asset('front/new/google-play-img.png')}}" alt="Download on the App Store" class="app-badge-img" />
                        </a>
                    </div>

                    <h3 class="footer-heading">{{ __('front.footer.follow_us') }}</h3>
                    <div class="social-icons">
                        @if($setting->facebook)
                            <a style="background: #f4f6fa; width: 40px; height: 40px" href="{{ $setting->facebook }}" class="social-icon" target="_blank" aria-label="Facebook">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                                            fill="#6B7280"
                                    />
                                </svg>
                            </a>
                        @endif
                        @if($setting->instagram)
                            <a style="background: #f4f6fa; width: 40px; height: 40px" href="{{ $setting->instagram }}" class="social-icon" target="_blank" aria-label="Instagram">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                                            fill="#6B7280"
                                    />
                                </svg>
                            </a>
                        @endif
                        @if($setting->linkedin)
                            <a style="background: #f4f6fa; width: 40px; height: 40px" href="{{ $setting->linkedin }}" target="_blank" class="social-icon" aria-label="LinkedIn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"
                                            fill="#6B7280"
                                    />
                                </svg>
                            </a>
                        @endif
                        <a style="background: #f4f6fa; width: 40px; height: 40px" href="https://www.linkedin.com/company/ase-express-ocs-worldwide-logistics/?originalSubdomain=az" target="_blank"  class="social-icon" aria-label="LinkedIn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                        d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"
                                        fill="#6B7280"
                                />
                            </svg>
                        </a>
                        @if($setting->whatsapp || $setting->whatsapp2)
                            <a style="background: #f4f6fa; width: 40px; height: 40px" href="https://wa.me/{{ $setting->whatsapp ?: $setting->whatsapp2 }}" class="social-icon" target="_blank" aria-label="WhatsApp">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.064 3.488"
                                            fill="#6B7280"
                                    />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="footer-copyright">
                <p>{{ __('front.footer.copyright') }} {{ date("Y") }}. {{ __('front.footer.copyright2') }}</p>
            </div>
        </div>
    </footer>
    {{--    <div class="footer section-space60">--}}
    {{--        <!-- footer -->--}}
    {{--        <div class="container">--}}
    {{--            <div class="row">--}}

    {{--                <div class="col-md-1 col-sm-12 col-xs-12 mb-3">--}}
    {{--                    <img src="{{ $setting->footer_logo ? asset('uploads/setting/' . $setting->footer_logo) : asset('front/images/logo-footer.png') }}">--}}
    {{--                </div>--}}
    {{--                <div class="col-md-2 col-sm-12 col-xs-12 mb-3">--}}
    {{--                    <a target="_blank" href="https://apps.apple.com/app/ase-shop-mobile/id6443923436"><img src="{{ asset('front/images/appstore_2.png') }}"></a>--}}
    {{--                    <a target="_blank" href="https://play.google.com/store/apps/details?id=az.com.aseshop.mobile"><img src="{{ asset('front/images/gplay_2.png') }}"></a>--}}
    {{--                </div>--}}
    {{--                <div class="col-md-6 col-sm-12 col-xs-12">--}}
    {{--                    <div class="row">--}}
    {{--                        <div class="col-md-7 col-xs-12">--}}
    {{--                            <a class="mb-3"><span><i class="fa fa-map-marker"></i> </span>{{ $setting->address }} </a>--}}
    {{--                        </div>--}}
    {{--                        <div class="col-md-5 col-xs-12">--}}
    {{--                            <a class="mb-3"  href="tel:{{ $setting->phone }}"><span><i--}}
    {{--                                            class="fa fa-phone"></i></span>&nbsp; {{ $setting->phone }}</a>--}}
    {{--                        </div>--}}
    {{--                    </div>--}}
    {{--                </div>--}}

    {{--                <div class="col-md-3 col-sm-12 col-xs-12 mb-3">--}}
    {{--                    <div class="row">--}}
    {{--                        @if ($setting->facebook)--}}
    {{--                            <div class="col-md-6 col-xs-6">--}}
    {{--                                <a target="_blank" href="{{ $setting->facebook }}" target="_blank"><span><i--}}
    {{--                                                class="fa fa-facebook"></i></span>&nbsp; Facebook</a>--}}
    {{--                            </div>--}}
    {{--                        @endif--}}
    {{--                        @if ($setting->twitter)--}}
    {{--                            <div class="col-md-6 col-xs-6">--}}
    {{--                                <a target="_blank" href="{{ $setting->twitter }}"><span><i--}}
    {{--                                                class="fa fa-twitter"></i></span>&nbsp; Twitter</a>--}}
    {{--                            </div>--}}
    {{--                        @endif--}}
    {{--                        @if ($setting->instagram)--}}
    {{--                            <div class="col-md-6 col-xs-6">--}}
    {{--                                <a target="_blank" href="{{ $setting->instagram }}"><span><i--}}
    {{--                                                class="fa fa-instagram"></i></span>&nbsp; Instagram</a>--}}
    {{--                            </div>--}}
    {{--                        @endif--}}
    {{--                        @if ($setting->linkedin)--}}
    {{--                            <div class="col-md-6 col-xs-6">--}}
    {{--                                <a target="_blank" href="{{ $setting->linkedin }}"><span><i class="fa fa-linkedin"></i></span>&nbsp;--}}
    {{--                                    Linked In</a>--}}
    {{--                            </div>--}}
    {{--                        @endif--}}
    {{--                        @if ($setting->whatsapp)--}}
    {{--                            <div class="col-md-6 col-xs-6">--}}
    {{--                                <a target="_blank" href="https://wa.me/{{ $setting->whatsapp }}" target="_blank"><span><i--}}
    {{--                                                class="fa fa-whatsapp"></i></span>&nbsp; Whatsapp US/GB</a>--}}
    {{--                            </div>--}}
    {{--                        @endif--}}
    {{--                        @if ($setting->whatsapp2)--}}
    {{--                            <div class="col-md-6 col-xs-6">--}}
    {{--                                <a target="_blank" href="https://wa.me/{{ $setting->whatsapp2 }}" target="_blank"><span><i--}}
    {{--                                                class="fa fa-whatsapp"></i></span>&nbsp; Whatsapp TR</a>--}}
    {{--                            </div>--}}
    {{--                        @endif--}}
    {{--                    </div>--}}

    {{--                    <!-- /.widget footer -->--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="container">--}}
    {{--            <p>--}}
    {{--                Copyright &copy; ASE Express 2025. All Rights Reserved--}}
    {{--            </p>--}}
    {{--        </div>--}}
    {{--    </div>--}}
    <!-- /.footer -->

@endif
{{--<div class="tiny-footer">
    <!-- tiny footer -->
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-6">
                <p>© {{ __('front.copyright') }} {{ date("Y") }} | Aseshop.az</p>
            </div>
        </div>
    </div>
</div>
<!-- /.tiny footer -->--}}
<!-- back to top icon -->
<a href="#0" class="cd-top" title="Go to top">Top</a>
