@extends('front.layout')

@section('content')
    @include('front.sections.page-header')

    <div class=" ">
        <!-- content start -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrapper-content bg-white pinside40">
                        <div class="">
                            {!! $page->translateOrDefault($_lang)->content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content end -->

@endsection