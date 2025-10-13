<!-- To create new custom column :  just add /view/admin/crud/columns/column_name.blade.php  -->
@if (! empty($entry))
{{--    @if($entry->fullname != $item->fullname) ( {{ $item->fullname }} ) @endif--}}
    <span>{{ $entry->fullname }}  <a target="_blank" href="{{ route('customers.index').'?q='.$entry->fullname }}&partner_id={{$entry->partner_id}}" > <i class="icon-arrow-right14"></i></a></span>
@else
    <span>( {{ $item->fullname }} )</span>
@endif
