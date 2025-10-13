@extends(config('saysay.crud.layout'))

@section('content')
@if(!empty($alertText))
<div class="alert alert-{{$alertType}}" role="alert">
  {{$alertText}}
</div>
@endif
    <div class="row">
        <div class="col-lg-12 col-lg-offset-0 col-md-12 col-xs-12">
            <div class="panel panel-flat" >

                <div class="panel-heading">
                    <h6>
			{{ isset($_view['name']) ? str_plural($_view['name']) : null }}   {{$parcel_name}}
                    </h6>
                </div>

                <div class="panel-body">

                        @permission('create-tracks')
                        {!! Form::open(['id' => 'Tracks_import_form', 'method' => 'post', 'route' => $_view['mod_name'].".import", 'files' => true ]) !!}
                        <div class="col-md-3" >
                            <label>Parcel name</label>
                            <div class="input-group">
                                <input id="parcel" type="text" name="parcel" value="{{$parcel_name}}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4" >
                            <label>Import Excel File</label>
                            <div class="input-group">
                                <input id="import_excel" type="file" name="import_excel" value="" accept="*.xlsx">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" title="Look Up" id="sync_form_button" class="btn btn-primary btn-icon" style="margin-top: 28px;"><i
                                        class="icon-loop"></i></button>
                        </div>
                        {!! Form::close() !!}
                        @endpermission

                    <div class="table-responsive overflow-visible" style="padding-top: 70px;">
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
                            @foreach($tracks as $track)
                                @include('admin.widgets.track', ['item' => $track])
                            @endforeach
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
