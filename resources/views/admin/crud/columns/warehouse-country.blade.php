<span>{{ $entry->company_name }}</span>
(<img src="{{ $entry->country->flag }}" class="{{ $head['class'] or null }}" id="{{ $head['id'] or null }}"
     width="{{ $head['width'] or '20' }}"
     height="{{ $head['height'] or '20' }}">)