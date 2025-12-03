<br>

@php
    $customer = $item->customer;
    if($customer)
    $count = \App\Models\CD::where('customer_id', optional($customer)->id)->count();
    else
        $count = 0;
@endphp

@if($item->first_track && $item->first_track->partner &&  $item->first_track->partner->id==3 and $count>2)
    <div style="display: flex"> {{$count}}
        <button class="btn btn-secondary btn-xs" onclick="copyToClipboard(this)"
                data-href="https://aseshop.az/courier_deliveries/payment/{{ $item->id }}">
            Copy
        </button>
    </div>
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
