<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | NidusCart</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General Styles */
        body, html {
            font-family: 'Roboto', sans-serif;
            background-color: #f2f3f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Logo and Headings */
        .card-header, .card-body {
            text-align: start;
        }
        .brand-wrap img {
            width: 120px;
            margin-bottom: 15px;
        }
        .forgot-title {
            font-size: 24px;
            font-weight: 500;
            color: #333;
        }
        .forgot-subtitle {
            font-size: 14px;
            color: #777;
            /* margin-bottom: 20px; */
        }

        /* Form Styles */
        .form-group label {
            font-weight: 500;
            color: #333;
        }
        .form-control {
            border-radius: 4px;
            padding: 12px 14px;
            border: 1px solid #ddd;
            transition: border-color 0.2s ease;
        }
        .form-control:focus {
            border-color: #1a73e8;
            box-shadow: none;
            outline: none;
        }

        /* Button Styles */
        .btn-primary {
            background-color: #1a73e8;
            border: none;
            padding: 12px;
            font-size: 16px;
            width: 100%;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #1765c2;
        }

        /* Alerts */
        .alert {
            font-size: 14px;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card p-4">

                <div class="card-body">
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    <div>
                        <a href="{{ url('/') }}" class="brand-wrap">
                            <img src="{{ asset('assets/imgs/logos/niduscart_logo_1.png') }}" alt="NidusCart Logo">
                        </a>
                        <h1 class="forgot-title">Forgot Password?</h1>
                        <p class="forgot-subtitle">Enter your email address to receive a password reset link.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.password.email') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <button type="submit" class="btn w-100 btn-sm btn-outline-secondary">Send Reset Link</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
