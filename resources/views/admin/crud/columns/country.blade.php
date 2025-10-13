@if($entry)
    <img title="{{ $entry->name }}" src="{{ $entry->flag }}" class="{{ $head['class'] or null }}"
         id="{{ $head['id'] or null }}"
         width="{{ $head['width'] or '20' }}"
         height="{{ $head['height'] or '20' }}">
@elseif(isset($item->country))
    <img title="{{ $item->country->name }}" src="{{ $item->country->flag }}" class="{{ $head['class'] or null }}"
         id="{{ $head['id'] or null }}"
         width="{{ $head['width'] or '20' }}"
         height="{{ $head['height'] or '20' }}">
@endif