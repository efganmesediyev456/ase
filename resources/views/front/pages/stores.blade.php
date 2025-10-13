@extends('front.layout')

@section('content')

    @include('front.sections.page-header')
    @include('front.widgets.modal', ['modalId' => 'ajaxModal'])

    <div class="custom-container">
        <div  class="row section-space100">
            <div style="display: flex; align-items: center; justify-content: space-between ; flex-wrap: wrap" class=" floating-stores">
                @foreach($items->chunk(3) as $datas)
                    @each('front.pages.stores.' . $singleView .'-item', $datas, 'item')
                @endforeach
            </div>
        </div>
    </div>
@endsection