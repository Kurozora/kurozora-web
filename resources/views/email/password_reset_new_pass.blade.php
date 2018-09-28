@extends('email.email_base')

@section('content')
    Hey {{ $username or 'Kurozora User' }}, <br><br>

    Your password has successfully been reset. Please use the password below to log into your account. <br><br>

    <div style="text-align: center;">
        New Password: <br>
        <span style="background: orange; color: #fff; font-weight: bold">{{ $newPass }}</span>
    </div>

    <br>

    It is advised that you change this password to one of your own as soon as possible.

    <br><br>
    <b>Note: All your logged in devices have automatically been logged out.</b>
@endsection