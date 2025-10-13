<table>
    <thead>
    <tr>
        <th><b>#</b></th>
        <th><b>RRN</b></th>
        <th><b>Amount</b></th>
        <th><b>Time at Portmanat</b></th>
        <th><b>Time</b></th>
        <th><b>Packages</b></th>
    </tr>
    </thead>
    <tbody>
    <?php 
	$cnt=count($packages);
	$num=0;
    ?>
    
    @foreach($packages as $package)
    <?php 
	$rrn=NULL;
	$created_at=NULL;
	if($package->extra_data) {
	    $data = \GuzzleHttp\json_decode($package->extra_data, true);
	    $rrn=isset($data['body']['client_rrn']) ? $data['body']['client_rrn'] : "-";
	    $created_at=isset($data['body']['created_at']['date']) ? $data['body']['created_at']['date'] : "-";
	}
    ?>
        <tr>
	    <?php $num++; ?>
            <td>{{ $num }}</td>
            <td>{{ $rrn }}</td>
            <td>{{ $package->amount }}</td>
            <td>{{ date( 'Y-m-d H:i:s',strtotime($created_at)) }}</td>
            <td>{{ date( 'Y-m-d H:i:s',strtotime($package->created_at)) }}</td>
            <td>{{ $package->pkg_custom_id }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
