<table>
    <thead>
    <tr>
        <th><b>Receiver</b></th>
        <th><b>Dealer</b></th>
        <th><b>Customer Id</b></th>
        <th><b>Delivery Date</b></th>
        <th><b>Cell</b></th>
        <th><b>City</b></th>
        <th><b>Country</b></th>
        <th><b>Parcel</b></th>
        <th><b>CWB</b></th>
        <th><b>Track #</b></th>
        <th><b>QTY</b></th>
        <th><b>Description</b></th>
        <th><b>Weight (kq)</b></th>
        <th><b>Delivery Price [AZN]</b></th>
        <th><b>Delivery Price [USD]</b></th>
        <th><b>Discount</b></th>
        <th><b>Delivery Price [AZN] With Discount</b></th>
        <th><b>Delivery Price [USD] With Discount</b></th>
        <th><b>Value</b></th>
        <th><b>Paid</b></th>
        <th><b>Paid By</b></th>
        <th><b>Debt Price</b></th>
        <th><b>Paid Debt</b></th>
    </tr>
    </thead>
    <tbody>
    <?php 
        $skip=0; 
	$cnt=$packages->count();
	$limit=1000;
	while(true) {
	   $packages1=$packages->slice($skip)->take($limit);//->get();
	   //if(!$packages1) break;
	   if($skip>$cnt) break;
	   $skip+=$limit;
    ?>
    
    @foreach($packages1 as $package)
        <tr>
            <td>{{ $package->user ? $package->user->full_name : '-' }}</td>
            <td>{{ ($package->user && $package->user->dealer) ? $package->user->dealer->full_name : '-' }}</td>
            <td>{{ $package->user ? $package->user->customer_id : '-' }}</td>
            <td>{{ $package->scanned_at ? date( 'Y-m-d',strtotime($package->scanned_at)) : '-' }}</td>
            <td>{{ $package->cell }}</td>
            <td>{{ $package->user ? $package->user->city_name : '-' }}</td>
            <td>{{ ($package->warehouse and $package->warehouse->country) ? $package->warehouse->country->code : '-' }}</td>
            {{--<td>{{ $package->user ? ($package->user->phone ? \App\Models\Extra\SMS::clearNumber($package->user->phone) : '-' ) : '-' }}</td>--}}
            <td>{{ count($package->parcel)>0?$package->parcel[0]->custom_id:'' }}</td>
            <td>{{ $package->custom_id }}</td>
            <td>{{ $package->tracking_code }}</td>
            <td>{{ $package->number_items_goods }}</td>
            <td>{{ $package->detailed_type }}</td>
            <td>{{ $package->weight_goods ? $package->weight_goods : $package->weight }}</td>
            <td>{{ $package->delivery_manat_price }}</td>
            <td>{{ $package->delivery_usd_price }}</td>
            <td>{{ $package->discount_percent_with_label }}</td>
            <td>{{ $package->delivery_manat_price_discount }}</td>
            <td>{{ $package->delivery_usd_price_discount }}</td>
            <td>{{ $package->total_price_with_label }}</td>
            <td>{{ $package->paid_att_with_label }}</td>
            <td>{{ $package->transaction ? $package->transaction->paid_by : '-' }}</td>
            <td>{{ $package->debt_price }}</td>
            <td>{{ $package->paid_debt_att_with_label }}</td>
        </tr>
    @endforeach
    <?php  } ?>
    </tbody>
</table>
