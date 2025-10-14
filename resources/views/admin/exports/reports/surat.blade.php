<table>
    <thead>
    <tr>
        <th>Izləmə nömrəsi</th>
        <th>Ad Soyad</th>
        <th>Status</th>
        <th>Məntəqə</th>
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
            <td>
                @if($package->package)
                    {{ $package->package->user->customer_id??'-' }}
                @elseif($package->track)
                    {{ $package->track->customer_id }}
                @endif
            </td>
            <td>{{ __('admin.surat_warehouse_package_status_' . $package->status) }}</td>
            @if($package->container)
                @if($package->container->suratOffice)
                    <td>{{ $package->container->suratOffice->name }}</td>
                @endif
            @endif
        </tr>
    @endforeach
    </tbody>
</table>



<br>

<table style="margin-top: 20px; width: 100%;">
    <tr>
        <td style="font-weight: bold;">Ədəd sayı:</td>
        <td>{{ $packages->count() }}</td>
    </tr>
    <tr>
        <td style="padding-top: 10px;">Təhvil verdi: _____________________</td>
        <td style="padding-top: 10px;">Təhvil aldı: _____________________</td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 15px;">
            Tarix: {{ now()->format('d.m.Y') }}
        </td>
    </tr>
</table>