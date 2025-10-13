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
                        Sync Parcel {{$parcel_name}} with Manifest code {{$sync_id}}
                    </h6>
                </div>

                <div class="panel-body">

                        @permission('read-logic_sync')
                        {!! Form::open(['id' => 'logic_sync_form', 'method' => 'get' ]) !!}
                        <div class="col-md-4" >
                            <label>Manifest Code</label>
                            <div class="input-group">
                                <input id="done" type="hidden" name="done" value="0">
                                <input id="parcel" type="hidden" name="parcel" value="{{$parcel_name}}">
                                <input id="sync_id" type="text" name="sync_id" value="{{$sync_id}}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" title="Look Up" id="sync_form_button" class="btn btn-primary btn-icon" style="margin-top: 28px;"><i
                                        class="icon-search4"></i></button>
                        </div>
                        {!! Form::close() !!}
                        @endpermission


                    @if(count($tracks)>0)
                        {!! Form::open(['id' => 'logic_sync_form', 'method' => 'get' ]) !!}
                        <div class="col-md-4" >
                            <label>Parcel name</label>
                            <div class="input-group">
                                <input id="done" type="hidden" name="done" value="1">
                                <input id="sync_id" type="hidden" name="sync_id" value="{{$sync_id}}"
                                       class="form-control">
                                <input id="parcel" type="text" name="parcel" value="{{$parcel_name}}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" title="Do Sync" id="sync_form_button" class="btn btn-primary btn-icon" style="margin-top: 28px;"><i
                                        class="icon-loop"></i></button>
                        </div>
                        {!! Form::close() !!}
                    @endif

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
                            @foreach($syncs as $sync)
                                @include('admin.widgets.logic_sync', ['item' => $sync])
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
