@if($item->has_battery )
    <img title="" src="{{ asset('admin/images/battery-11-32.png') }}" class="{{ $head['class'] or null }}"
         id="{{ $head['id'] or null }}"
         width="{{ $head['width'] or '32' }}"
         height="{{ $head['height'] or '32' }}">
@endif
