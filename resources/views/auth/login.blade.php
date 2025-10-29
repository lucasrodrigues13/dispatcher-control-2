<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | NextLoad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #8b5cf6;
            --accent-color: #ec4899;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --light-bg: #f8fafc;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-dark);
        }

        .auth-container {
            display: flex;
            min-height: 100vh;
            align-items: center;
            margin: 20px 0;
        }

        .auth-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
        }

        .auth-header {
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
            position: relative;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logo-icon i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .logo-slogan {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 4px;
            font-weight: 400;
        }

        .auth-body {
            padding: 2.5rem;
        }

        .auth-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
            font-size: 1.5rem;
        }

        .form-control {
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider::before {
            margin-right: 1rem;
        }

        .divider::after {
            margin-left: 1rem;
        }

        .social-login {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .social-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid #e2e8f0;
            color: var(--text-light);
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            background: var(--light-bg);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Efeito de hover para links */
        a {
            transition: color 0.2s ease;
        }

        a:hover {
            color: var(--primary-dark);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .auth-container {
                padding: 1rem;
            }

            .auth-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">





                <div class="col-md-8 col-lg-6">

                    @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                    </div>
                @endif


                    <div class="auth-card">
                        <div class="auth-header">
                            <div class="logo-container">
                                <div class="logo-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <div class="logo-text">NextLoad</div>
                                    <div class="logo-slogan">Load tracking system, your the best choice</div>
                                </div>
                            </div>
                        </div>

                        <div class="auth-body">
                            <h1 class="auth-title">Login</h1>

                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" id="email" required autocomplete="email" placeholder="Enter your email">
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" id="password" required autocomplete="current-password" placeholder="Enter your password">
                                </div>

                                <div class="remember-forgot">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">
                                            Remember me
                                        </label>
                                    </div>
                                    <a href="#" class="text-decoration-none">Forgot password?</a>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3 mb-3">Sign In</button>

                                <!--
                                <div class="divider">or continue with</div>

                                <div class="social-login mb-4">
                                    <a href="#" class="social-btn"><i class="fab fa-google"></i></a>
                                    <a href="#" class="social-btn"><i class="fab fa-microsoft"></i></a>
                                    <a href="#" class="social-btn"><i class="fab fa-apple"></i></a>
                                </div> -->

                                <div class="auth-footer">
                                    Don't have an account? <a href="/register">Sign up</a>
                                </div>


                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simulação de erro de login (apenas para demonstração)
        document.querySelector('form').addEventListener('submit', function(e) {
            const errorDiv = document.getElementById('loginError');
            // Simulando um erro (remova isso em produção)
            if(Math.random() > 0.8) {
                e.preventDefault();
                errorDiv.style.display = 'block';
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                }, 3000);
            }
        });
    </script>
</body>
</html>
