<table>
    <thead>
    <tr>
        <th>Partner</th>
        <th>CWB #</th>
        <th>Track #</th>
        <th>Parcel</th>
        <th>Bag</th>
        <th>User</th>
        <th>Country</th>
        <th>Weight</th>
        <th>Delivery Price</th>
        <th>Status</th>
        <th>Paid</th>
        <th>Debt Price</th>
        <th>Paid Debt</th>
        <th>Stop Debt</th>
        <th>Filial</th>
    </tr>
    </thead>
    <tbody>
    @foreach($tracks as $track)
        <tr>
            <td>{{ $track->partner_with_label }}</td>
            <td>{{ $track->custom_id }}</td>
            <td>{{ $track->tracking_code }}</td>
            <td>{{ $track->container_name }}</td>
            <td>{{ $track->airbox_name }}</td>
            <td>{{ $track->fullname }}</td>
            <td>{{ $track->from_country }}</td>
            <td>{{ $track->weight }} kg</td>
            <td>{{ $track->delivery_price }} $ / {{ $track->delivery_price_azn }} ₼</td>
            <td>{{ $track->status_with_label }} </td>
            <td>{{ $track->paid == 1 ? 'Yes' : 'No' }}</td>
            <td>{{ $track->debt_price }} ₼</td>
            <td>{{ $track->paid_debt_att_with_label }}</td>
            <td>{{ $track->stop_debt_att_with_label }}</td>
            <td>{{ $track->filial_name }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
