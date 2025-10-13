<table>
    <thead>
    <tr>
        <th>Parcel</th>
        <th>CWB #</th>
        <th>Track #</th>
        <th>User</th>
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
    @foreach($packages as $package)
        <tr>
            <td>{{ $package->parcel_name }}</td>
            <td>{{ $package->custom_id }}</td>
            <td>{{ $package->tracking_code }}</td>
            <td>{{ $package->user->name.' '.$package->user->surname.' ('.$package->user->customer_id.')' }}</td>
            <td>{{ $package->weight }} kg</td>
            <td>{{ $package->delivery_price }} $ / {{ $package->delivery_price_azn }} ₼</td>
            <td>{{ $package->status_label }} </td>
            <td>{{ $package->paid_with_label }} </td>
            <td>{{ $package->debt_price }} ₼</td>
            <td>{{ $package->paid_debt_att_with_label }}</td>
            <td>{{ $package->stop_debt_att_with_label }}</td>
            <td>{{ $package->filial_name }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
