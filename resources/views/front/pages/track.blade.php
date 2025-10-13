@extends('front.layout')

@section('content')
    <div class=" ">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content bg-white pinside40">
                        <div class="contact-form mb60">
                            <div class=" ">
                                <div class="col-md-offset-2 col-md-8 col-sm-12 col-xs-12">
                                    <div class="mb60 section-title text-center  ">
                                        <!-- section title start-->
                                        <h1>{{ __('front.track.title') }}</h1>
                                        <p>{{ __('front.track.sub_title') }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                </div>
                            </div>
                            <!-- /.section title start-->
                        </div>
                        <div class="map" id="googleMap"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={ENTER YOUR KEY}"></script>
<script src="https://unpkg.com/location-picker/dist/location-picker.min.js"></script>
@endpush
