<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Plant Portal Sign In | {{ Qs::getSystemName() }}</title>

    <link rel="icon" href="{{ Qs::getSystemLogo() }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{ asset('global_assets/css/icons/icomoon/styles.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/bootstrap_limitless.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/layout.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/components.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/colors.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/theme-green-white.css') }}" rel="stylesheet" type="text/css">
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #044e39 100%);">

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">

                <!-- Brand Card -->
                <div class="text-center mb-3">
                    <div class="d-inline-block bg-white p-2 rounded shadow-sm mb-2">
                        <img src="{{ Qs::getSystemLogo() }}" alt="Metonia" style="height: 38px;">
                    </div>
                    <h5 class="text-white font-weight-bold mb-0">Metonia Enterprise Limited</h5>
                    <div class="text-white-50 font-size-sm">Nairobi Assembly Plant #1 Operations Floor</div>
                </div>

                <!-- Login Card -->
                <div class="card shadow-lg mb-3 border-0" style="border-radius: 8px;">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <h6 class="font-weight-bold text-dark text-uppercase mb-1">
                                <i class="icon-lock text-success mr-1"></i> Authorized Staff Sign In
                            </h6>
                            <span class="text-muted font-size-sm">Enter your Plant ID / Username or Email</span>
                        </div>

                        @include('partials.flash_message')

                        <form action="{{ route('login.post') }}" method="POST">
                            @csrf

                            <div class="form-group form-group-feedback form-group-feedback-left">
                                <input type="text" name="identifier" class="form-control" placeholder="Username or email (e.g. admin)" value="{{ old('identifier', 'admin') }}" required autofocus>
                                <div class="form-control-feedback">
                                    <i class="icon-user text-muted"></i>
                                </div>
                            </div>

                            <div class="form-group form-group-feedback form-group-feedback-left form-group-feedback-right">
                                <input type="password" name="password" id="login-password" class="form-control" placeholder="Account Password" value="password" required>
                                <div class="form-control-feedback">
                                    <i class="icon-lock2 text-muted"></i>
                                </div>
                                <div class="form-control-feedback" style="pointer-events: auto; cursor: pointer;" onclick="togglePasswordVisibility('login-password', this)">
                                    <i class="icon-eye-blocked text-muted"></i>
                                </div>
                            </div>

                            <div class="form-group d-flex align-items-center justify-content-between mb-3">
                                <div class="form-check mb-0">
                                    <label class="form-check-label font-size-sm text-muted">
                                        <input type="checkbox" name="remember" value="1" class="form-check-input" checked> Remember session
                                    </label>
                                </div>
                                <a href="{{ route('password.request') }}" class="font-size-xs">Forgot password?</a>
                            </div>

                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2">
                                    <i class="icon-enter2 mr-1"></i> Authenticate &amp; Proceed
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Quick Demo Credentials Box -->
                    <div class="card-footer bg-light border-top p-3 font-size-xs">
                        <div class="font-weight-bold text-muted text-uppercase mb-1">Demo Plant Accounts (Password: <code>password</code>):</div>
                        <div class="d-flex flex-wrap" style="gap: 4px;">
                            <button type="button" class="btn btn-outline-dark btn-xs font-weight-semibold" onclick="setCreds('admin')">Admin</button>
                            <button type="button" class="btn btn-outline-success btn-xs font-weight-semibold" onclick="setCreds('manager')">Manager</button>
                            <button type="button" class="btn btn-outline-warning btn-xs font-weight-semibold" onclick="setCreds('shopkeeper')">Shopkeeper</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs font-weight-semibold" onclick="setCreds('accountant')">Accountant</button>
                        </div>
                    </div>
                </div>

                <div class="text-center text-white-50 font-size-xs">
                    Protected by Metonia Systems Internal Security Protocols &copy; {{ date('Y') }}
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('global_assets/js/main/jquery.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/main/bootstrap.bundle.min.js') }}"></script>
    <script>
        function setCreds(user) {
            document.querySelector('input[name="identifier"]').value = user;
            document.querySelector('input[name="password"]').value = 'password';
        }

        function togglePasswordVisibility(inputId, toggleEl) {
            const input = document.getElementById(inputId);
            const icon = toggleEl.querySelector('i');
            const showing = input.type === 'text';

            input.type = showing ? 'password' : 'text';
            icon.classList.toggle('icon-eye-blocked', showing);
            icon.classList.toggle('icon-eye', !showing);
        }
    </script>
</body>
</html>
