<table>
    <thead>
    <tr>
        <th><b>Admin #</b></th>
        <th><b>Worker #</b></th>
        <th><b>Track #</b></th>
        <th><b>Data #</b></th>
        <th><b>Created at</b></th>
    </tr>
    </thead>
    <tbody>
    @foreach($activities as $activity)

        <tr>
            <td>{{ $activity->admin->name ?? '-' }}</td>
            <td>{{ $activity->worker->name ?? '-' }}</td>
            <td>{{ $activity->content_code }}</td>
            <td>{{ strip_tags($activity->data) }}</td>
            <td>{{ $activity->created_at }}</td>
        </tr>

    @endforeach
    </tbody>
</table>
