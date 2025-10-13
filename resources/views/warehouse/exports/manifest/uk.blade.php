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
        <td colspan="{{ $span }}" style="color: #fff">-</td>
    </tr>
    <tr>
        <td colspan="{{ $span }}" style="color: #fff">-</td>
    </tr>

    <tr>
        <td><b>Shipper</b></td>
        <td colspan="{{ $span - 6 }}">{{ $warehouse->company_name }}</td>
        <td><b>Receiver</b></td>
        <td colspan="4">ASE AFRIKA HIZLI CARGO AS</td>
    </tr>
    <tr>
        <td style="color: #fff">-</td>
        <td colspan="{{ $span - 6 }}">{{ $warehouse->address->address_line_1 }}</td>
        <td style="color: #fff">-</td>
        <td colspan="4">25 UZEIR HAJIBEYOV STR</td>
    </tr>
    <tr>
        <td style="color: #fff">-</td>
        <td colspan="{{ $span - 6 }}">{{ $warehouse->address->city }} {{ $warehouse->address->state }}
            , {{ $warehouse->address->zip_code }}</td>
        <td style="color: #fff">-</td>
        <td colspan="4">BAKU, AZERBAIJAN</td>
    </tr>
    <tr>
        <td style="color: #fff">-</td>
        <td colspan="{{ $span - 6 }}">{{ isset($warehouse->country->translate('en')->name) ? $warehouse->country->translate('en')->name : $warehouse->country->name }}</td>
        <td style="color: #fff">-</td>
        <td colspan="4">TEL: 994124973775</td>
    </tr>

    <tr>
        <td colspan="{{ $span }}" style="color: #fff"> -</td>
    </tr>
    <tr>
        <td colspan="{{ $span }}" style="color: #fff"> -</td>
    </tr>


    <tr>
        <th style="color: #fff">-</th>
        <th><b>SENDER</b></th>
        <th><b>BUYER</b></th>
        @if($ext == 'Xlsx')
            <th><b>FIN</b></th>
        @endif
        <th><b>ID CODE</b></th>
        <th><b>ADDRESS</b></th>
        <th><b>ITEM CODE</b></th>
        <th><b>ITEM DESCRIPTION</b></th>
        <th><b>QTY</b></th>
        <th><b>PRICE (GBP)</b></th>
        <th><b>WEIGHT (kg)</b></th>
        <th><b>Delivery price (USD)</b></th>
        <th><b>Total price (USD)</b></th>
    </tr>


    @foreach($packages as $key => $package)
        <tr>
            <td>{{ $key + 1 }} <span>         </span></td>
            <td><span>         </span> {{ $package->website_name ? getOnlyDomain($package->website_name) : '-' }}</td>
            <td>{{ $package->user ? $package->user->full_name : '-' }}</td>
            @if($ext == 'Xlsx')
                <td>{{ $package->user ? $package->user->fin : '-' }}</td>
            @endif
            <td>{{ $package->user ? str_replace("ASE", "", $package->user->customer_id) : '-' }} <span>         </span>
            </td>
            <td><span>         </span> {{ $package->user ? $package->user->address : '-' }}</td>
            <td>{{ $package->custom_id or '-' }} <span>         </span></td>
            <td> <span>         </span>{{ $package->detailed_type }}</td>
            <td>{{ $package->number_items_goods }}</td>
            <td>{{ specialPrice( $package->shipping_amount_goods ? $package->shipping_amount_goods : $package->shipping_amount) }}</td>
            <td>{{ specialPrice( $package->weight_goods ? $package->weight_goods : $package->weight) }}</td>
            <td>{{ $package->delivery_usd_price }}</td>
            <td>{{ $package->total_price }}</td>
        </tr>
    @endforeach

    </tbody>
</table>

</body>
</html>
