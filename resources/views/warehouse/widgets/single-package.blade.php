<tr class="scanned_package" id="package_{{ $item->id }}">

    <td scope="row">{{ $item->id }}</td>

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


                <button data-item="{{ $item->id }}"
                        class="btn btn-sm btn-group btn-danger btn-icon legitRipple btn-ladda btn-ladda-spinner delete-package"
                        data-spinner-color="#fff" data-style="slide-left" type="button">
                    <i class="icon-trash"></i>
                </button>
        </div>
    </td>
</tr>