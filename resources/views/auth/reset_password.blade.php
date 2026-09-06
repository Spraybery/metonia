<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reset Password | {{ Qs::getSystemName() }}</title>

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

                <!-- Reset Password Card -->
                <div class="card shadow-lg mb-3 border-0" style="border-radius: 8px;">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <h6 class="font-weight-bold text-dark text-uppercase mb-1">
                                <i class="icon-lock text-success mr-1"></i> Set a New Password
                            </h6>
                            <span class="text-muted font-size-sm">Choose a new password for your account</span>
                        </div>

                        @include('partials.flash_message')

                        <form action="{{ route('password.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-group form-group-feedback form-group-feedback-left">
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="you@metonia.co.ke" value="{{ old('email', $email) }}" required autofocus>
                                <div class="form-control-feedback">
                                    <i class="icon-mail5 text-muted"></i>
                                </div>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group form-group-feedback form-group-feedback-left form-group-feedback-right">
                                <input type="password" name="password" id="reset-password" class="form-control @error('password') is-invalid @enderror" placeholder="New Password" required minlength="8">
                                <div class="form-control-feedback">
                                    <i class="icon-lock2 text-muted"></i>
                                </div>
                                <div class="form-control-feedback" style="pointer-events: auto; cursor: pointer;" onclick="togglePasswordVisibility('reset-password', this)">
                                    <i class="icon-eye-blocked text-muted"></i>
                                </div>
                                @error('password')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <span class="font-size-xs text-muted">Min 8 characters, upper &amp; lowercase letters, and a number.</span>
                            </div>

                            <div class="form-group form-group-feedback form-group-feedback-left form-group-feedback-right mb-3">
                                <input type="password" name="password_confirmation" id="reset-password-confirm" class="form-control" placeholder="Confirm New Password" required minlength="8">
                                <div class="form-control-feedback">
                                    <i class="icon-lock2 text-muted"></i>
                                </div>
                                <div class="form-control-feedback" style="pointer-events: auto; cursor: pointer;" onclick="togglePasswordVisibility('reset-password-confirm', this)">
                                    <i class="icon-eye-blocked text-muted"></i>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2">
                                    <i class="icon-checkmark mr-1"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer bg-light border-top p-3 text-center">
                        <a href="{{ route('login') }}" class="font-size-sm">
                            <i class="icon-arrow-left7 mr-1"></i> Back to Sign In
                        </a>
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
