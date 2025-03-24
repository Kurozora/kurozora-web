@extends('email.email_base')

@section('content')
    Hey, {{ $username ?? __(':x User', ['x' => config('app.name')]) }}! <br><br>

    Your {{ config('app.name') }} account was successfully created. However, you'll first have to confirm your email address prior to signing in. <br><br>

    Click the button below to confirm your email address.

    <a href="{{ $confirmation_url }}" target="_blank" style="margin-top: 10px; display: block; border-radius: 10px; text-decoration: none; background: #FF9300; color: #fff; font-family: Ubuntu, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: normal; padding: 5px 10px; text-align: center;">Confirm email address</a>

    <p style="font-size: 12px; line-height: 12px; text-align: center; margin: 2px 0; color: #CCC;">
        or copy the URL below and paste it in your browser <br>
        <a href="{{ $verification_url }}" target="_blank" style="color: #FFA726; font-weight: bold; text-decoration: none !important;">
            {{ $confirmation_url }}
        </a>
    </p>
@endsection
