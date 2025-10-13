<tr>
    @foreach($_list as $key => $head)
            @php
                $key = is_array($head) ? $key : $head;
                $type = isset($head['type']) ? $head['type'] : 'text';
                $entry = parseRelation($item, $key);
            @endphp
	    @if($key=='status')
	    	@if($item->status_id==1)
        	    <td style="color:green">
	    	@elseif($item->status_id==2)
        	    <td style="color:blue">
	    	@elseif($item->status_id==3)
        	    <td style="color:red">
		@else
        	    <td>
            	@endif
	    @else
                <td>
	    @endif
            @if(view()->exists('admin.crud.columns.' . $type))
                @include('admin.crud.columns.' . $type)
            @else
                @include('crud::components.columns.' . $type )
            @endif
        </td>
    @endforeach

    <td>


        <div class="btn-group">
            @if($extraActions)
                @foreach($extraActions as $extraAction)
                    @if($item->{$extraAction['key']})
                        <a @if(isset($extraAction['target'])) target="{{ $extraAction['target'] }}"
                           @endif title="{{ $extraAction['label'] }}" href="{{ isset($extraAction['route']) ? route($extraAction['route'], $item->id) : $item->{$extraAction['key']} }}"
                           type="button" class="btn btn-sm btn-{{ $extraAction['color'] }} btn-icon legitRipple"><i
                                    class="icon-{{ $extraAction['icon'] }}"></i></a>
                    @endif
                @endforeach
            @endif
        </div>
    </td>
</tr>
