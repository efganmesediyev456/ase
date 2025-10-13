<div class="modal-content">
    <div id="modal_theme" class="modal-header bg-success">
        <h2 class="modal-title">Package CWB #{{ $item->custom_id }}</h2>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <tbody style="font-size: 22px;">
                <tr>
                    <td><b>ID:</b></td>
                    <td>{{ $item->id }}</td>
                </tr>
                <tr>
                    <td><b>CWB:</b></td>
                    <td>{{ $item->custom_id }}</td>
                </tr>
                <tr>
                    <td><b>Tracking #:</b></td>
                    <td>{{ $item->tracking_code }}</td>
                </tr>
                <tr>
                    <td><b>Member:</b></td>
                    <td>{{ $item->user ? $item->user->full_name : '-' }}</td>
                </tr>
                <tr>
                    <td><b>Member #:</b></td>
                    <td>{{ $item->user ? $item->user->customer_id : '-' }}</td>
                </tr>
              {{--  <tr>
                    <td><b>Weight:</b></td>
                    <td>{{ $item->weight_with_type or '-' }}</td>
                </tr>
                <tr>
                    <td><b>Qty:</b></td>
                    <td>{{ $item->number_items_goods ?: '-' }}</td>
                </tr>
                <tr>
                    <td><b>Dims #:</b></td>
                    <td>{{ $item->width ? $item->full_size : '-' }}</td>
                </tr>

                <tr>
                    <td><b>Invoice Value:</b></td>
                    <td>{{ $item->shipping_price ? $item->shipping_price : '-' }}</td>
                </tr>
                <tr>
                    <td><b>Delivery Value:</b></td>
                    <td>{{ $item->delivery_price ? $item->delivery_usd_price . ' USD' : '-' }}</td>
                </tr>
                <tr>
                    <td><b>Total Declared Value:</b></td>
                    <td>{{ $item->total_price ? $item->total_price . ' USD' : '-' }}</td>
                </tr>
                <tr>
                    <td><b>Ship date:</b></td>
                    <td>{{ $item->created_at or '-' }}</td>
                </tr>--}}
                </tbody>
            </table>
        </div>
    </div>
    {{--<div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
        @if($item->show_label)
            <a target="_blank" href="{{ route('w-packages.label', $item->id) }}" class="btn bg-info">
                Label
            </a>
        @endif
        @if($item->invoice)
            <a target="_blank" href="{{ asset($item->invoice) }}" class="btn bg-success">
                Invoice
            </a>
        @endif
        @if(in_array($item->status, [0, 6]))
            <a target="_blank" href="{{ route('w-packages.edit', $item->id) }}" class="btn bg-warning">
                Edit
            </a>
        @endif
    </div>--}}
</div>
