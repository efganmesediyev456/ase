@extends(config('saysay.crud.layout'))

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1 col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6>
                       CAMPAIGN
                        <small class="display-block">Sub title here</small>
                    </h6>
                </div>

                {{ Form::open(['method' => 'POST', 'class' => 'form-horizontal', 'files' => true]) }}
                <div class="panel-body">
                    @if($fields)
                        @foreach ($fields as $field)
                            @include('crud::fields.' . $field['type'], ['field' => $field])
                        @endforeach
                    @else
                        @include('crud::components.alert', ['text' => trans('saysay::crud.no_fields')])
                    @endif
                </div>

                <div class="panel-footer">
                    <div class="heading-elements">
                        <div class="heading-btn pull-right">
                            <button type="submit" class="btn btn-info legitRipple">{{ trans('saysay::crud.save') }}</button>
                            <a href="#" class="btn btn-default legitRipple">{{ trans('saysay::crud.cancel') }}</a>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection