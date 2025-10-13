<table style="width: 1400px">
    <thead>
    <tr>
       {{-- <th><b>User</b></th>--}}
        <th><b>Country</b></th>
        <th><b>AWB</b>{{ str_repeat("_", 9) }}</th>
        <th><b>CWB</b>{{ str_repeat("_", 15) }}</th>
        <th><b>By</b>{{ str_repeat("_", 11) }}</th>
        <th><b>RRN</b>{{ str_repeat("_", 11) }}</th>
        <th><b>Date</b>{{ str_repeat("_", 15) }}</th>
        <th><b>Amount</b></th>
        <th><b>Amount 90%</b></th>
        <th><b>Amount 90% USD</b></th>
    </tr>
    </thead>
    <tbody>
    <?php $total = 0; ?>
    @foreach($transactions as $transaction)
        <tr>
            {{--<td>{{ $transaction->user ? $transaction->user->full_name : '-' }}</td>--}}
            <td>
                @if ($transaction->paid_for === 'PACKAGE' && $transaction->package && $transaction->package->warehouse && $transaction->package->warehouse->country)
                    {{ $transaction->package->warehouse->country->translateOrDefault('en')->name }}
                @elseif ($transaction->paid_for === 'TRACK_DELIVERY' && $transaction->track && $transaction->track->warehouse && $transaction->track->warehouse->country)
                    {{ $transaction->track->warehouse->country->translateOrDefault('en')->name }}
                @else
                    -
                @endif
            </td>
            <td>{{ $transaction->awb}}&nbsp;</td>
            <td>{{ $transaction->cwb}}</td>
            {{--<td>{{ $transaction->paid_for }}</td>--}}
            <td>{{ $transaction->paid_by }}</td>
            <td>{{ $transaction->source_id }}</td>
            {{--<td>{{ $transaction->custom_id }}</td>--}}
            <td>{{ $transaction->created_at }}</td>
            <td>{{ $transaction->amount }}</td>
            <td>{{ $transaction->amount_90 }}</td>
            <td>{{  round( $transaction->amount_90 /1.702 , 2) }}</td>
        </tr>
    @endforeach

    </tbody>
</table>
