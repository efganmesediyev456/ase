@extends(config('saysay.crud.layout'))

@section('content')
    <div class="row">
        <div class="col-xl-{{ $_view['formColumns'] }} col-xl-offset-{{ (12 - $_view['formColumns'])/2 }} col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6>
                        {{ isset($_view['name']) ? str_plural($_view['name']) : null }}
                        <small class="display-block">{{ $_view['sub_title'] or null }}</small>
                    </h6>
                    <div class="heading-elements">
                        @if(isset($currentLang))
                            <div class="btn-group heading-btn">
                                <button class="btn btn-success">{{ config('translatable.locales_name.' . $currentLang) }}</button>
                                <button class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                    <span class="caret"></span>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-right">
                                    @foreach(config('translatable.locales_name') as $_lang => $langName)
                                        @if($_lang != $currentLang)
                                            <li>
                                                <a href="{{ $form['selfLink'] . "?lang=" . $_lang }}">{{ $langName }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif
                 {{ Form::open(['url' => $form['route'], 'method' => $form['method'], 'id' => 'form-' . strtolower($_view['name']), 'class' => 'form-' . $_view['formStyle'], 'files' => true]) }}
                 <div class="panel-body">
                     <div id="updated" class="alert alert-success" style="display: none">Updated! </div>
                     @if(isset($currentLang))
                         {{ Form::hidden('_lang', $currentLang) }}
                     @endif

                     @if($fields)
			  @foreach ($fields as $field)
                             @include('crud::fields.' . $field['type'], ['field' => $field])
                          @endforeach
                     @else
                         @include('crud::components.alert', ['text' => trans('saysay::crud.no_fields')])
                     @endif

<script>
var goods=[];
var keys=[];
var w_id=0;
                     @if(isset($item) && isset($item->goods) && isset($item->warehouse_id))
			  w_id={{$item->warehouse_id}};
			  @foreach ($item->goods as $good)
			        @if($good->ru_type)
				    goods.push([{{$good->ru_type_id}},{{$good->shipping_amount}},{{$good->weight}},{{$good->number_items}},'{{$good->ru_type->hs_code}}','{{$good->ru_type->name_ru}}']);
				@else
				    @if($good->customs_type_id)
				        goods.push([1,{{$good->customs_type_parent_id}},{{$good->customs_type_id}},{{$good->shipping_amount}},{{$good->weight}},{{$good->number_items}}]);
			  	    @else
				        goods.push([0,{{$good->type_id}},{{$good->shipping_amount}},{{$good->weight}},{{$good->number_items}}]);
				    @endif
				@endif
                          @endforeach
			  @if($item->warehouse_id==2 && count($item->goods)<=0 && $item->type_id && $item->type_id>0)
			        goods.push([0,{{$item->type_id}},{{$item->shipping_amount}},{{$item->weight}},{{$item->number_items}}]);
			  @endif
                     @endif
                     @if(isset($old_keys) && $old_keys)
			  @foreach ($old_keys as $key)
			      keys.push(['{{$key->name}}','{{$key->type}}']);
                          @endforeach
                     @elseif(isset($item) && isset($item->keys))
			  @foreach ($item->keys as $key)
			      keys.push(['{{$key->name}}','{{$key->type}}']);
                          @endforeach
                     @endif
</script>
                         <div class="row">
                             <div class="col-lg-12">

                                 @if(isset($includes))
                                     @foreach($includes as $include)
                                         @include($include['view'], $include['data'])
                                     @endforeach
                                 @endif
                             </div>
                         </div>

                 </div>

                 <div class="panel-footer">
                     @include('crud::inc.form_save_buttons')
                 </div>
                 {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection
