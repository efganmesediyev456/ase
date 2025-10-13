<table>
    <thead>
    <tr>
        <th><b>Name</b></th>
        <th><b>Code</b></th>
        <th><b>Country</b></th>
        <th><b>Package CWB</b></th>
        <th><b>Package Weight (kg)</b></th>
        <th><b>Package Delivery Price [AZN]</b></th>
        <th><b>Package Delivery Price [USD]</b></th>
        <th><b>Used Percent</b></th>
        <th><b>Used Amount [AZN]</b></th>
        <th><b>Used Amount [USD]</b></th>
        <th><b>Used Weight (kg)</b></th>
        <th><b>Used Weight Amount [AZN]</b></th>
        <th><b>Used Weight Amount [USD]</b></th>
        <th><b>Delivery Price [AZN] With Discount</b></th>
        <th><b>Delivery Price [USD] With Discount</b></th>
        <th><b>User</b></th>
        <th><b>Date</b></th>
    </tr>
    </thead>
    <tbody>
    @foreach($promo_logs as $promo_log)
        <tr>
            <td>{{ $promo_log->promo->name }}</td>
            <td>{{ $promo_log->promo->code }}</td>
            <td>{{ $promo_log->warehouse->country->code }}</td>
            <td>{{ $promo_log->package->custom_id }}</td>
            <td>{{ $promo_log->package->weight_goods ? $promo_log->package->weight_goods : $package->weight }}</td>
            <td>{{ $promo_log->package->delivery_manat_price }}</td>
            <td>{{ $promo_log->package->delivery_usd_price }}</td>
            <td>{{ $promo_log->package->promo_discount_percent }}</td>
            <td>{{ $promo_log->package->promo_discount_amount_azn }}</td>
            <td>{{ $promo_log->package->promo_discount_amount_usd }}</td>
            <td>{{ $promo_log->package->promo_discount_weight }}</td>
            <td>{{ $promo_log->package->promo_discount_weight_amount_azn }}</td>
            <td>{{ $promo_log->package->promo_discount_weight_amount_usd }}</td>
            <td>{{ $promo_log->package->delivery_manat_price_discount }}</td>
            <td>{{ $promo_log->package->delivery_usd_price_discount }}</td>
            <td>{{ $promo_log->user->fullname }}</td>
            <td>{{ $promo_log->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
