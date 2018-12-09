@extends('admin_panel.main_template')

@section('content')
    <div class="container">
        <h4 class="white-text">Users</h4>

        <div class="card-panel white">
            {{-- Search filters --}}
            <form action="{{ url('/admin/users') }}" method="GET">
                <ul class="collapsible">
                    <li>
                        <div class="collapsible-header">
                            <i class="material-icons">filter_list</i>Filters
                            @if($filterData['activeFilterCount'] > 0)
                            <span class="badge kurozora-orange white-text">{{ $filterData['activeFilterCount'] }} active</span>
                            @endif
                        </div>
                        <div class="collapsible-body">
                            <div class="row">
                                {{-- Role filter --}}
                                <div class="input-field col s12">
                                    <select name="role">
                                        <option value="-1">All roles</option>
                                        @foreach($filterData['roles'] as $roleID => $roleStr)
                                            <option value="{{ $roleID }}" @if($filterData['selectedRole'] === $roleID) selected @endif>{{ $roleStr }}</option>
                                        @endforeach
                                    </select>
                                    <label>User role</label>
                                </div>

                                {{-- Clear all filters --}}
                                <div class="input-field col s12">
                                    <a href="{{ url('/admin/users') }}" class="btn-flat">
                                        reset
                                    </a>

                                    {{-- Apply filters --}}
                                    <button class="btn kurozora-orange" type="submit">
                                        Apply filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </form>


            {{-- Users table --}}
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
                    @if(!count($users))
                        <tr>
                            <td>...</td>
                            <td>No users found</td>
                            <td>...</td>
                            <td>...</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            {{-- Pagination --}}
            {{ $users->links('admin_panel.pagination') }}
        </div>
    </div>
@endsection