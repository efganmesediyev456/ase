@extends('front.layout')

@section('content')

    <div class=" ">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content bg-white pinside60">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-6 col-sm-12">
                                <div class="error-img mb60">
                                    <img src="{{ asset('front/images/error/404.png') }}" class="" alt="">
                                </div>
                                <div class="error-ctn text-center">
                                    <h2 class="msg">Opps</h2>
                                    <h1 class="error-title mb40">Səhifə tapılmadı</h1>
                                    <p class="mb40">Axtardığınız səhifə tapılmadı və ya siz düzgün linkə daxil olmamısınız.</p>
                                    <a href="{{ route('home') }}" class="btn btn-default text-center">Ana səhifəyə keç</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection