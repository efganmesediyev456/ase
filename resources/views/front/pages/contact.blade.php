@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
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
                                        <h1>{{ __('front.contact.title') }}</h1>
                                        <p>{{ __('front.contact.sub_title') }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    {!! Form::open(['class' => 'contact-us']) !!}
                                        <div class=" ">
                                            <!-- Text input-->
                                            <div class="col-md-4 col-sm-12 col-xs-12">
                                                <div class="form-group">
                                                    <label class="sr-only control-label" for="name">{{ __('front.contact.name') }}<span class=" "> </span></label>
                                                    <input id="name" name="name" type="text" placeholder="{{ __('front.contact.name') }}" class="form-control input-md" required>
                                                </div>
                                            </div>
                                            <!-- Text input-->
                                            <div class="col-md-4 col-sm-12 col-xs-12">
                                                <div class="form-group">
                                                    <label class="sr-only control-label" for="email">{{ __('front.contact.email') }}<span class=" "> </span></label>
                                                    <input id="email" name="email" type="email" placeholder="{{ __('front.contact.email') }}" class="form-control input-md" required>
                                                </div>
                                            </div>
                                            <!-- Text input-->
                                            <div class="col-md-4 col-sm-12 col-xs-12">
                                                <div class="form-group">
                                                    <label class="sr-only control-label" for="phone">{{ __('front.contact.phone') }}<span class=" "> </span></label>
                                                    <input id="phone" name="phone" type="text" placeholder="{{ __('front.contact.phone') }}" class="form-control input-md" required>
                                                </div>
                                            </div>
                                            <!-- Select Basic -->
                                            <div class="col-md-12 col-xs-12">
                                                <div class="form-group">
                                                    <label class="control-label" for="message"> </label>
                                                    <textarea class="form-control" id="message" rows="7" name="message" placeholder="{{ __('front.contact.message') }}"></textarea>
                                                </div>
                                            </div>
                                            <!-- Button -->
                                            <div class="col-md-12 col-xs-12">
                                                <button type="submit" class="btn btn-default">{{ __('front.contact.submit') }}</button>
                                            </div>
                                        </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                            <!-- /.section title start-->
                        </div>
                        <div class="contact-us mb60">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb60  section-title">
                                        <!-- section title start-->
                                        <h1>{{ __('front.contact.help.title') }}</h1>
                                        <p class="lead">{{ __('front.contact.help.sub_title') }}</p>
                                    </div>
                                    <!-- /.section title start-->
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-xs-12">
                                    <div class="bg-boxshadow pinside60 outline text-center mb30 contact-box">
                                        <div class="mb40"><i class="icon-briefcase icon-2x icon-default"></i></div>
                                        <h2 class="capital-title">{{ __('front.contact.help.office') }}</h2>
                                        <p>{{ $setting->address }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4 col-xs-12">
                                    <div class="bg-boxshadow pinside60 outline text-center mb30 contact-box">
                                        <div class="mb40"><i class="icon-phone-call icon-2x icon-default"></i></div>
                                        <h2 class="capital-title">{{ __('front.contact.help.call_us_at') }}</h2>
                                        <p><a href="tel://{{ $setting->phone }}">{{ $setting->phone }}</a></p>
                                    </div>
                                </div>
                                <div class="col-md-4 col-xs-12">
                                    <div class="bg-boxshadow pinside60 outline text-center mb30 contact-box">
                                        <div class="mb40"> <i class="icon-letter icon-2x icon-default"></i></div>
                                        <h2 class="capital-title">{{ __('front.contact.help.email') }}</h2>
                                        <p><a href="mailto://{{ $setting->email }}">{{ $setting->email }}</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="map" id="googleMap"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')

<script>
    <?php $location = explode(",", $setting->location); ?>
    function initMap() {
        var myLatLng = {
            lat: <?= $location[0]?>,
            lng: <?= isset($location[1]) ? $location[1] : '40'; ?>
        };

        var map = new google.maps.Map(document.getElementById('googleMap'), {
            zoom: 17,
            center: myLatLng,
            scrollwheel: false,

        });
        var image = 'front/images/map-pin.png';
        var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            icon: image,
            title: '<?= config('app.name') ?>'

        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?&callback=initMap" async defer></script>
@endpush