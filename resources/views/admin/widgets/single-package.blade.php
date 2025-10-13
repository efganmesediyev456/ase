<tr class="scanned_package" id="package_{{ $item->id }}">

    <input checked type="hidden" name="items[]" value="{{ $item->id }}"/>
    @foreach($_list as $key => $head)
        <td>
            @php
                $key = is_array($head) ? $key : $head;
                $type = isset($head['type']) ? $head['type'] : 'text';
                $entry = parseRelation($item, $key);
            @endphp

            @if(view()->exists('admin.crud.columns.' . $type))
                @include('admin.crud.columns.' . $type)
            @else
                @include('crud::components.columns.' . $type )
            @endif
        </td>
    @endforeach

    <td>
      @if(isset($userPackageAction) && (!isset($userPackageAction['role']) || Auth::user()->can($extraAction['role'])))
                    @if($item->{$userPackageAction['key']})
                        <a @if(isset($userPackageAction['target'])) target="{{ $userPackageAction['target'] }}"
                           @endif title="{{ $userPackageAction['label'] }}" href="{{ isset($userPackageAction['route']) ? route($userPackageAction['route'], 'done=0&id='.$item->id) : $item->{$userPackageAction['key']} }}{{ (isset($item->track) && $item->track) ? '&track=1':''}}"
			    @if(isset($userPackageAction['confirm'])) onclick="return confirm('Are you sure to {{$userPackageAction['confirm']}}?')" @endif
                           type="button" class="btn btn-sm btn-{{ $userPackageAction['color'] }} btn-icon legitRipple"><i
                                    class="icon-{{ $userPackageAction['icon'] }}"></i></a>
                    @endif
       @endif
    </td>

    <td align="right">

        <div class="btn-group">

            @if($extraActions)
                @foreach($extraActions as $extraAction)
      @if(!isset($extraAction['role']) || Auth::user()->can($extraAction['role']))
                    @if($item->{$extraAction['key']})
                        <a @if(isset($extraAction['target'])) target="{{ $extraAction['target'] }}"
                           @endif title="{{ $extraAction['label'] }}" href="{{ isset($extraAction['route']) ? route($extraAction['route'], $item->id) : $item->{$extraAction['key']} }}"
			    @if(isset($extraAction['confirm'])) onclick="return confirm('Are you sure to {{$extraAction['confirm']}}?')" @endif
                           type="button" class="btn btn-sm btn-{{ $extraAction['color'] }} btn-icon legitRipple"><i
                                    class="icon-{{ $extraAction['icon'] }}"></i></a>
                    @endif
       @endif
                @endforeach
            @endif
        </div>
    </td>
</tr>
