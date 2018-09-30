@extends('website.main_template')

@section('content')
    <div class="container">
        <div class="row login-row">
            <div class="col s12 m6 offset-m3">
                <div class="card-panel" id="login-top-panel">
                    <h1>Login to your account</h1>
                </div>
            </div>
        </div>
        <div class="row login-row">
            <div class="col s12 m6 offset-m3">
                <div class="card-panel" id="login-panel">
                    <div class="container">
                        <p class="grey-text text-darken-2">Your Kurozora account works across all platforms. Login below if you already have an account.</p>

                        <div class="row">
                            <div class="input-field col s12">
                                <input class="login-input" id="username" type="text" autofocus>
                                <label for="username">Username</label>
                            </div>

                            <div class="input-field col s12">
                                <input class="login-input" id="password" type="password">
                                <label for="password">Password</label>
                            </div>

                            <div class="col s12 red-text" id="loginError"></div>

                            <div class="input-field col s12">
                                <button class="right btn waves-effect waves-light kurozora-orange" id="loginBtn">Login
                                    <i class="material-icons right">send</i>
                                </button>
                            </div>

                            <div class="col s12">
                                <span class="grey-text">or <a href="{{ asset('/register') }}">register a new account</a>.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .login-row {
            margin: 0;
            padding: 0;
        }

        #login-top-panel {
            background-image: url({{ asset('img/static/star_bg_md.jpg') }});
            background-position: center;
            -webkit-background-size: cover;
            background-size: cover;
            margin-top: 30px;
            margin-bottom: 0;

            border-radius: 10px 10px 0 0;
        }
            #login-top-panel h1 {
                color: #fff;
                text-align: center;
                font-size: 30px;
                padding: 5px 0;
            }

        #login-panel {
            border-radius: 0 0 5px 5px;
            margin: 0;
        }

        .login-input {
            display: block;
            width: 100%;
        }
    </style>
@endsection