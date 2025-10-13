@if($entry)
  @if(!$entry->check_customs)
    <div class="cr_b_ct">
       <div class="cr_b_ok2"> </div>
       <div class="cr_b_ok2"> </div>
       <div class="cr_b_ok2"> </div>
    </div>
  @else 
    @if($entry->code==200 || $entry->code==400)
    <div class="cr_b_ct">
       @if($entry->is_commercial)
          <div class="cr_b_ok3"> </div>
       @else
          <div class="cr_b_ok"> </div>
       @endif
       @if(!empty($entry->ecoM_REGNUMBER))
          @if($entry->is_commercial)
             <div class="cr_b_ok3"> </div>
          @else
             <div class="cr_b_ok"> </div>
          @endif
       @else
	 @if($entry->status==1)
             <div class="cr_b_ok4"> </div>
	 @else
             <div class="cr_b_err"> </div>
	 @endif
       @endif
       @if(!empty($entry->depesH_NUMBER))
          @if($entry->is_commercial)
             <div class="cr_b_ok3"> </div>
          @else
             <div class="cr_b_ok"> </div>
          @endif
       @else
         <div class="cr_b_err"> </div>
       @endif
    </div>
    @else
       {{$entry->errorMessage}} {{$entry->validationError}}
    @endif
  @endif
@else
@endif
