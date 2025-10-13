@if($item)
  @if($item->user && $item->warehouse_id==11 && $item->user->ukr_express_id && (is_null($item->bot_comment) || trim($item->bot_comment) !=''))
    <div class="cr_b_ct">
      @if ($item->ukr_express_deleted || $item->ukr_express_utilized || $item->ukr_express_unassigned)
         @if ($item->ukr_express_deleted)
             <div class="ue_status_error"> DELETED </div>
         @elseif ($item->ukr_express_utilized)
             <div class="ue_status_error"> UTILIZED</div>
         @elseif ($item->ukr_express_unassigned)
             <div class="ue_status_error"> UNASSIGNED</div>
	 @endif
      @elseif ($item->ukr_express_status<=0)
         <div class="ue_status ue_status_no"> </div>
      @elseif ($item->ukr_express_status>=100)
         @if($item->bot_comment=='tracking-attention')
           <div class="ue_status ue_status_warning"> </div>
	 @else
           <div class="ue_status ue_status_error"> </div>
	 @endif
      @elseif ($item->ukr_express_status==1 || $item->ukr_express_status==8)
         @if (empty($item->getWeight()))
            <div class="ue_status ue_status_added"> </div>
	 @else
            <div class="ue_status ue_status_assigned"> </div>
	 @endif
      @elseif ($item->ukr_express_status==3 && empty($item->getWeight()))
         <div class="ue_status ue_status_added"> </div>
      @else
	 @if (empty($item->getWeight()) || str_contains($item->bot_comment,'error'))
             <div class="ue_status ue_status_error"> </div>
	 @else
             <div class="ue_status ue_status_assigned"> </div>
	 @endif
      @endif
    </div>
  @endif
@endif
