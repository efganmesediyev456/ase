@extends('panel.layouts.app')

@php

        @endphp

@section('title', 'Qapida - sistemə giriş')

@section('content')
    @if(Session::has('success'))
        <audio id="successPlayer" autoplay>
            <source src="{{ url('/panel/success.mp3') }}" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
    @endif

    @if(Session::has('warning'))
        <audio id="successPlayer" autoplay>
            <source src="{{ url('/panel/warning.mp3') }}" type="audio/wav">
            Your browser does not support the audio element.
        </audio>
    @endif

    @if(Session::has('errors'))
        <audio id="successPlayer" autoplay>
            <source src="{{ url('/panel/error.wav') }}" type="audio/wav">
            Your browser does not support the audio element.
        </audio>
    @endif

    <div class="box box-primary">
        <div class="box-header">

            <form method="post"
                  action="{{ route('panel.qapida_warehouse.store', $group->id) }}"
                  enctype="multipart/form-data"
                  class="form-prevent-multiple-submits"
                  id="">
                @csrf
                <div class="form-group mb-3">
                    <label for="">Barcode</label>
                    <input type="text" name="barcode" id="barcode"
                           class="form-control"
                           autocomplete="off"
                           value="" autofocus>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" name="button" type="submit" id="">Save</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('footer-js')

@endsection



