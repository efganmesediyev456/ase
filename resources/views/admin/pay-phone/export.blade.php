<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Phone</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Order ID</th>
        <th>Redirect URL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($pay_phones as $phone)
        <tr>
            <td>{{ $phone->id }}</td>
            <td>{{ $phone->phone }}</td>
            <td>{{ $phone->amount }}</td>
            <td>{{ $phone->status }}</td>
            <td>{{ $phone->created_at }}</td>
            <td>{{ $phone->order_id }}</td>
            <td>{{ $phone->redirect_url }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
