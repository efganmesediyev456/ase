@if(
 (!Auth::user()->can('change-post_terminal') && $item->paid_by == 'POST_TERMINAL')
 ||
 (!Auth::user()->can('change-cash') && $item->paid_by == 'CASH')
)
{{ $item->paid_with_label }}
    @if($item->transaction)
        <br/> @ {{ $item->transaction->created_at }}
    @endif
@elseif($item->portmanat)
    <a target="_blank" href="{{ route('transactions.index') }}?id={{ $item->portmanat->id }}">by PortManat</a>
    @if($item->transaction)
        <br/> @ {{ $item->transaction->created_at }}
    @endif
    @if($item->status == 2 || $item->status == 8)
        <br>
        <a href="#!" class="btn btn-info btn-xs legitRipple text-white"
           data-ajax-request="{{ route('packages.request', $item->id) }}">Request</a>
    @endif
@elseif($item->kapital)
    <a target="_blank" href="{{ route('transactions.index') }}?id={{ $item->kapital->id }}">by Kapital</a>
    @if($item->transaction)
        <br/> @ {{ $item->transaction->created_at }}
    @endif
    @if($item->status == 2 || $item->status == 8)
        <br>
        <a href="#!" class="btn btn-info btn-xs legitRipple text-white"
           data-ajax-request="{{ route('packages.request', $item->id) }}">Request</a>
    @endif
@else
    @include('crud::components.columns.select-editable' )
    @if($item->transaction)
        <br/> @ {{ $item->transaction->created_at }}
    @endif
@endif
