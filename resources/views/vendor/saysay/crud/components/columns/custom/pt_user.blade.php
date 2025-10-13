<!-- To create new custom column :  just add /view/admin/crud/columns/column_name.blade.php  -->
@if (! empty($item->user))
    <span>{{ $item->user->full_name }} ({{ $item->user->customer_id }})</span>
    @if($item->user->dealer)
    <br>
    <span><b>D : {{ $item->user->dealer->full_name }} ({{ $item->user->dealer->customer_id }})</b> </span>
    @endif
@elseif (! empty($item->fullname))
    <span>{{ $item->fullname }}</span>
@endif
