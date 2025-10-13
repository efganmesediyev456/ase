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
                        Airwaybill @if($airPackages && count($airPackages)>0) {{count($airPackages)}} @endif packages {{$airwaybill}} @if($depesH_NUMBER) with depesh {{$depesH_NUMBER}} @endif
                    </h6>
                </div>

                <div class="panel-body">
                        @permission('customs-check')
    <div class="row">
                        {!! Form::open(['id' => 'air_packages', 'method' => 'get' ]) !!}
                        <div class="col-md-3" >
                            <label>AirwayBill</label>
                            <div class="input-group">
                                <input id="airwaybill" type="text" name="airwaybill" value="{{$airwaybill}}"
 					class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3" >
                            <label>Depesh Number</label>
                            <div class="input-group">
                                <input id="depesH_NUMBER" type="text" name="depesH_NUMBER" value="{{$depesH_NUMBER}}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" title="Look Up" id="air_packages_button" class="btn btn-primary btn-icon" style="margin-top: 28px;"><i
                                        class="icon-search4"></i></button>
                        </div>
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
                            </tr>
                            </thead>
                            <tbody id="scanned" >
			    @if(isset($_view['sum']))
                                @foreach($_view['sum'] as $sumKey)
                                    <?php $_total_[$sumKey['key']] = 0;?>
                                @endforeach
                            @endif
                            @foreach($airPackages as $airPackage)
				<?php $item=$airPackage ?>
				@if(isset($_view['sum']))
                                    @foreach($_view['sum'] as $sumKey)
                                        <?php $_total_[$sumKey['key']] += $item->{$sumKey['key']}?>
                                    @endforeach
                                @endif
                                @include('admin.widgets.logic_sync', ['item' => $item])
                            @endforeach
                            <tr>
			        @if(isset($_view['sum']))
				    @foreach($_view['sum'] as $sumKey)
				        @if($_total_[$sumKey['key']])

                                            @if($sumKey['skip'] )
                                                <td colspan="{{ $sumKey['skip'] }}" style="text-align: center"> Total page</td>
                                            @endif
                                            <td>
                                                <b>{{ $_total_[$sumKey['key']] }} {{ isset($sumKey['add']) ? $sumKey['add'] : null }}</b>
                                            </td>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>
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
