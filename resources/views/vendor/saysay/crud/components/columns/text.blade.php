<?php $_entry = (! is_null(request()->get('q'))) ? (str_replace(trim(request()->get('q')), ("<strong>" . trim(request()->get('q')) . "</strong>"), strip_tags($entry))) : strip_tags($entry) ;?>
{!! str_limit($_entry, 80) !!}
