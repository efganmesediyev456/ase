<table>
    <thead>
    <tr>
        <th><b>Parcel #</b></th>
        <th><b>Bag #</b></th>
        <th><b>Track #</b></th>
        <th><b>Courier</b></th>
        <th><b>Status</b></th>
        <th><b>Fullname</b></th>
        <th><b>FIN</b></th>
        <th><b>Phone</b></th>
        <th><b>Items quantity</b></th>
        <th><b>Weight (kq)</b></th>
        <th><b>Delivery Type</b></th>
        <th><b>HD Filial</b></th>
        <th><b>Pudo Filial</b></th>
        <th><b>Paid</b></th>
        <th><b>Comments</b></th>
        <th><b>Comments 2</b></th>
        <th><b>Paid date</b></th>
        <th><b>Delivery Price GFS</b></th>
        <th><b>Delivery Price Ozon</b></th>
        <th><b>Invoice Price</b></th>
        <th><b>Invoice Currency</b></th>
        <th><b>Delivery Price</b></th>
        <th><b>Delivery Currency</b></th>
        <th><b>City</b></th>
        <th><b>Address</b></th>
        <th><b>Detailed type</b></th>
        <th><b>Created at</b></th>
    </tr>
    </thead>
    <tbody>
    @foreach($tracks as $track)
        <tr>
            <td>{{ $track->container ? $track->container->name : ''}}</td>
            <td>{{ $track->airbox ? $track->airbox->name : ''}}</td>
            <td>{{ $track->tracking_code }}</td>
            <td>{{ $track->courier_delivery && $track->courier_delivery->courier ? $track->courier_delivery->courier->name : '' }}</td>
            <td>{{ $track->status_with_label }}</td>
            <td>{{ $track->fullname }}</td>
            <td>{{ $track->fin }}</td>
            <td>{{ $track->phone }}</td>
            <td>{{ $track->items_quantity }}</td>
            <td>{{ $track->weight }}</td>
            <td>{{ $track->delivery_type }}</td>
            <td>{{ $track->filial_hd_name }}</td>
            <td>{{ $track->filial_pudo_name}}</td>
            <td>
                @if($track->paid)
                    YES
                @else
                    NO
                @endif
            </td>
            <td>{{ $track->worker_comments }}</td>
            <td>{{ $track->worker2_comments }}</td>
            <td>{{ $track->transaction && $track->transaction ? $track->transaction->created_at : '' }}</td>
            <td>{{ $track->delivery_price_gfs }}</td>
            <td>{{ $track->delivery_price_ozon }}</td>
            <td>{{ $track->shipping_amount }}</td>
            <td>{{ $track->currency }}</td>
            <td>{{ $track->delivery_price }}</td>
            <td>{{ $track->delivery_price_cur }}</td>
            <td>{{ $track->city_name }}</td>
            <td>{{ $track->address }}</td>
            <td>{{ $track->detailed_type }}</td>
            <td>{{ $track->created_at }}</td>
        </tr>

    @endforeach
    </tbody>
</table>
