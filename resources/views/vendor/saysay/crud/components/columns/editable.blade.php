<?php $rand = uniqid();
if (isset($head['editable']['sourceFromConfig'])) $head['editable']['data']['source'] = config($head['editable']['sourceFromConfig']);
$target = (! isset($head['editable']['skip'])) ? "update-" . explode(".", $head['editable']['route'])[0] : 'update-packages';
?>
@if(1)
<a @if(isset($head['editable']['data']))
   @foreach($head['editable']['data'] as $dataKey => $dataValue)
   data-{{ $dataKey }}="{{ $dataValue }}"
   @endforeach
   @endif
   data-step="any" data-type="{{ isset($head['editable']['type']) ? $head['editable']['type'] : 'text' }}"
   data-pk="{{ $item->id }}" data-url="{{ route($head['editable']['route'], $item->id) }}"
   data-name="{{ isset($head['editable']['key']) ? $head['editable']['key'] : $key }}"
   data-value="{{ isset($head['editable']['key']) ? $item->{$head['editable']['key']} : $entry }}"
   data-plugin="editable" id="edit_{{ $rand }}"
   data-title="{{ isset($head['editable']['title']) ? $head['editable']['title'] : 'Edit ' . clearKey($key) }}">{{ str_limit(strip_tags($entry), 80) }}</a>
@else
   {{ str_limit(strip_tags($entry), 80) }}
@endif