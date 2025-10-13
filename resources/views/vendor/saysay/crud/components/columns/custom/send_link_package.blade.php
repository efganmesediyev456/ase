@if($item->paid_broker == 0 && $item->status == 4)
    <br>
    <div style="display: flex; align-items:center; gap:10px;">
        <form action="{{ route('package-pay-broker.post', $item->custom_id) }}" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-success btn-xs legitRipple text-white">
                BROKER SMS ({{ (empty($item->user->voen)) ? 15 : 50 }} AZN) <i class="icon-arrow-right14"></i>
            </button>
        </form>
        <button class="btn btn-secondary btn-xs" onclick="copyToClipboard(this)" data-href="https://aseshop.az/package/pay/broker/{{ $item->custom_id }}">
            Copy
        </button>
    </div>
@endif

<script>
    window.copyToClipboard = function(button) {
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
