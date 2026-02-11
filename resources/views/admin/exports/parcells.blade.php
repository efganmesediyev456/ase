<table border="1">
    <thead>
    <tr>
        <th>Parcel Nömrəsi</th>
        <th>Ölkə</th>
        <th>Tarix</th>
        <th>Bağlamaların sayı</th>
        <th>Bakı / KOBIA-da skan olunma tarixi (delivered)</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr>
            {{-- Parcel Nömrəsi --}}
            <td>{{ $item->custom_id }}</td>

            {{-- Ölkə --}}
            <td>
                {{ optional(optional($item->warehouse)->country)->name }}
            </td>

            {{-- Tarix --}}
            <td>{{ optional($item->created_at)->format('Y-m-d H:i') }}</td>

            {{-- Bağlamaların sayı --}}
            <td>{{ $item->packages_count }}</td>

            {{-- Bakı / KOBIA skan tarixi --}}
            <td>
                {{ $item->first_scanned_at ? $item->first_scanned_at : '' }}
            </td>

            {{-- Status --}}
            <td>
                {{$item->sent_with_label}}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
