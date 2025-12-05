@if($item->first_track && $item->first_track->partner &&  $item->first_track->partner->id==3 )
    @php
        $customer = $item->customer;
        if($customer)
        $count = \App\Models\CD::where('customer_id', optional($customer)->id)->count();
        else
        $count = 0;
    @endphp

    @if($count>2)
        @forelse($item->courierTrackOzonDeliveryTransactions as $transaction)
            <a target="_blank" href="{{ route('transactions.index') }}?id={{ $transaction->id }}">by Kapital</a>
            <br/> @ {{ $transaction->created_at }}
        @empty
            <div style="display: flex">
                <button class="btn btn-secondary btn-xs" onclick="copyToClipboard(this)"
                        data-href="https://aseshop.az/courier_deliveries/payment/{{ $item->id }}">
                    Copy
                </button>
            </div>
        @endforelse
    @endif

    <script>
        window.copyToClipboard = function (button) {
            const link = button.getAttribute("data-href");
            const tempInput = document.createElement("input");
            tempInput.value = link;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);
            alert("Link copied: " + link);
        }
    </script>

@endif


