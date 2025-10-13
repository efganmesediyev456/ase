<!-- To create new custom column :  just add /view/admin/crud/columns/column_name.blade.php  -->
@if (! empty($entry))
    <span>{{ $entry->full_name }} ({{ $entry->customer_id }}) <a target="_blank" href="{{ route('users.index').'?q='.$entry->customer_id }}" > <i class="icon-arrow-right14"></i></a></span>
    @if($entry->dealer)
    <br>
    <span><b>D :  {{ $entry->dealer->full_name }} ({{ $entry->dealer->customer_id }}) <a target="_blank" href="{{ route('users.index').'?q='.$entry->dealer->customer_id }}" > <i class="icon-arrow-right14"></i></a></b> </span>
    @endif
@endif
