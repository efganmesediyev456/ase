    <br>
    <div style="display: flex">
        <button class="btn btn-secondary btn-xs" onclick="copyToClipboard(this)"
                data-href="https://aseshop.az/courier_deliveries/payment/{{ $item->id }}">
            Copy
        </button>
    </div>

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
