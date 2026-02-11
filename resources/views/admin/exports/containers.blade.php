<table>
    <thead>
    <tr>
        <th>Parcel Nömrəsi </th>
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
        <td>{{$item->name}}</td>
        <td>{{ $item->from_country }}</td>
        <td>{{ $item->created_at->diffForHumans() }}</td>
        <td>{{$item->tracks_count}}/ {{$item->track_not_completed_count}}</td>
        <td>
            @if($item->first_scanned_at)
                {{ $item->first_scanned_at }}
            @endif
            @if($item->scanned_cnt)
                ({{ $item->scanned_cnt }})
            @endif
        </td>
        <td>{{ $item->status_with_label }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
