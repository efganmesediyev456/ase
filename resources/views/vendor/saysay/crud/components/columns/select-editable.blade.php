<?php
$rand = uniqid();
if (isset($head['editable']['sourceFromConfig'])) $head['editable']['source'] = config($head['editable']['sourceFromConfig']);
$target = "update-" . explode(".", $head['editable']['route'])[0];
?>
@checking($target)
<a data-value="{{ $entry??$item->hd_filial }}" data-source='{{ $head['editable']['source'] }}'
   data-type="{{ $head['editable']['type'] or 'text' }}" href="#" data-pk="{{ $item->id }}"
   data-url="{{ route($head['editable']['route'], $item->id) }}"
   data-name="{{ $key }}"
   data-plugin="{{ $head['editable']['type'] == 'select2' ? 'c-' : null }}editable"
   id="edit_{{ $rand }}"
   data-title="Edit {{ clearKey($key) }}">{{ str_limit(strip_tags($item->{$key . '_with_label'}), 80) }}</a>
@else
    {{ str_limit(strip_tags($item->{$key . '_with_label'}), 80) }}
@endchecking
