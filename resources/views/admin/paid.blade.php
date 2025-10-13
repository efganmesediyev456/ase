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

                <div class="panel-heading" style="height: 130px;">
                    <div class="heading-delements">

                        @permission('read-paids')
                        {!! Form::open(['id' => 'paid_form', 'method' => 'get' ]) !!}
                        <div class="col-md-4" >
                            <label>Package CWB No</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="icon-barcode2"></i></div>
                                <input id="paid_form_input" type="text" name="cwb" value=""
                                       class="form-control">
                            </div>
                            <p class="help-block">Or enter package CWB No manually</p>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" id="paid_form_button" class="btn btn-primary btn-icon" style="margin-top: 28px;"><i
                                        class="icon-search4"></i></button>
                        </div>
                        {!! Form::close() !!}
                        @endpermission

                    </div>
                </div>

                <div class="panel-body">
                    @permission('read-paids')
                    @if(empty($package->id))
                          <div class="alert alert-light">Please scan a package</div>
		    @else
                          @include('admin.widgets.paid-package', ['item' => $package ] )
                    @endif
                    @endpermission
                </div>
                <div class="panel-body">
                    <label>User packages with In Baku status</label>
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
                            @foreach($packages as $citem)
                                @include('admin.widgets.single-package', ['item' => $citem])
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
