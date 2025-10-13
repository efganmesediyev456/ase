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
			{{ isset($_view['name']) ? str_plural($_view['name']) : null }} 
			@if($tracks)
			<small class="display-block"> Showing {{ $tracks->firstItem() }} to {{ $tracks->lastItem() }}
                            of {{ number_format($tracks->total()) }} {{ $_view['sub_title'] or lcfirst(str_plural($_view['name'])) }}</small>
			@endif
			@if($set_count) <br><b>{{$set_count}} Tracks are set </b>@endif
                    </h6>
                </div>

                <div class="panel-body">
                        @permission('update-tracks')
                        {!! Form::open(['id' => 'in_customs_tracks_form', 'method' => 'post', 'route' => $_view['mod_name']."s.set" ]) !!}
                        <div class="col-md-4" >
                            <label><b 'color:blue'>SET</b> Status Track List:</label>
                            <div class="input-group">
                                <textarea id="set_tracks" rows=10 name="set_tracks" style='width: 300px;'
                                       class="form-control">{{$set_count?'':$set_tracks}}</textarea>
                            </div>
                        </div>
                        <div class="col-md-3" >
                            <div class="input-group">
				@include('crud::fields.select_from_array', ['field' => [
			            'name' => 'set_status',
		                    'type' => 'select_from_array',
		                    'optionsFromConfig' => 'ase.attributes.track.statusShort',
		                    'wrapperAttributes' => [
                    			'class' => 'col-lg-12',
		                    ],
	    			    'allowNull' => 'Select status',
				]])
                            </div>
                        </div>
                        <div class="col-md-3" >
                            <div class="input-group">
				@include('crud::fields.checkbox', ['field' => [
			            'name' => 'clear_parcel',
			            'label' => 'Remove tracks from parcel & bag',
		                    'type' => 'checkbox',
		                    'wrapperAttributes' => [
                    			'class' => 'form-group col-lg-12',
		                    ],
	    			    'default' => '0',
				]])
                            </div>
                        </div>
                        <div class="col-md-3" >
                            <div class="input-group">
				@include('crud::fields.select_from_array', ['field' => [
			            'name' => 'scan_no_check',
		                    'type' => 'select_from_array',
				    'options' =>  [1=>"Don't check customs when scaning",2=>"Check customs when scaning"],
		                    'wrapperAttributes' => [
                    			'class' => 'form-group col-lg-12',
		                    ],
	    			    'allowNull' => 'Select action when scaning',
				]])
                            </div>
                        </div>
                        <div class="col-md-3" >
                            <div class="input-group">
				@include('crud::fields.checkbox', ['field' => [
			            'name' => 'ignore_list',
			            'label' => 'Ignore',
		                    'type' => 'checkbox',
		                    'wrapperAttributes' => [
                    			'class' => 'form-group col-lg-12',
		                    ],
	    			    'default' => '0',
				]])
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" title="Update" id="in_customs_tracks_form_button" class="btn btn-primary btn-icon" style="margin-top: 28px;"><i
                                        class="icon-spinner"></i></button>
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
			    @if($tracks)
                            @foreach($tracks as $track)
                                @include('admin.widgets.track', ['item' => $track])
                            @endforeach
			    @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="heading-elements">
                        @if (isset($_view['checklist']) and is_array($_view['checklist']))
                            <div class="btn-group">
                                @foreach($_view['checklist'] as $button)
                                    <button data-route="{{ route($button['route']) }}"
                                            data-value="{{ $button['value'] }}"
                                            data-key="{{ $button['key'] }}" type="button"
                                            data-loading-text="<i class='icon-spinner4 spinner position-left'></i> Loading"
                                            class="btn btn-{{ isset($button['type']) ? $button['type'] : 'info' }} btn-loading do-list-action">
                                        <i class="icon-{{ isset($button['icon']) ? $button['icon'] : 'spinner4' }} position-left"></i>
                                        {{ $button['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
			@if($tracks)
                        <div class="pull-right">
                            <div>{!! $tracks->appends(Request::except('page'))->links() !!}</div>
                        </div>
			@endif
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
