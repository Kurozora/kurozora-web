@extends('email.email_base')

@section('content')
    Hey, {{ $username or 'Kurozora User' }}! <br><br>

    Your Kurozora account was successfully created. However, you'll first have to confirm your email address prior to signing in. <br><br>

    Click the button below to confirm your email address.

    <a href="{{ env('APP_URL', '') }}/confirmation/{{ $confirmation_id }}" target="_blank" style="margin-top: 10px; display: block; border-radius: 10px; text-decoration: none; background: #ffa726; color: #fff; font-family: Ubuntu, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; padding: 5px 10px; text-align: center;">Confirm email address</a>

    <p style="font-size: 12px; line-height: 12px; text-align: center; margin: 2px 0; color: #CCC;">
        or copy the URL below and paste it in your browser <br>
        <a href="{{ env('APP_URL', '') }}/confirmation/{{ $confirmation_id }}" target="_blank" style="color: #FDC84A; font-weight: bold; text-decoration: none !important;">
            {{ env('APP_URL', '') }}/confirmation/{{ $confirmation_id }}
        </a>
    </p>
@endsection