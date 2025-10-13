<!doctype html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASE: Qutular</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1, h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 60px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .signature-section {
            margin-top: 20px;
        }
        .signature-section p{
            width: 120px;
            display: inline-block;
            margin: 0;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 300px;
            display: inline-block;
            margin-left: 20px;
        }
    </style>
</head>
<body>
<h1>Qutu ({{ $box->name }})</h1>
<table>
    <thead>
    <tr>
        <th>#No</th>
        <th>Qutu</th>
        <th>Tracking</th>
        <th>Qutuya əlavə edilmə tarixi</th>
    </tr>
    </thead>
    <tbody>
    @foreach($packages as $key => $package)
        <tr>
            <td>#{{ ++$key }}</td>
            <td>
                <a href="{{ route('hub.boxes.show', $package->box->id) }}">
                    <b>{{ $package->box->name }}</b>
                </a>
            </td>
            <td>
                @if($package->tracking)
                    <a href="{{ $package->parcel_type == 'package' ? route('packages.index', ['q' => $package->tracking]) : route('tracks.index', ['q' => $package->tracking]) }}" target="_blank">
                        {{ $package->tracking }}
                    </a>
                @else
                    Tapılmadı
                @endif
            </td>
            <td>{{ $package->created_at->format('d/m/Y H:i') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="signature-section">
    <p>Təslim etdi</p>
    <div class="signature-line"></div>
</div>
<div class="signature-section">
    <p>Qəbul edti</p>
    <div class="signature-line"></div>
</div>
<div class="signature-section">
    <p>Tarix</p>
    <div class="signature-line"></div>
</div>
</body>
</html>
