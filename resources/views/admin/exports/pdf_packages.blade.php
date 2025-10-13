    <div>
        <img src="{{ asset('admin/images/logo_cert.jpg') }}" alt="">
    </div>
<table>
    <thead>
    <tr>
        <th><b>Receiver</b></th>
        <th><b>City</b></th>
        <th><b>CWB</b></th>
        <th><b>QTY</b></th>
        <th><b>Description</b></th>
        <th><b>Weight (kq)</b></th>
        <th><b>Delivery Price [AZN]</b></th>
        <th><b>Delivery Price [USD]</b></th>
        <th><b>Discount</b></th>
        <th><b>Delivery Price [AZN] With Discount</b></th>
        <th><b>Delivery Price [USD] With Discount</b></th>
        <th><b>Value</b></th>
        <th><b>Paid By</b></th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $package)
        <tr>
            <td>{{ $package->user ? $package->user->full_name : '-' }}</td>
            <td>{{ $package->user ? $package->user->city_name : '-' }}</td>
            {{--<td>{{ $package->user ? ($package->user->phone ? \App\Models\Extra\SMS::clearNumber($package->user->phone) : '-' ) : '-' }}</td>--}}
            <td>{{ $package->custom_id }}</td>
            <td>{{ $package->number_items }}</td>
            <td>{{ ($package->type_id && $package->type ? ($package->type->translate('en') ? ($package->type->translate('en')->name . ($package->other_type ? "(" . $package->other_type .")" : null)) : "-") : ($package->detailed_type ?: '-')) }}</td>
            <td>{{ $package->weight }}</td>
            <td>{{ $package->delivery_manat_price }}</td>
            <td>{{ $package->delivery_usd_price }}</td>
            <td>{{ $package->discount_percent_with_label }}</td>
            <td>{{ $package->delivery_manat_price_discount }}</td>
            <td>{{ $package->delivery_usd_price_discount }}</td>
            <td>{{ $package->total_price_with_label }}</td>
            <td>{{ $package->transaction ? $package->transaction->paid_by : '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
