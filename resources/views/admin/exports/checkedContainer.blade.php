<table>
    <thead>
    <tr>
        <th>Izləmə nömrəsi</th>
        <th>Ad Soyad</th>
        <th>ASE kod</th>
    </tr>
    </thead>
    <tbody>
    @foreach($containers as $package)
            <tr>
                <td>{{ $package->barcode }}</td>
                <td>
                    @if($package->package && $package->type == 'package')
                        {{ $package->package->user->full_name??'-' }}
                    @elseif($package->track && $package->type == 'track')
                        {{ $package->track->customer->fullname??'-' }}
                    @endif
                </td>
                <td>
                    @if($package->package && $package->type == 'package')
                        {{ $package->package->user->customer_id??'-' }}
                    @elseif($package->track && $package->type == 'track')
                        {{ $package->track->customer_id }}
                    @endif
                </td>
            </tr>
    @endforeach
    </tbody>
</table>
