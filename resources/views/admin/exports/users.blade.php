<table>
    <thead>
    <tr>
        <th><b>Customer Id</b></th>
        <th><b>Name</b></th>
        <th><b>Surname</b></th>
        <th><b>Packages</b></th>
        <th><b>Last Login</b></th>
        <th><b>Registration date</b></th>
        <th><b>City</b></th>
        <th><b>Gender</b></th>
        <th><b>Birthday</b></th>
        <th><b>Phone Number</b></th>
        <th><b>Email</b></th>
    </tr>
    </thead>
    <tbody>
    @foreach($users as $user)
        <tr>
            <td>{{ $user->customer_id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->surname }}</td>
            <td>{{ $user->packages_cnt }}</td>
            <td>{{ date( 'Y-m-d',strtotime($user->login_at)) }}</td>
            <td>{{ date( 'Y-m-d',strtotime($user->created_at)) }}</td>
            <td>{{ $user->city_name }}</td>
            <td>{{ $user->gender ? 'Male' : 'Female' }}</td>
            <td>{{ $user->birthday or '-' }}</td>
            <td>{{ App\Models\Extra\SMS::clearNumber($user->phone, true) }}</td>
            <td>{{ $user->email }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
