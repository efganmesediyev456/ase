<html>
<style>
    table {
        border: none !important;
    }
</style>
<body>
<table style="width: 1200px">
    <tbody>


    <tr>
        <th><b>isEtgb</b></th>
        <th><b>OrderCode</b></th>
        <th><b>Cwb Code</b></th>
        <th><b>Customer Referance</b></th>
        <th><b>Sender</b></th>
        <th><b>Sender Name</b></th>
        <th><b>Sender Country</b></th>
        <th><b>Sender City</b></th>
        <th><b>Sender Post Code</b></th>
        <th><b>Sender Address</b></th>
        <th><b>Sender Telephone</b></th>
        <th><b>Sender Email</b></th>
        <th><b>Receiver Name</b></th>
        <th><b>Contact Person</b></th>
        <th><b>Receiver Country</b></th>
        <th><b>Receiver City</b></th>
        <th><b>Receiver Post Code</b></th>
        <th><b>Receiver Address</b></th>
        <th><b>Receiver Telephone</b></th>
        <th><b>Receiver Email</b></th>
        <th><b>Dox/Sps</b></th>
        <th><b>DDU/DDP</b></th>
        <th><b>Invoice Number</b></th>
        <th><b>Total Invoice Value</b></th>
        <th><b>Invoice Currency</b></th>
        <th><b>Prepaid/Collect</b></th>
        <th><b>Total Weight (Kg)</b></th>
        <th><b>Cod Amount</b></th>
        <th><b>Cod Currency</b></th>
        <th><b>Product Description</b></th>
        <th><b>HS. Code (Gtip)</b></th>
        <th><b>Product Quantity</b></th>
        <th><b>Unit Kg</b></th>
        <th><b>Unit Price</b></th>
        <th><b>Product Code</b></th>
        <th><b>Web Url</b></th>
        <th><b>Origin of product (ETGB)</b></th>
        <th><b>Delivery Price (USD)</b></th>
    </tr>


    @foreach($packages as $key => $package)
        <tr>
            <td>no</td>
            <td>{{ $package->tracking_code }}</td>
            <td>{{ $package->custom_id }}</td>
            <td>{{ $package->user ? str_replace("ASE", "", $package->user->customer_id) : '-' }}</td>
            <td>{{ $package->website_name ? getOnlyDomain($package->website_name) : '-' }}</td>
            <td>{{ $package->website_name ? getOnlyDomain($package->website_name) : '-' }}</td>
            <td>tr</td>
            <td>ist</td>
            <td>34306</td>
            <td>{{ $package->fake_address ?: $warehouse->address->address_line_1 }}</td>
            {{--<td>+90 212 970 2178</td>
            <td>tr@tibiex.com</td>--}}
            <td></td>
            <td></td>
            <td>{{ $package->user ? $package->user->full_name : '-' }}</td>
            <td>{{ $package->user ? $package->user->full_name : '-' }}</td>
            <td>AZ</td>
            <td>Bakı</td>
            <td>{{ $package->user ? $package->user->fin : '-' }}</td>
            <td>{{ $package->user ? $package->user->address : '-' }}</td>
            <td>{{ $package->user ? App\Models\Extra\SMS::clearNumber($package->user->phone, true, " ") : null }}</td>
            <td>{{ $package->user ? $package->user->email : "-" }}</td>
            <td>sps</td>
            <td>ddu</td>
            <td></td>
            <td>{{ $package->shipping_converted_price }}</td>
            <td>USD</td>
            <td>Prepaid</td>
            <td>{{ $package->weight }}</td>
            <td></td>
            <td></td>
            <td>
                {{ $package->detailed_type }}
            </td>
            <td></td>
            <td>{{ $package->number_items_goods }}</td>
            <td>{{ specialPrice( $package->weight_goods ? $package->weight_goods : $package->weight) }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td> {{ $package->delivery_usd_price }}</td>
        </tr>
    @endforeach

    </tbody>
</table>

</body>
</html>
