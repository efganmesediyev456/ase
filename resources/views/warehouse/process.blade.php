@extends(config('saysay.crud.layout'))

@section('content')
   <?php $bag_id=0;?>
    <div class="row">
        {!! Form::open(['route' => [$crud['route'] . '.update', $bag_id], 'method' => 'put', 'id' => 'package_ids']) !!}
        <div class="col-lg-12 col-lg-offset-0 col-md-12 col-xs-12">
            <div class="panel panel-flat">
                {!! Form::close() !!}
                <div class="panel-heading" style="height: 150px; border-top: 1px solid #ccc;">
                    <div class="heading-delements">

                        {!! Form::open(['id' => 'manual_add_package', 'class' => 'no_loading']) !!}
                        <div class="col-md-4">
                            <label>Tracking Number or CWB</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="icon-truck"></i></div>
                                <input id="manual_add" type="text" name="manual_add" value=""
                                       class="form-control">
                            </div>
                            <p class="help-block">If you want add a package manually</p>

                        </div>
                        @if ( auth()->guard('worker')->user()->warehouse->draft_label)
                        <div class="col-md-2">
                          <input type="checkbox" class="styled" disabled="disabled" name="check_draft_label" id="check_draft_label" {{ auth()->guard('worker')->user()->warehouse->draft_label ? 'checked':'' }}>
                          <i class="icon-bin"></i>
                            <label> Draft Label</label>
                        </div>
                            @endif
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-icon" style="margin-top: 28px;"><i
                                        class="icon-checkmark"></i></button>
                        </div>
                        <div class="col-md-4">
                            @if ( ! auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice)
                                <span style="position: relative; top: 35px;margin-right: 20px; border: 1px solid #ff1c90; padding: 10px; border-radius: 5px">Waiting ready packages : <a
                                            href="{{ route('w-packages.index') }}?status=0&dec=1&limit=25"
                                            id="waiting_packages" class="waiting_packages"
                                            style="background: red;color: #fff;padding: 5px;border-radius: 5px;">{{ $ready_packages }}</a></span>
                            @endif
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <div class="heading-elements">
                        @if (! auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice)
                            <span style="position: relative; top: 2px;margin-right: 20px; border: 1px solid #ff1c90; padding: 10px; border-radius: 5px">Waiting ready packages : <a
                                        href="{{ route('w-packages.index') }}?status=0&dec=1&limit=25"
                                        class="waiting_packages"
                                        style="background: red;color: #fff;padding: 5px;border-radius: 5px;">{{ $packages }}</a></span>
                        @else
                            <span style="position: relative; top: 2px;margin-right: 20px; border: 1px solid #ff1c90; padding: 10px; border-radius: 5px">Use <b>SHIFT+ENTER</b> to add new package</span>
                            <button type="button" data-toggle="modal" data-target="#new_package"
                                    class="btn btn-primary btn-icon" style="margin-right: 28px;">ADD
                            </button>
                        @endif
                    </div>
                </div>

                <div class="panel-body">
                    <div class="table-responsive overflow-visible">
                        <table class="table table-hover responsive table-striped">
                            <thead>
                            <tr>
                                @foreach($listProcessed as $key => $head)
                                    <th>{{ is_array($head) ? (array_key_exists('label', $head) ? $head['label'] : ucfirst(str_replace("_", " ", $key))) : ucfirst(str_replace("_", " ", $head)) }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody id="scanned" data-id="{{ $bag_id }}">
                            @foreach($packages as $citem)
                                @include('warehouse.widgets.processed-package', ['item' => $citem, 'extraActions' => $extraActionsForPackage])
                            @endforeach
                            <tr style="display: none" id="empty_package">
                                <td colspan="{{ count($_list) + 2 }}">
                                    <div class="alert alert-danger">Please scan or add a package(s)</div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="modal" role="dialog" id="new_package">
        <div class="modal-dialog" role="document" style="width: 1100px">
            <div class="modal-content">
                <span id="delivery_index" data-value="{{  auth()->guard('worker')->user()->warehouse->country->delivery_index }}"></span>
                {{ Form::open(['url' => route('w-parcel.add_package', $bag_id), 'class' => 'no_loading','method' => 'post', 'id' => 'form_add_package', 'files' => true]) }}
                <div class="modal-header">
                    <h5 class="modal-title">Add new package</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @if($fields)
			    <?php
	if(auth()->guard('worker')->user()->warehouse->country->code=='uk') {
            $fields[4]['validation'] = 'required|string|min:9|unique:packages,tracking_code';
	    $fields[4]['hint']= 'Special Tracking number (required)';
	    unset($fields[4]['attributes']['data-validation-optional']);
	}
			    ?>
                            @foreach ($fields as $field)
                                @if(auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice || (! auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice && auth()->guard('worker')->user()->warehouse->only_weight_input && isset($field['short'])))
                                    @include('crud::fields.' . $field['type'], ['field' => $field])
                                @endif
                            @endforeach
                        @else
                            @include('crud::components.alert', ['text' => trans('saysay::crud.no_fields')])
                        @endif
                    </div>
                    <div id="alert_price" style="display: none" class="alert alert-danger">Price is over 900. Be sure
                        that the price is correct.
                    </div>
                    <div id="alert_weight" style="display: none" class="alert alert-danger">Weight is over 9kq. Be sure
                        that the weight is correct.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Package</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
                {{ Form::close() }}
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
