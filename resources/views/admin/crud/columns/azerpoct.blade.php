@if($item->azerpoct_send && $item->azerpoct) 
      {{trans('front.azerpoct_status_'.$item->azerpoct->status,['zip_code'=>$item->zip_code])}}
      <br>Order ID: {{$item->azerpoct->order_id}}
@endif
