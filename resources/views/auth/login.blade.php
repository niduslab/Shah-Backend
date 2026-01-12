<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>NidusCart Login</title>
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="description" content="" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta property="og:title" content="" />
        <meta property="og:type" content="" />
        <meta property="og:url" content="" />
        <meta property="og:image" content="" />
        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/imgs/icons/nidus_icon_2.png')}}" />
        <!-- Template CSS -->
        <link href="{{asset('assets/css/main.css?v=1.1')}}" rel="stylesheet" type="text/css" />
        <style>
            /* .cus-img{
                border: 1px solid #eee;
                border-radius: 30px;
            } */

            body, html {
    font-family: 'Roboto', sans-serif;
    background-color: #f8f9fa;
    color: #333;
}

.card-login {
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.cus-img {
    border-radius: 8px;
    margin-bottom: 20px;
}

.form-control {
    border-radius: 6px;
    padding: 10px 15px;
    border: 1px solid #ddd;
    transition: border-color 0.2s;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: none;
}

.btn-primary {
    background-color: #007bff;
    border: none;
    padding: 10px 15px;
    font-size: 16px;
    transition: background-color 0.3s;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.main-footer {
    font-size: 14px;
    color: #6c757d;
}

.alert {
    font-size: 14px;
}

        </style>
    </head>

    <body>
        <main class="login-main">
            <section class="content-main d-flex justify-content-center align-items-center min-vh-100">
                <div class="row justify-content-center w-75 mx-0" style="border: 1px solid #eee; border-radius: 30px; background-color: #29A56C">
                    <div class="col-md-4 d-none d-md-block text-center">
                        <img src="{{ asset('assets/imgs/logos/login.png') }}" class="cus-img" style="height: 450px;" alt="Welcome Image">
                    </div>
                    <div class="col-md-4">
                        <div class=" mx-auto p-md-4 py-sm-4">
                            <div class="">
                                <div class="text-center my-4">
                                    <a href="{{ url('/') }}" class="brand-wrap">
                                        <img src="{{ asset('assets/imgs/logos/niduscart_logo_1.png') }}" class="logo mb-2" alt="NidusCart Logo">
                                    </a>
                                    <h2 class="font-weight-bold text-light">Welcome Back</h2>
                                    <p class="text-light">Please log in to your account</p>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger p-2 rounded-3">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger text-danger p-2 rounded-3">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('check.auth') }}" class="p-3 mt-3">
                                    @csrf
                                    <div class="mb-3">
                                        <input type="email" name="email" id="email" class="form-control rounded-3" placeholder="Email" value="{{ old('email') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="password" name="password" id="pwd" class="form-control rounded-3" placeholder="Password" required>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <input type="checkbox" class="form-check-input" checked>
                                            <label class="form-check-label text-light">Remember me</label>
                                        </div>
                                        <a href="{{ route('admin.password.request') }}" class="font-sm text-light">Forgot password?</a>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-3">Login</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <footer class="main-footer text-center mt-4">
                <p class="font-xs text-muted">
                    &copy; <script>document.write(new Date().getFullYear());</script> NidusCart - All rights reserved.
                </p>
            </footer>
        </main>

        <script src="{{asset('assets/js/vendors/jquery-3.6.0.min.js')}}"></script>
        <script src="{{asset('assets/js/vendors/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('assets/js/vendors/jquery.fullscreen.min.js')}}"></script>
        <!-- Main Script -->
        <script src="{{asset('assets/js/main.js?v=1.1" type="text/javascript')}}"></script>
    </body>
</html>

