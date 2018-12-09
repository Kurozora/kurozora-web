@extends('admin_panel.main_template')

@section('content')
    <div class="container">
        <h4 class="white-text">Users</h4>

        <div class="card-panel white">
            <table class="highlight">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>username</th>
                        <th>role</th>
                        <th>registration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->username }} @if($user->id == $curUser->id)<span class="red-text">(you)</span>@endif</td>
                            <td>{{ \App\User::getStringFromRole($user->role) }}</td>
                            <td>{{ $user->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $users->links() }}
        </div>
    </div>
@endsection