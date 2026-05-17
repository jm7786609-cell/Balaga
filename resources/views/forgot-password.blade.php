<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pharmacy System | Forgot Password</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">

    <style>
        body.login-page {
            overflow: hidden;
            background: linear-gradient(135deg, #d9f7ed, #bff3db);
            position: relative;
        }

        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(43, 191, 130, 0.15);
            animation: floatUp 12s infinite ease-in-out;
            backdrop-filter: blur(3px);
        }

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

        .glass-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.35);
            max-width: 400px;
            margin: auto;
            margin-top: 15vh;
        }

        .login-logo a {
            color: #2bbf82 !important;
            font-weight: 700;
            font-size: 30px;
        }

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

        .alert {
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="login-page">
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>

    <div class="login-box">
        <div class="login-logo mb-4">
            <a href="#"><i class="fas fa-prescription-bottle-alt mr-1"></i> <b>Pharmacy</b> System</a>
        </div>

        <div class="glass-card">
            <p class="login-box-msg">Enter your email to reset your password</p>

            <form id="forgotPasswordForm">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block" id="submitBtn">Send Reset Link</button>
            </form>
        </div>
    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#forgotPasswordForm').on('submit', function(e) {
                e.preventDefault();
                $('.alert').remove();

                $('#submitBtn').attr('disabled', true).text('Sending...');

                $.ajax({
                    url: "{{ route('password.email') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        $('<div class="alert alert-success text-center mb-3">' + response
                                .status + '</div>')
                            .prependTo('.glass-card');
                        $('#submitBtn').attr('disabled', false).text('Send Reset Link');
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorHtml = '<div class="alert alert-danger"><ul>';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul></div>';
                        $('.glass-card').prepend(errorHtml);
                        $('#submitBtn').attr('disabled', false).text('Send Reset Link');
                    }
                });
            });
        });
    </script>
</body>

</html>
