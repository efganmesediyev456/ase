<table>
    <thead>
    <tr>
        <th>Izləmə nömrəsi</th>
        <th>Ad Soyad</th>
        <th>ASE kod</th>
        <th>Konteyner</th>
        <th>Tarix</th>
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
                <td>{{ $row->precinctOffice->name }}</td>
                <td>{{ $row->created_at->format('d-m-Y') }}</td>
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



@php
    $totalPackages = $containers->sum(function ($container) {
        return $container->packages->count();
    });
@endphp

<br>

<table style="margin-top: 20px; width: 100%;">
    <tr>
        <td style="font-weight: bold;">Ədəd sayı:</td>
        <td>{{ $totalPackages }}</td>
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
