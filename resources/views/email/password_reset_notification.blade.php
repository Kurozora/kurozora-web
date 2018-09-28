@extends('email.email_base')

@section('content')
    Hey {{ $username or 'Kurozora User' }}, <br><br>

    We have received a password reset request for your account. <br>
    (IP: <b>{{ $ip }}</b>)<br><br>

    Please click the button below to reset your password. If it was not you who requested this password reset, you can ignore this email.

    <a href="{{ $reset_url }}" target="_blank" style="margin-top: 10px; display: block; border-radius: 10px; text-decoration: none; background: #ffa726; color: #fff; font-family: Ubuntu, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; padding: 5px 10px; text-align: center;">Reset my password</a>

    <p style="font-size: 12px; line-height: 12px; text-align: center; margin: 2px 0; color: #CCC;">
        or copy the URL below and paste it in your browser <br>
        <a href="{{ $reset_url }}" target="_blank" style="color: #FDC84A; font-weight: bold; text-decoration: none !important;">
            {{ $reset_url }}
        </a>
    </p>
@endsection