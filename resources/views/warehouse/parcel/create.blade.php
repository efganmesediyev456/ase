@extends(config('saysay.crud.layout'))

@section('content')
    <div class="row">
        {!! Form::open(['route' => $crud['route'] . '.store', 'id' => 'package_ids']) !!}
        <div class="col-lg-12 col-lg-offset-0 col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading" style="margin-bottom: 30px;">
                    <div class="row">
                        <div class="col-lg-6">
                            <div >
                                <label>Parcel name</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="icon-barcode2"></i></div>
                                    <input id="name" type="text" name="name" value="{{ $defaultValue }}"
                                           class="form-control">
                                </div>

                            </div>
                            <div>
                                <label>Bag name</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="icon-barcode2"></i></div>
                                    <input id="bname" type="text" name="bname" placeholder="(Optional) Leave empty for autonumbering" value="{{ $defaultValue }}"
                                           class="form-control">
                                </div>

                            </div>
                        </div>
                    </div>


                    <div class="heading-elements">

                        <button type="submit" id="export-packages" class="btn btn-info btn-sm legitRipple">
                            <i class="icon-check position-left"></i>
                            Done
                            <span class="legitRipple-ripple"></span></button>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="table-responsive overflow-visible">
                        <table class="table table-hover responsive table-striped">
                            <thead>
                            <tr>
                                <th>#</th>
                                @foreach($_list as $key => $head)
                                    <th>{{ is_array($head) ? (array_key_exists('label', $head) ? $head['label'] : ucfirst(str_replace("_", " ", $key))) : ucfirst(str_replace("_", " ", $head)) }}</th>
                                @endforeach
                                <th></th>
                            </tr>
                            </thead>
                            <tbody id="scanned">
                            <tr id="empty_package">
                                <td colspan="{{ count($_list) + 2 }}">
                                    <div class="alert alert-danger">Please scan or add a package(s)</div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                {!! Form::close() !!}
                <div class="panel-heading" style="height: 150px; border-top: 1px solid #ccc;">
                    <div class="heading-delements">

                        {!! Form::open(['id' => 'manual_add_package', 'class' => 'no_loading']) !!}
                        <div class="col-md-6">
                            <label>Tracking Number or CWB</label>
                            <div class="input-group">
                                <div class="input-group-addon"><i class="icon-truck"></i></div>
                                <input id="manual_add" type="text" name="manual_add" value=""
                                       class="form-control">
                            </div>
                            <p class="help-block">If you want add a package manually</p>

                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-icon" style="margin-top: 28px;"><i class="icon-plus2"></i></button>
                        </div>
                        {!! Form::close() !!}
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
