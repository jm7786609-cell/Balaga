<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pharmacy System | Login</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">

    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">

    <style>
        /* Animated pharmacy background */
        body.login-page {
            overflow: hidden;
            background: linear-gradient(135deg, #d9f7ed, #bff3db);
            background-size: cover;
            position: relative;
        }

        /* Floating medical bubbles */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(43, 191, 130, 0.15);
            animation: floatUp 12s infinite ease-in-out;
            backdrop-filter: blur(3px);
        }

        /* Different bubble sizes */
        .bubble:nth-child(1) {
            width: 120px;
            height: 120px;
            left: 10%;
            animation-duration: 14s;
        }

        .bubble:nth-child(2) {
            width: 80px;
            height: 80px;
            left: 70%;
            animation-duration: 18s;
        }

        .bubble:nth-child(3) {
            width: 150px;
            height: 150px;
            left: 40%;
            animation-duration: 16s;
        }

        .bubble:nth-child(4) {
            width: 60px;
            height: 60px;
            left: 85%;
            animation-duration: 12s;
        }

        .bubble:nth-child(5) {
            width: 100px;
            height: 100px;
            left: 20%;
            animation-duration: 20s;
        }

        @keyframes floatUp {
            0% {
                bottom: -200px;
                transform: translateX(0);
                opacity: 0.3;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                bottom: 110%;
                transform: translateX(30px);
                opacity: 0;
            }
        }

        /* Glassmorphism card */
        .glass-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .login-logo a {
            color: #2bbf82 !important;
            font-weight: 700;
            font-size: 30px;
            letter-spacing: 0.4px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
        }

        /* Inputs */
        .form-control {
            border-radius: 10px !important;
            border-color: #d5e9e1;
            height: 45px;
        }

        .form-control:focus {
            border-color: #2bbf82;
            box-shadow: 0 0 0 0.2rem rgba(43, 191, 130, 0.25);
        }

        .input-group-text {
            background-color: #2bbf82;
            color: #fff !important;
            border: none;
            border-radius: 0 10px 10px 0 !important;
        }

        /* Buttons */
        .btn-primary {
            background-color: #2bbf82 !important;
            border-radius: 10px;
            border-color: #28a776 !important;
            height: 45px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #28a776 !important;
        }

        a {
            color: #28a776;
        }

        a:hover {
            color: #1e7b59;
        }

        .login-box-msg {
            font-size: 15px;
            color: #234c39;
        }
    </style>
</head>

<body class="hold-transition login-page">

    <!-- Animated bubbles -->
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>

    <div class="login-box">
        <div class="login-logo mb-4">
            <a href="#">
                <i class="fas fa-prescription-bottle-alt mr-1"></i>
                <b>Pharmacy</b> System
            </a>
        </div>

        <div class="glass-card">
            <p class="login-box-msg">Sign in to continue</p>

            <form action="#" method="post">
                <div class="input-group mb-3">
                    <input type="email" class="form-control" placeholder="Email">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" class="form-control" id="password" placeholder="Password">
                    <div class="input-group-append">
                        <span class="input-group-text" id="showPasswords"><i class="fas fa-lock"></i></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember"> Remember Me </label>
                        </div>
                    </div>

                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </div>
                </div>
            </form>

            <p class="mb-1 text-center">
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </p>
        </div>
    </div>

    <!-- JS -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <script>
        var showPass = false;
        $(document).ready(function() {
            $('#showPassword').click(function() {
                $('#showPassword').click(function() {
                    showPass = !showPass;
                    if (showPass) {
                        $(this).html('');
                        $(this).html('<i class="fas fa-eye-slash"></i>');
                        $('#password').attr('type', 'text');
                    } else {
                        $(this).html('');
                        $(this).html('<i class="fas fa-eye"></i>');
                        $('#password').attr('type', 'password');
                    }
                });
            });

            // Intercept form submission
            $('form').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submit

                // Get form values
                var email = $('input[type="email"]').val();
                var password = $('input[type="password"]').val();
                var remember = $('#remember').is(':checked') ? 1 : 0;

                // Clear previous messages
                $('.alert').remove();

                // Send AJAX request
                $.ajax({
                    url: "{{ route('login') }}",
                    method: "POST",
                    data: {
                        email: email,
                        password: password,
                        remember: remember,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Show success message
                        $('<div class="alert alert-success text-center mb-3">' + response
                                .message + '</div>')
                            .prependTo('.glass-card');

                        // Redirect after 1 second (optional)
                        setTimeout(function() {
                            window.location.href =
                                '/admin/dashboard'; // Change to your dashboard route
                        }, 1000);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            var errors = xhr.responseJSON.errors;
                            var errorHtml = '<div class="alert alert-danger"><ul>';
                            $.each(errors, function(key, value) {
                                errorHtml += '<li>' + value[0] + '</li>';
                            });
                            errorHtml += '</ul></div>';
                            $('.glass-card').prepend(errorHtml);
                        } else {
                            // Other errors
                            $('<div class="alert alert-danger text-center mb-3">Login failed. Please try again.</div>')
                                .prependTo('.glass-card');
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
