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
    </style>

    <div class="custom-container section-space100">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12 col-sm-12 st-accordion col-xs-12">
                    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                        @foreach($faqs as $key => $item)
                            <div style="border-radius: 8px !important; overflow: hidden; background: #e7e7e7; border: 1px solid #dedede"  class="panel panel-default ">
                                <div style=" background: #e7e7e7; padding: 20px ; " class="panel-heading2 " role="tab" id="heading_{{ $key }}">
                                    <div  class="panel-title">
                                        <a style="color:#15549a ; font-size: 20px; font-weight: 500; display: flex; justify-content: space-between; align-items: center" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_{{ $key }}" aria-expanded="{{ !$key ? 'true' : 'false' }}" aria-controls="collapse_{{ $key }}">
                                            {{ $item->translateOrDefault($_lang)->question }}
                                            <div style="display: flex; align-items: center">
                                                <svg class="accordion-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M7 10L12 15L17 10" stroke="#15549a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                        </a>

                                    </div>
                                </div>
                                <div id="collapse_{{ $key }}" class="panel-collapse collapse @if(!$key) in @endif" role="tabpanel" aria-labelledby="heading_{{ $key }}">
                                    <div class="panel-body">
                                        {!! $item->translateOrDefault($_lang)->answer !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection