<table>
    <thead>
    <tr>
        <th><b>MAWB</b></th>
        <th><b>Packages</b></th>
        <th><b>Status</b></th>
        <th><b>N/D Status</b></th>
        <th><b>Courier</b></th>
        <th><b>Courier Comment</b></th>
        <th><b>Recipient</b></th>
        <th><b>Phone</b></th>
        <th><b>Address</b></th>
        <th><b>Paid</b></th>
        <th><b>Delivery price</b></th>
        <th><b>User Comment</b></th>
        <th><b>Assigned At</b></th>
        <th><b>Courier Get At</b></th>
        <th><b>Delivered At</b></th>
        <th><b>Created At</b></th>
    </tr>
    </thead>
    <tbody>
    <?php 
        $skip=0; 
	$cnt=$cds->count();
	$limit=1000;
	while(true) {
	   $cds1=$cds->slice($skip)->take($limit);//->get();
	   //if(!$packages1) break;
	   if($skip>$cnt) break;
	   $skip+=$limit;
    ?>
    
    @foreach($cds1 as $cd)
        <tr>
            <td>{{ $cd->parcel_name}}</td>
            <td>{{ $cd->packages_with_cells_nl_str}}</td>
            <td>{{ $cd->status_with_label}}</td>
            <td>{{ $cd->not_delivered_status_with_label}}</td>
            <td>{{ $cd->courier ? $cd->courier->name : ''}}</td>
            <td>{{ $cd->courier_comment}}</td>
            <td>{{ $cd->name}}</td>
            <td>{{ $cd->phone}}</td>
            <td>{{ $cd->address}}</td>
            <td>{{ $cd->paid ? 'yes' : 'no'}}</td>
            <td>{{ $cd->delivery_price}}</td>
            <td>{{ $cd->user_comment}}</td>
            <td>{{ $cd->courier_assigned_at}}</td>
            <td>{{ $cd->courier_get_at}}</td>
            <td>{{ $cd->delivered_at}}</td>
            <td>{{ $cd->created_at}}</td>
        </tr>
    @endforeach
    <?php  } ?>
    </tbody>
</table>
