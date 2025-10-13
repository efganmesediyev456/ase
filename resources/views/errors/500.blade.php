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
                                    <img src="{{ asset('front/images/error/500.png') }}" class="" alt="">
                                </div>
                                <div class="error-ctn text-center">
                                    <h2 class="msg">Üzr istəyirik</h2>
                                    <h1 class="error-title mb40">Xəta baş verdi</h1>
                                    <p class="mb40">Hal hazırda sistemdə xəta yaranmışdır. Bir az sonra yenidən cəhd edin.</p>
                                    <a href="/" class="btn btn-default text-center">Əsas səhifəyə keç</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection