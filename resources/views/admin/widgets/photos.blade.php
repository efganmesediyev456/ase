<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Photos of {{ $item->custom_id}} -- {{ $item->tracking_code }}">
    <meta name="author" content="ASE">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UE {{ $item->custom_id}} -- {{ $item->tracking_code }}</title>

    <style>
    </style>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css">
</head>

<body>

<main>
         <div class="container">
    @if ($item->ukr_express_deleted || $item->ukr_express_utilized || $item->ukr_express_unassigned)
     <div class="row">
       <div class="col-md-12">
         @if ($item->ukr_express_deleted)
	     <h2 style="color:red"><b>Tracking number {{$item->tracking_code}} is DELETED </b></h2>
         @elseif ($item->ukr_express_utilized)
	     <h2 style="color:red"><b>Tracking number {{$item->tracking_code}} is UTILIZED </b></h2>
         @elseif ($item->ukr_express_unassigned)
	     <h2 style="color:red"><b>Tracking number {{$item->tracking_code}} is UNASSIGNED </b></h2>
         @endif
       </div>
     </div>
    @endif

    @if($track)
        <div class="row">
            <div class="col-md-6">
		@if($track->number != $item->tracking_code)
		  <b>Tracking number:</b> <i  style="color:red" >{{$track->number}} != {{$item->tracking_code}}</i><br>
		@else
		  <b>Tracking number:</b> <i>{{$track->number}}</i><br>
		@endif

		@if($action=="return_form")
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1 col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6>
		    RETURN FORM
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
                            <a href="{{ route( "packages.ue_info", $item->id) }}" class="btn btn-default legitRipple">{{ trans('saysay::crud.cancel') }}</a>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
		@elseif($action=="additional_price_form")
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1 col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6>
		    Set Additional Delivery Price
                    </h6>
                </div>

                {{ Form::open(['method' => 'POST', 'class' => 'form-horizontal', 'files' => true]) }}
                <div class="panel-body">
		  <input id="action" type="hidden" name="action" value="additional_price">
		  <div class="col-md-4" >
                    <div class="input-group">
		        <input id="aditional_delivery_final_price" type="text" name="aditional_delivery_final_price" value="{{ $item->additional_delivery_final_price }}"  class="form-control">
		    </div>
		  </div>
                </div>

                <div class="panel-footer">
                    <div class="heading-elements">
                        <div class="heading-btn pull-right">
                            <button type="submit" class="btn btn-info legitRipple">{{ trans('saysay::crud.save') }}</button>
                            <a href="{{ route( "packages.ue_info", $item->id) }}" class="btn btn-default legitRipple">{{ trans('saysay::crud.cancel') }}</a>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

		@else
		    @if(isset($track->receiving_info) && $track->receiving_info->timestamp > 0)
		      <b>Received at:</b> <i>{{date('Y-m-d H:i:s',$track->receiving_info->timestamp)}}</i><br>
		    @endif
		    @if($user)
		      <b>Customer:</b> <i @if($user->id != $item->user_id) style="color:red" @endif; >{{$user->full_name}} ({{$user->customer_id}})</i><br>
		    @endif
		    @if(isset($track->parcel_code) && $track->parcel_code)
		      <b>Packed parcel code:</b> <i @if($track->parcel_id != $item->ukr_express_parcel_id) style="color:red" @endif; >{{$track->parcel_code}}</i><br>
		    @endif
		    @if($new_weight)
		      <b>Weight:</b> <i>{{$new_weight}} kg</i><br>
		    @endif
		    @if($new_length || $new_width || $new_height)
		      <b>Length/Width/Height:</b> <i>{{$new_length}}/{{$new_width}}/{{$new_height}} cm ({{$item->full_size}})</i><br>
		    @endif
		    @if(isset($track->fees) && isset($track->fees->receiving) && ($track->fees->receiving))
		      <b>Receiving fee:</b> <i>{{number_format(0 + round($track->fees->receiving , 2), 2, ".","")}} $</i><br>
		    @endif
		    @if($track->forbidden->is)
		      <b style="color:red">Forbidden</b> {{$track->forbidden->description}}<br>
		    @endif
		    @if($track->permissions->change_sending)
		    @else
			<b style="color:blue">No permission to change sending</b><br>
		    @endif
		      @if($track->is_sending_allowed)
		        <b style="color:green">Sending allowed</b> <a href="{{ route('packages.ue_info', $item->id) }}?action=not_allow_sending" onclick="return confirm('Are you sure?')" >make not allowed</a><br>
		      @else
		        <b style="color:red">Sending not allowed <a href="{{ route('packages.ue_info', $item->id) }}?action=allow_sending" onclick="return confirm('Are you sure?')">make allowed</a></b><br>
		      @endif
		    @if(isset($track->return_info->returned) && $track->return_info->returned)
		      <b style="color:green">Returned Address:{{$track->return_info->address}} Note:{{$track->return_info->note}}</b> @if(isset($track->permissions->cancel_return) && $track->permissions->cancel_return)<a href="{{ route('packages.ue_info', $item->id) }}?action=return_cancel" onclick="return confirm('Are you sure?')" >Cancel return</a> @endif<br>
		    @elseif(isset($track->return_info->requested) && $track->return_info->requested)
		      @if (isset($track->permissions->cancel_return) && $track->permissions->cancel_return )
		        <b style="color:blue">Requested return Address:{{$track->return_info->address}} Note:{{$track->return_info->note}}</b>
                        <br> @if(isset($track->permissions->cancel_return) && $track->permissions->cancel_return)<a href="{{ route('packages.ue_info', $item->id) }}?action=return_cancel" onclick="return confirm('Are you sure?')" >Cancel return</a>@endif<br>
		      @else
			<b style="color:blue">No permission to cancel return</b><br>
		      @endif
		    @elseif (isset($track->permissions->return) && $track->permissions->return )
		      <b><a style="color:red" href="{{ route('packages.ue_info', $item->id) }}?action=return_form">Return</a></b><br>
		    @else
			<b style="color:blue">No permission to return</b><br>
		    @endif
		    <b>Additional price: <a style="color:blue" href="{{ route('packages.ue_info', $item->id) }}?action=additional_price_form">{{ $item->additional_delivery_final_price }}$</a></b><br>
		    @if(isset($track->photos_info) && $track->photos_info->has_any_photos > 0)
		      <b>Photos:</b> <i>{{$track->photos_info->has_any_photos}}</i><br>
		    @endif
		    @if(!empty($message))
		      <b>Error:</b> <b>{{ $message }}</b><br>
		    @endif
		    @if(!empty($item->bot_comment))
		      <b>Last Bot Message:</b> {{ $item->bot_comment }} <br>
		    @endif
		@endif
            </div>
        </div>
    @endif
    @if(($action!="return_form") && ($action!="additional_price_form") && $photos && is_array($photos) && count($photos)>0)
             <div class="row">
 	       @foreach ($photos as $photo)
               <div class="col-sm-6">
		   <a href="{{ $photo->url }}" target="_blank" title="{{ $photo->description }}">
                     <img src="{{ $photo->thumb_url }}">
		   </a>
               </div>
	       @endforeach
            </div>
        </div>
    @endif
         <div class="container">
</main>
</body>
</html>
