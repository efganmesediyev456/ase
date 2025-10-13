
@permission('update-paids')
<div align="right" style="padding-bottom:10px;">
                    @if($item->{$donePackageAction['key']})
                        <a @if(isset($donePackageAction['target'])) target="{{ $donePackageAction['target'] }}"
                           @endif title="{{ $donePackageAction['label'] }}" href="{{ isset($donePackageAction['route']) ? route($donePackageAction['route'], 'done=1&id='.$item->id) : $item->{$donePackageAction['key']} }}{{ (isset($item->track) && $item->track) ? '&track=1':''}}"
                           type="button" class="btn btn-sm btn-{{ $donePackageAction['color'] }} btn-icon legitRipple"><i
                                    class="icon-{{ $donePackageAction['icon'] }}"></i> Done</a>
                    @endif
</div>
@endpermission

<div class="table-responsive overflow-visible">
    <table class="table table-hover responsive table-striped">

    <input checked type="hidden" name="items[]" value="{{ $item->id }}"/>
    @foreach($listMain as $key => $head)
            @php
                $key = is_array($head) ? $key : $head;
                $type = isset($head['type']) ? $head['type'] : 'text';
                $label = isset($head['label']) ? $head['label'] : '';
                $entry = parseRelation($item, $key);
            @endphp
            <tr class="scanned_package" id=="package_{{ $item->id }}">
            <td> {{$label}}</td> 

            <td>
            @if($key=='status_with_label' && $entry=='Done')
            <font color="green"><b>
            @endif
            @if($key=='user')
            <b>
            @endif

            @if(view()->exists('admin.crud.columns.' . $type))
                @include('admin.crud.columns.' . $type)
            @else
                @include('crud::components.columns.' . $type )
            @endif

            @if($key=='status' && $entry=='Done')
            </b></font>
	    @endif
            @if($key=='user')
            </b>
            @endif
            </td>

	    </tr> 
    @endforeach

      <tr>
      <td colspan=2 align=right>
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

</table>
</div>

