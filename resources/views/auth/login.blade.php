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

                        <form action="{{ route('login.post') }}" method="POST" autocomplete="off">
                            @csrf
                            <!-- Dummy inputs to prevent browser password managers from auto-filling saved credentials -->
                            <input type="text" name="fake_username_remembered" style="display:none;" tabindex="-1" aria-hidden="true" autocomplete="off">
                            <input type="password" name="fake_password_remembered" style="display:none;" tabindex="-1" aria-hidden="true" autocomplete="new-password">

                            <div class="form-group form-group-feedback form-group-feedback-left">
                                <input type="text" name="identifier" id="login-identifier" class="form-control" placeholder="Username or email (e.g. admin)" value="" required autofocus autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" oninput="this.dataset.userInteracted='true';">
                                <div class="form-control-feedback">
                                    <i class="icon-user text-muted"></i>
                                </div>
                            </div>

                            <div class="form-group form-group-feedback form-group-feedback-left position-relative">
                                <input type="password" name="password" id="login-password" class="form-control pr-5" placeholder="Account Password" value="" required autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" oninput="this.dataset.userInteracted='true';">
                                <div class="form-control-feedback">
                                    <i class="icon-lock2 text-muted"></i>
                                </div>
                                <button type="button" class="btn btn-sm btn-light border-0 position-absolute" style="right: 6px; top: 50%; transform: translateY(-50%); z-index: 5; background: transparent; cursor: pointer; font-size: 15px;" onclick="togglePasswordVisibility('login-password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                                    👁️
                                </button>
                            </div>

                            <div class="form-group d-flex align-items-center justify-content-between mb-3">
                                <div class="form-check mb-0">
                                    <label class="form-check-label font-size-sm text-muted">
                                        <input type="checkbox" name="remember" value="1" class="form-check-input"> Remember session
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
            const idInput = document.getElementById('login-identifier');
            const passInput = document.getElementById('login-password');
            if (idInput) { idInput.removeAttribute('readonly'); idInput.value = user; idInput.dataset.userInteracted = 'true'; }
            if (passInput) { passInput.removeAttribute('readonly'); passInput.value = 'password'; passInput.dataset.userInteracted = 'true'; }
        }

        function forceClearLoginInputs() {
            const idInput = document.getElementById('login-identifier');
            const passInput = document.getElementById('login-password');
            if (idInput && !idInput.dataset.userInteracted) {
                idInput.value = '';
            }
            if (passInput && !passInput.dataset.userInteracted) {
                passInput.value = '';
            }
        }

        window.addEventListener('DOMContentLoaded', function() {
            forceClearLoginInputs();
            setTimeout(forceClearLoginInputs, 50);
            setTimeout(forceClearLoginInputs, 200);
            setTimeout(forceClearLoginInputs, 500);
        });
        window.addEventListener('pageshow', function(e) {
            forceClearLoginInputs();
        });

        function togglePasswordVisibility(inputId, toggleEl) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            if (toggleEl) {
                const icon = toggleEl.querySelector('i');
                if (icon) {
                    icon.classList.toggle('icon-eye-blocked', showing);
                    icon.classList.toggle('icon-eye', !showing);
                } else {
                    toggleEl.style.opacity = showing ? '0.5' : '1';
                }
            }
        }
    </script>
</body>
</html>
