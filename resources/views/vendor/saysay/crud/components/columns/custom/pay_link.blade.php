<!-- To create new custom column :  just add /view/admin/crud/columns/column_name.blade.php  -->
@if (! empty($entry))
    <span> @if($entry!=null)  {{ $entry }}  <a target="_blank" href="https://aseshop.az/track/pay/{{ $entry }}" > (Pay <i class="icon-arrow-right14"></i>)</a> @endif </span>
@else
    <span>( {{ $entry }} )</span>
@endif
