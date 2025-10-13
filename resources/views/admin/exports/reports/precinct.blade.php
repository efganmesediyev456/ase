<table>
    <thead>
    <tr>
        <th>Izləmə nömrəsi</th>
        <th>Ad Soyad</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($packages as $package)
        <tr>
            <td>{{ $package->barcode }}</td>
            <td>
                @if($package->package)
                    {{ $package->package->user->full_name??'-' }}
                @elseif($package->track)
                    {{ $package->track->customer->fullname??'-' }}
                @endif
            </td>
            <td>{{ __('admin.precinct_warehouse_package_status_' . $package->status) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
