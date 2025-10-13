<?php
$category = implode((isset($head['divider']) ? $head['divider'] : ','), $entry->pluck('name')->all());
?>
{{ $category ? str_limit(strip_tags($category), 100) : trans('saysay::crud.not_choose') }}