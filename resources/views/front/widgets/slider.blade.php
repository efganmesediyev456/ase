<div class="slider" id="slider">
@foreach($sliders as $slider)
    <!-- slider -->
        <div class="slider-gradient-img"><img src="{{ $slider->image }}"
                                              alt="{{ $slider->translateOrDefault($_lang)->title }}" class="">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                        <div class="slider-captions">
                            <!-- slider-captions -->
                            <h1 class="slider-title">{!! $slider->translateOrDefault($_lang)->title !!}</h1>
                            <p class="slider-text hidden-xs">{!! $slider->translateOrDefault($_lang)->content !!}</p>
                            <a href="{{ $slider->url }}" class="btn btn-outline hidden-xs">{{ $slider->translateOrDefault($_lang)->button_label }}</a></div>
                        <!-- /.slider-captions -->
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@push('css')
    <style>
        .slider-gradient-img {
            background: -moz-linear-gradient(-135deg,rgba(25,46,167,.96) 0,rgba(24,40,134,.66) 10%,rgba(148,58,158,.29) 64%);
            background: -webkit-linear-gradient(-135deg,rgba(25,46,167,.96) 0,rgba(24,40,134,.66) 10%,rgba(148,58,158,.29) 64%);
            background: linear-gradient(45deg,rgba(25,46,167,.96) 0,rgba(24,40,134,.66) 10%,rgba(148,58,158,.29) 64%);
        }
        @media screen and (max-width: 1440px) and (min-width: 1300px) {
            .slider-captions {
                position: absolute;
                bottom: 150px;
            }
        }
        .slider-captions * {
            color: #fff;
        }
    </style>
@endpush