@extends('admin_panel.main_template', ['hide_sidebar' => true])

@section('content')
    <div class="container white-text">
        <h3 class="center">Login</h3>

        @if(session('admin_login_msg') !== null)
            <div class="card-panel kurozora-orange brown-text">
                <span>{{ session('admin_login_msg') }}</span>
            </div>
        @endif

        <div class="valign-wrapper row">
            <div class="col card hoverable s10 pull-s1 m8 pull-m2 l6 pull-l3">
                <form method="POST" action="{{ url('admin/login') }}" id="loginForm">
                    @csrf
                    <div class="card-content">
                        <span class="card-title grey-text">Enter your credentials</span>
                        <div class="row">
                            <div class="input-field col s12">
                                <label for="email">Email address</label>
                                <input type="email" class="validate" name="email" id="email" required autofocus />
                            </div>
                            <div class="input-field col s12">
                                <label for="password">Password </label>
                                <input type="password" class="validate" name="password" id="password" required />
                            </div>
                        </div>
                    </div>

                    <div class="card-action right-align">
                        <button type="reset" class="btn-flat black-text">
                            Reset
                        </button>

                        <button type="submit" class="btn green waves-effect waves-light" id="loginBtn">
                            <i class="material-icons left">vpn_key</i>
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection