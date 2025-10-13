@extends(config('saysay.crud.layout'))

@section('content')
@if(!empty($alertText))
<div class="alert alert-{{$alertType}}" role="alert">
  {{$alertText}}
</div>
@endif
     <div id="courier_tracks_scan_url" data-scan-url="{{ route('courier_tracks.scan') }}"></div>
            <div class="panel panel-flat" >
    <div class="row">

                <div class="panel-heading" style="height: 130px;">
                    <div class="heading-delements">
                        <div class="col-lg-12 col-md-12 col-xs-12">
			<H1 id='tracks_count' style="color: blue;"></H1>
			<br>
                        </div>
                        @permission('read-courier_tracks')
                        {!! Form::open(['id' => 'manual_add_package', 'class' => 'no_loading' ]) !!}
                        <div class="col-md-3" >
                            <label>Track CWB No</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="icon-barcode2"></i></div>
                                <input id="manual_add" type="text" name="cwb" value=""
                                       class="form-control">
                            </div>
                            <p class="help-block">Scan Or enter track CWB</p>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" id="paid_form_button" class="btn btn-primary btn-icon" style="margin-top: 28px;"><i
                                        class="icon-checkmark"></i></button>
                        </div>
                        <div class="col-lg-12 col-md-12 col-xs-12" >
                            <label>Select courier</label>
                            <div class="input-group col-lg-12 col-xs-12 col-md-12">
			        @include('crud::fields.' . $search['type'], ['field' => $search])
                            </div>
                        </div>
                        {!! Form::close() !!}
                        @endpermission
                    </div>
                </div>
        <div class="col-lg-12 col-lg-offset-0 col-md-12 col-xs-12">

                <div class="panel-body">
                    <label>Courier tracks scaned</label>
                    <div class="table-responsive overflow-visible">
                        <table class="table table-hover responsive table-striped">
                            <thead>
                            <tr>
                                @foreach($_list as $key => $head)
                                    <th>{{ is_array($head) ? (array_key_exists('label', $head) ? $head['label'] : ucfirst(str_replace("_", " ", $key))) : ucfirst(str_replace("_", " ", $head)) }}</th>
                                @endforeach
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody id="scanned" >
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
    <script>
        $.validate();
    </script>
@endpush
