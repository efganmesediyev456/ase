<!DOCTYPE html>
<html>
<head>
    <style>
        .receipt {
            width: 300px;
            padding: 20px;
            font-family: Arial, sans-serif;
            border: 1px solid #ddd;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
            font-size: 18px; /* Increased from default */
        }

        .logo {
            max-width: 100px;
            margin-bottom: 10px;
        }

        .customer-info {
            margin-bottom: 15px;
            padding: 5px;
            border: 1px solid #000;
            font-size: 16px; /* Increased from default */
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 16px; /* Increased from 12px */
            text-align: left;
        }

        .totals {
            border-top: 1px solid #000;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 16px; /* Increased from default */
        }

        .signatures {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 16px; /* Increased from default */
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px; /* Increased from default */
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 16px; /* Increased from 12px */
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <img src="https://aseshop.az/uploads/setting/59e0ea432e511.png" class="logo" alt="ASE">
        <div>
            @if(!empty($tracks->first()) && $tracks->first()->customer)
                {{ $tracks->first()->customer->fullname }}
            @elseif(!empty($packages->first()) && $packages->first()->user)
                {{ $packages->first()->user->fullname }}
            @endif
        </div>
        <div>{{ !empty($tracks->first()) ? $tracks->first()->customer->fin :(!empty($packages->first()) && $packages->first()->user?$packages->first()->user->fin:'') }}</div>
    </div>

    @php
        $i = 1;
    @endphp
    <table class="items-table">
        <tr>
            <th>№</th>
            <th>Barkod</th>
            <th>Rəf</th>
        </tr>

        @foreach($packages as $package)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $package->custom_id }}</td>
                <td>{{ $package->cell }}</td>
            </tr>
        @endforeach
        @foreach($tracks as $track)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $track->tracking_code }}</td>
                <td>{{ $track->cell }}</td>
            </tr>
        @endforeach
        <!-- Add more rows as needed -->
    </table>

    <div class="signatures">
        <div class="signature-row">
            <span>Anbardar</span>
            <span>imza: _________</span>
        </div>
        <div class="signature-row">
            <span>Təhvil alan şəxs</span>
            <span>imza: _________</span>
        </div>
        <div class="signature-row">
            <span>Tarix</span>
            <span>{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <div class="footer">
        Bizimlə əməkdaşlıq etdiyiniz üçün təşəkkür edirik!
    </div>
</div>
</body>
<script>
    function printPage() {
        window.print();
    }

    window.onload = function() {
        printPage();
    };
</script>
</html>
