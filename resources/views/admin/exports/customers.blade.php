<table>
    <thead>
    <tr>
        <th><b>Partner</b></th>
        <th><b>Fin</b></th>
        <th><b>Fullname</b></th>
        <th><b>City</b></th>
        <th><b>Phone</b></th>
        <th><b>Email</b></th>
        <th><b>Address</b></th>
        <th><b>Created At</b></th>
    </tr>
    </thead>
    <tbody>
    <?php 
        $skip=0; 
	$cnt=$customers->count();
	$limit=1000;
	while(true) {
	   $customers1=$customers->slice($skip)->take($limit);//->get();
	   if($skip>$cnt) break;
	   $skip+=$limit;
    ?>
    
    @foreach($customers1 as $customer)
        <tr>
            <td>{{ $customer->partner_with_label}}</td>
            <td>{{ $customer->fin}}</td>
            <td>{{ $customer->fullname}}</td>
            <td>{{ $customer->city? $customer->city->name : $customer->city_name}}</td>
            <td>{{ App\Models\Extra\SMS::clearNumber($customer->phone, true)}}</td>
            <td>{{ $customer->email}}</td>
            <td>{{ $customer->address}}</td>
            <td>{{ $customer->created_at}}</td>
        </tr>
    @endforeach
    <?php  } ?>
    </tbody>
</table>
