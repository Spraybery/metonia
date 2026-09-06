<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Forgot Password | {{ Qs::getSystemName() }}</title>

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

                <!-- Forgot Password Card -->
                <div class="card shadow-lg mb-3 border-0" style="border-radius: 8px;">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <h6 class="font-weight-bold text-dark text-uppercase mb-1">
                                <i class="icon-key text-success mr-1"></i> Reset Your Password
                            </h6>
                            <span class="text-muted font-size-sm">Enter your account email and we'll send you a reset link</span>
                        </div>

                        @include('partials.flash_message')

                        <form action="{{ route('password.email') }}" method="POST">
                            @csrf

                            <div class="form-group form-group-feedback form-group-feedback-left">
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="you@metonia.co.ke" value="{{ old('email') }}" required autofocus>
                                <div class="form-control-feedback">
                                    <i class="icon-mail5 text-muted"></i>
                                </div>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2">
                                    <i class="icon-paperplane mr-1"></i> Send Reset Link
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
</body>
</html>
