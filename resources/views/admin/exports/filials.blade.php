<table>
    <thead>
    <tr>
        <th><b>Type (New)</b></th>
        <th><b>Type</b></th>
        <th><b>UID</b></th>
        <th><b>Foreign Id	</b></th>
        <th><b>Type İD</b></th>
        <th><b>Contact Phone</b></th>
        <th><b>Contact Name</b></th>
        <th><b>Name</b></th>
        <th><b>Description</b></th>
        <th><b>Address</b></th>
        <th><b>City</b></th>
        <th><b>Longtitude</b></th>
        <th><b>Latitude</b></th>
        <th><b>Work Time</b></th>
        <th><b>Registration date</b></th>
    </tr>
    </thead>
    <tbody>
    @foreach($filials as $filial)
        @php
            $parts = explode('-', $filial->type_id);
            $prefix = $parts[0] ?? '';
            $suffix = $parts[1] ?? '';

            switch($prefix) {
                case 'AZEXP':
                    $code = 'EX';
                    break;
                case 'AZPOST':
                    $code = 'AZ';
                    break;
                case 'ASE':
                    $code = 'DP';
                    break;
                case 'SURAT':
                    $code = 'SR';
                    break;
                case 'KARGOMAT':
                    $code = 'KR';
                    break;
                default:
                    $code = $prefix;
            }
        @endphp
        <tr>
            <td>{{ $code }}-{{ $suffix }}</td>
            <td>{{ $filial->type }}</td>
            <td>{{ $code . $suffix }}</td>
            <td>{{ $filial->foreign_id }}</td>
            <td>{{ $filial->type_id }}</td>
            <td>{{ $filial->contact_phone }}</td>
            <td>{{ $filial->contact_name }}</td>
            <td>{{ $filial->name }}</td>
            <td>{{ $filial->description }}</td>
            <td>{{ $filial->address }}</td>
            <td>{{ $filial->city_name }}</td>
            <td>{{ $filial->longitude }}</td>
            <td>{{ $filial->latitude }}</td>
            <td>{{ $filial->work_time }}</td>
            <td>{{ date( 'Y-m-d',strtotime($filial->created_at)) }}</td>

        </tr>
    @endforeach
    </tbody>
</table>
