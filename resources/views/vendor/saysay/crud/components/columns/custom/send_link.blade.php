<?php
$rand = uniqid();
if (isset($head['editable']['sourceFromConfig'])) $head['editable']['source'] = config($head['editable']['sourceFromConfig']);
$target = "update-" . explode(".", $head['editable']['route'])[0];
?>
@checking($target)
<a data-value="{{ $entry??$item->hd_filial }}" data-source='{{ $head['editable']['source'] }}'
   data-type="{{ $head['editable']['type'] or 'text' }}" href="#" data-pk="{{ $item->id }}"
   data-url="{{ route($head['editable']['route'], $item->id) }}"
   data-name="{{ $key }}"
   data-plugin="{{ $head['editable']['type'] == 'select2' ? 'c-' : null }}editable"
   id="edit_{{ $rand }}"
   data-title="Edit {{ clearKey($key) }}">{{ str_limit(strip_tags($item->{$key . '_with_label'}), 80) }}</a>
@else
    {{ str_limit(strip_tags($item->{$key . '_with_label'}), 80) }}
    @endchecking

    @if($item->debt_price > 0 && $item->paid_debt == 0)
        <br>
        <div style="display: flex">
            <form action="{{ route('track-pay-debt.post', $item->custom_id) }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-success btn-xs legitRipple text-white">
                   DEBT SMS <i class="icon-arrow-right14"></i>
                </button>
            </form>
            <button class="btn btn-secondary btn-xs" onclick="copyToClipboard(this)" data-href="https://aseshop.az/track/pay/debt/{{ $item->custom_id }}">
                Copy
            </button>
        </div>
    @endif
    @if($item->paid_broker == 0 && $item->status == 18)
        <br>
        <div style="display: flex">
            <form action="{{ route('track-pay-broker.post', $item->custom_id) }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-success btn-xs legitRipple text-white">
                    BROKER SMS <i class="icon-arrow-right14"></i>
                </button>
            </form>
            <button class="btn btn-secondary btn-xs" onclick="copyToClipboard(this)" data-href="https://aseshop.az/track/pay/broker/{{ $item->custom_id }}">
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
