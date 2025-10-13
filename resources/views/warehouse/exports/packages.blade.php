<table align="center">
    <tbody>
    <tr>
        <td colspan="8"></td>
    </tr>
    <tr>
        <td colspan="8"></td>
    </tr>

    <tr>
        <td></td>
        <td><b>Shipper</b></td>
        <td colspan="2">{{ $warehouse->company_name }}</td>
        <td><b>Receiver</b></td>
        <td colspan="3">ASE AFRIKA HIZLI CARGO AS</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td colspan="2">{{ $warehouse->address->address_line_1 }}</td>
        <td></td>
        <td colspan="3">25 UZEIR HAJIBEYOV STR</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td colspan="2">{{ $warehouse->address->city }} {{ $warehouse->address->state }}, {{ $warehouse->address->zip_code }}</td>
        <td></td>
        <td colspan="3">BAKU, AZERBAIJAN</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td colspan="2">{{ $warehouse->country->name }}</td>
        <td></td>
        <td colspan="3">TEL: 994124973775</td>
    </tr>
    <tr>
        <td colspan="8"></td>
    </tr>
    </tbody>
</table>


<table>
    <tbody>
    <?php $bkey=0; ?>
    @if($bags)
    @foreach($bags as $key1 => $bag)
    @if($bag->packages_count > 0)
    <tr>
        <?php $bkey++; ?>
        <td></td>
        <td colspan="5"><b>{{ $bkey }} BAG {{ $bag->custom_id }}</b></td>
    </tr>
    <tr>
        <td></td>
        <td><b>CWB</b></td>
        <td><b>QTY</b></td>
        <td><b>Description</b></td>
        <td><b>Weight (kg)</b></td>
        <td><b>Value ({{ $warehouse->currency_with_label }})</b></td>
    </tr>
    @foreach($bag->packages as $key => $package)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $package->custom_id }}</td>
            <td>{{ $package->number_items_goods }}</td>
            <td>{{ $package->detailed_type }}</td>
            <td>{{ $package->weight_goods ? $package->weight_goods : $package->weight }}</td>
            <td>{{ $package->shipping_amount_goods ? $package->shipping_amount_goods : $package->shipping_amount}}</td>
        </tr>
    @endforeach
    @endif
    @endforeach
    @else
    <tr>
        <td></td>
        <td colspan="5"></td>
    </tr>
    <tr>
        <td>#</td>
        <td><b>Receiver</b></td>
        <td><b>Parcel</b></td>
        <td><b>Bag</b></td>
        <td><b>CWB</b></td>
        <td><b>Track #</b></td>
        <td><b>QTY</b></td>
        <td><b>Description</b></td>
        <td><b>Weight (kg)</b></td>
        <td><b>Status</b></td>
        <td><b>Value ({{ $warehouse->currency_with_label }})</b></td>
        <td><b>Worker</b></td>
        <td><b>Time</b></td>
    </tr>
    @foreach($packages as $key => $package)
        <tr>
            <td>{{ $key + 1 }}</td>
	    <td>{{ $package->user ? $package->user->full_name : '-' }}</td>
            <td>{{ $package->parcel_name }}</td>
            <td>{{ $package->bag_name }}</td>
            <td>{{ $package->custom_id }}</td>
            <td>{{ $package->tracking_code }}</td>
            <td>{{ $package->number_items_goods }}</td>
            <td>{{ $package->detailed_type }}</td>
            <td>{{ $package->weight_goods ? $package->weight_goods : $package->weight }}</td>
            <td>{{ $package->status_label }}</td>
            <td>{{ $package->shipping_amount_goods ? $package->shipping_amount_goods : $package->shipping_amount}}</td>
            <td>{{ $package->activityworker ? $package->activityworker->name : '-' }}</td>
            <td>{{ $package->created_at }}</td>
        </tr>
    @endforeach
    @endif
    </tbody>
</table>
