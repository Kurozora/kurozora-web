@extends('admin_panel.main_template')

@section('content')
<div class="container">
    <h4 class="white-text center">Welcome back, {{ $curUser->username }}.</h4>
</div>
@endsection