<?php
if (! isset($setting)) $setting = App\Models\Setting::find(1);
?>

<style>
    @media (max-width:400px){
        .top-nav{
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .langs{
            width: 100px;
            display: flex;
            justify-content: start;
            gap: 5px;
            border-right: none !important;
        }
    }
</style>

<div style="background: #4274ac !important;margin-bottom: 14px; padding-block:14px " class="top-bar">
    <!-- top-bar -->
    <div  class="custom-container">
        <div class="row">
            <div class="col-md-4 hidden-xs hidden-sm">
                <div class="top-nav">
                    <span class="top-text">
                        <a style="color: #fff ; font-size: 12px; font-weight: 400" href="{{ route('home') }}">{{ __('front.menu.home') }}</a>
                    </span>
                    <span class="top-text">
                        <a style="color: #fff ; font-size: 12px; font-weight: 400" href="{{ route('about') }}">{{ __('front.menu.about_us') }}</a>
                    </span>
                    <span class="top-text">
                        <a style="color: #fff ; font-size: 12px; font-weight: 400" href="{{ route('contact') }}">{{ __('front.menu.contact_us') }}</a>
                    </span>
                </div>
            </div>
            <div class="col-md-8 col-sm-12 text-right col-xs-12">
                <div class="top-nav">
                    <span class="auth">
                        @if (Auth::check())
                            <span class="top-text  hidden-sm hidden-xs">
                                <a style="color: #fff ; font-size: 12px; font-weight: 400" href="{{ route('my-packages') }}">{{ Auth::user()->full_name }}</a>
                            </span>
                            <span class="top-text  hidden-md hidden-lg" style="padding-left: 12px">
                                <a  style="color: #fff ; font-size: 12px; font-weight: 400" href="{{ route('my-packages') }}">
                                    <i style="font-size: 15px;position: relative;top: 2px;color: #fff;"
                                       class="fa fa-user"></i>
                                </a>
                            </span>
                            <span class="top-text  hidden-sm hidden-xs">
                                <a style="color: #fff ; font-size: 12px; font-weight: 400" href="{{ route('auth.logout') }}">{{ __('front.logout') }}</a>
                            </span>
                        @else
                            <span class="top-text">
                                <a style="color: #fff ; font-size: 12px; font-weight: 400" href="{{ route('login') }}">{{ __('front.menu.sign_in') }}</a>
                            </span>
                            <span class="top-text">
                                <a style="color: #fff ; font-size: 12px; font-weight: 400" href="{{ route('register') }}">{{ __('front.menu.sign_up') }}</a>
                            </span>
                        @endif
                    </span>
                    @if(count(config('translatable.locales_name')) > 1)
                        <select style="border: none; background: none;padding-inline: 10px; color: #fff; font-size: 12px; font-weight: 400; outline: none;  appearance: revert; text-transform: uppercase" class="lang-switcher" onchange="window.location.href = '/' + this.value">
                            @foreach(config('translatable.locales_name') as $lang => $langName)
                                <option style="background: #15549A; border: none; padding: 10px" value="{{ $lang }}" @if($lang == App::getLocale()) selected @endif>
                                    {{ substr($langName, 0, 3) }}
                                </option>
                            @endforeach
                        </select>
                    @endif



                </div>
            </div>
        </div>
    </div>
</div>
<div id="sticky-wrapper" class="sticky-wrapper">
    <div class="header" >
        <div class="custom-container">
            <div class="row">
                <div style="padding: 0 !important;"  class="col-md-1 col-xs-6 ">
                    <div class="logo">
                        <a href="{{ route('home') }}">
                            <img src="{{ $setting->header_logo ? asset('uploads/setting/' . $setting->header_logo) : asset('front/images/logo-header.png') }}"></a>
                    </div>
                </div>
                <div style="padding: 0 !important;" class="col-md-11 col-xs-6">
                    <div style="gap:10px;" id="navigation" class="login-flex3">
                        <ul class="menu-ul">
                            <li>
                                <a href="{{ route('shop') }}" class="animsition-link">{{ __('front.menu.shop') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('tariffs') }}"
                                   class="animsition-link">{{ __('front.menu.tariffs') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('calculator') }}"
                                   class="animsition-link">{{ __('front.menu.calculator') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('news') }}" class="animsition-link">{{ __('front.menu.news') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('faq') }}" class="animsition-link">{{ __('front.menu.faq') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('vacancy') }}"
                                   class="animsition-link">{{ __('front.menu.vacancy') }}</a>
                            </li>
                            <li class="hidden-lg hidden-md">
                                <a href="{{ route('auth.logout') }}">{{ __('front.logout') }}</a>
                            </li>
                            @if (Auth::check())
                                <li  style="background: #F51F8A;border-radius: 4px;padding:8px 24px !important;">
                               <span>
                                <a href="{{ route('my-packages', ['id' => 6]) }}/?declaration=on"
                                   data-remote="false"
                                   style="color: #fff; font-weight: 500; ">{{ __('front.early_declaration') }}</a>
                                </span>
                                </li>
                                <li  style="background: #15549A;border-radius: 4px;padding:8px 24px !important; margin-left: 8px">
                                <span>
                                <a href="{{ route('my-orders.create') }}"
                                   style="color: #fff;font-weight: 500;">{{ __('front.order') }}</a>
                                    </span>
                                </li>
                            @endif
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
