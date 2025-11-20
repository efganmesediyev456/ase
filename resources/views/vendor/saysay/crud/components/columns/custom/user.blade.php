<!-- To create new custom column :  just add /view/admin/crud/columns/column_name.blade.php  -->
@if (! empty($entry))
    <span>{{ $entry->full_name }} ({{ $entry->customer_id }})</span>
    @if($entry->dealer and optional(auth()->user()->role)->id!=10 and optional(auth()->user()->role)->id!=26)
    <br>
    <span><b>D : {{ $entry->dealer->full_name }} ({{ $entry->dealer->customer_id }})</b> </span>
    @endif
@endif
