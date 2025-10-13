@extends('front.layout')

@section('content')
    @include('front.sections.page-header')
    @include('front.widgets.modal', ['modalId' => 'ajaxModal', 'noClick' => true])
    <div class="my-packages">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content bg-white pinside30">
                        @include('front.user.declaration')
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection