<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Create Account | {{ Qs::getSystemName() }}</title>

    <link rel="icon" href="{{ Qs::getSystemLogo() }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{ asset('global_assets/css/icons/icomoon/styles.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/bootstrap_limitless.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/layout.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/components.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/colors.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/theme-green-white.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/theme-dark.css') }}" rel="stylesheet" type="text/css">

    <script>
        (function() {
            const savedTheme = localStorage.getItem('metonia_theme');
            const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.documentElement.classList.add('dark-mode');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                document.documentElement.classList.remove('dark-mode');
            }
        })();
    </script>
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #044e39 100%);">

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">

                <!-- Brand Card -->
                <div class="text-center mb-3 position-relative">
                    <div class="position-absolute" style="right: 0; top: 0;">
                        <button type="button" class="btn-theme-toggle" id="theme-toggle-btn" onclick="toggleAppTheme()" title="Toggle Dark / Light Mode" aria-label="Toggle Dark / Light Mode">
                            <i class="icon-contrast" id="theme-toggle-icon"></i>
                        </button>
                    </div>
                    <div class="d-inline-block bg-white p-2 rounded shadow-sm mb-2">
                        <img src="{{ Qs::getSystemLogo() }}" alt="Metonia" style="height: 38px;">
                    </div>
                    <h5 class="text-white font-weight-bold mb-0">Metonia Enterprise Limited</h5>
                    <div class="text-white-50 font-size-sm">Nairobi Assembly Plant #1 Operations Floor</div>
                </div>

                <!-- Sign Up Card -->
                <div class="card shadow-lg mb-3 border-0" style="border-radius: 8px;">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <h6 class="font-weight-bold text-dark text-uppercase mb-1">
                                <i class="icon-user-plus text-success mr-1"></i> Request Staff Account
                            </h6>
                            <span class="text-muted font-size-sm">First time here? Sign up to request plant floor access</span>
                        </div>

                        @include('partials.flash_message')

                        <div class="alert alert-info py-2 font-size-sm">
                            <i class="icon-info22 mr-1"></i> New account requests are reviewed by an administrator before you can sign in.
                        </div>

                        <form action="{{ route('signup.post') }}" method="POST" autocomplete="off">
                            @csrf

                            <div class="form-group form-group-feedback form-group-feedback-left">
                                <input type="text" name="name" class="form-control" placeholder="Full Name (e.g. Jane Wanjiru)" value="{{ old('name') }}" required autofocus>
                                <div class="form-control-feedback">
                                    <i class="icon-user text-muted"></i>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 form-group form-group-feedback form-group-feedback-left">
                                    <input type="text" name="username" class="form-control" placeholder="Username" value="{{ old('username') }}" required>
                                    <div class="form-control-feedback">
                                        <i class="icon-profile text-muted"></i>
                                    </div>
                                    <small class="form-text text-muted">Can match a teammate's in the same role — your email keeps your account unique.</small>
                                </div>
                                <div class="col-md-6 form-group form-group-feedback form-group-feedback-left">
                                    <input type="email" name="email" class="form-control" placeholder="Email Address" value="{{ old('email') }}" required>
                                    <div class="form-control-feedback">
                                        <i class="icon-envelope text-muted"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group form-group-feedback form-group-feedback-left">
                                <select name="role" class="form-control" required>
                                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select the role you're requesting...</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                                    @endforeach
                                </select>
                                <div class="form-control-feedback">
                                    <i class="icon-briefcase text-muted"></i>
                                </div>
                            </div>

                            <div class="form-group form-group-feedback form-group-feedback-left position-relative">
                                <input type="password" name="password" id="signup-password" class="form-control pr-5" placeholder="Password (min 8, upper &amp; lower case, a number)" required autocomplete="new-password">
                                <div class="form-control-feedback">
                                    <i class="icon-lock2 text-muted"></i>
                                </div>
                                <button type="button" class="btn btn-sm btn-light border-0 position-absolute" style="right: 6px; top: 50%; transform: translateY(-50%); z-index: 5; background: transparent; cursor: pointer; font-size: 15px;" onclick="togglePasswordVisibility('signup-password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                                    👁️
                                </button>
                            </div>

                            <div class="form-group form-group-feedback form-group-feedback-left">
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required autocomplete="new-password">
                                <div class="form-control-feedback">
                                    <i class="icon-lock2 text-muted"></i>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2">
                                    <i class="icon-paperplane mr-1"></i> Submit Account Request
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3 font-size-sm">
                            Already have an account? <a href="{{ route('login') }}" class="font-weight-semibold">Sign In</a>
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
        function updateThemeToggleUI(theme) {
            const btn = document.getElementById('theme-toggle-btn');
            const icon = document.getElementById('theme-toggle-icon');
            if (!icon) return;
            if (theme === 'dark') {
                icon.className = 'icon-sun3';
                if (btn) btn.title = 'Switch to Light Mode';
            } else {
                icon.className = 'icon-contrast';
                if (btn) btn.title = 'Switch to Dark Mode';
            }
        }

        function toggleAppTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            if (newTheme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark-mode');
            }

            localStorage.setItem('metonia_theme', newTheme);
            updateThemeToggleUI(newTheme);
        }

        window.addEventListener('DOMContentLoaded', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            updateThemeToggleUI(currentTheme);
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
