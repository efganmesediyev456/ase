<table style="width: 1400px">
    <thead>
    <tr>
       {{-- <th><b>User</b></th>--}}
        <th><b>Country</b></th>
        <th><b>AWB</b>{{ str_repeat("_", 9) }}</th>
        <th><b>CWB</b>{{ str_repeat("_", 15) }}</th>
        <th><b>Phone</b>{{ str_repeat("_", 15) }}</th>
        <th><b>By</b>{{ str_repeat("_", 11) }}</th>
        <th><b>Date</b>{{ str_repeat("_", 15) }}</th>
        <th><b>Amount</b></th>
        <th><b>Amount 90%</b></th>
        <th><b>For</b></th>
    </tr>
    </thead>
    <tbody>
    <?php $total = 0; ?>
    @foreach($transactions as $transaction)
        <tr>
            {{--<td>{{ $transaction->user ? $transaction->user->full_name : '-' }}</td>--}}
            <td>{{ ($transaction->warehouse && $transaction->warehouse->country) ? $transaction->warehouse->country->translateOrDefault('en')->name : '-' }}</td>
            <td>{{ $transaction->awb}}&nbsp;</td>
            <td>{{ $transaction->cwb}}</td>
            <td>{{ $transaction->phone}}</td>
            {{--<td>{{ $transaction->paid_for }}</td>--}}
            <td>{{ $transaction->paid_by }}</td>
            {{--<td>{{ $transaction->custom_id }}</td>--}}
            <td>{{ $transaction->created_at }}</td>
            <td>{{ $transaction->amount }}</td>
            <td>{{ $transaction->amount_90 }}</td>
            <td>{{ $transaction->paid_for }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="7">{{ str_repeat("_", 124) }}</td>
    </tr>
    <tr>
        <td colspan="7">{{ str_repeat("_", 124) }}</td>
    </tr>
    @foreach($types as $key => $amount)
        <tr>
            <td colspan="6">{{ $key }}</td>
            <td>{{ $amount }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="7">{{ str_repeat("      .      .", 51) }}</td>
    </tr>
    <tr>
        <td colspan="7">{{ str_repeat("      .      .", 51) }}</td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td>Date</td>
        <td>{{ Carbon\Carbon::today()->format("Y.m.d") }}</td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td>Name</td>
        <td>{{ str_repeat("_", 12) }}</td>
    </tr>
    <tr>
        <td colspan="4"></td>
        <td>Signature</td>
        <td>{{ str_repeat("_", 12) }}</td>
    </tr>
    </tbody>
</table>
