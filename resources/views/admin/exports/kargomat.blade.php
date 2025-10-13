<table>
    <thead>
    <tr>
        <th>Izləmə nömrəsi</th>
        <th>Ad Soyad</th>
        <th>ASE kod</th>
        <th>Konteyner</th>
        <th style="min-width: 100px">Təhvil verildi</th>
        <th style="min-width: 100px">Təhvil alındı</th>
    </tr>
    </thead>
    <tbody>
    @foreach($containers as $row)
        @foreach($row->packages as $package)
            <tr>
                <td>{{ $package->barcode }}</td>
                <td>
                    @if($package->package)
                        {{ $package->package->user->full_name??'-' }}
                    @elseif($package->track)
                        {{ $package->track->customer->fullname??'-' }}
                    @endif
                </td>
                <td>
                    @if($package->package)
                        {{ $package->package->user->customer_id??'-' }}
                    @elseif($package->track)
                        {{ $package->track->customer_id }}
                    @endif
                </td>
                <td>{{ $row->name }}</td>
                <td></td>
                <td></td>
            </tr>
        @endforeach
        <tr>
            <td></td>
        </tr>
    @endforeach
    </tbody>
</table>
