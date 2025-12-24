<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | NextLoad</title>
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all .3s cubic-bezier(.4,0,.2,1);
            border: none;
        }
        .auth-header {
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff;
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
            width: 48px; height: 48px; background: #fff; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; margin-right: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
        }
        .logo-icon i { color: var(--primary-color); font-size: 1.5rem; }
        .logo-text { font-size: 1.8rem; font-weight: 700; letter-spacing: -0.5px; }
        .logo-slogan { font-size: .9rem; opacity: .9; margin-top: 4px; font-weight: 400; }

        .auth-body { padding: 2.5rem; }
        .auth-title { font-weight: 700; margin-bottom: 1rem; color: var(--text-dark); font-size: 1.5rem; }
        .auth-subtitle { color: var(--text-light); font-size: .95rem; margin-bottom: 1.5rem; }

        .form-control {
            padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0; transition: all .3s ease;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99,102,241,.2);
        }
        .form-label { font-weight: 500; margin-bottom: 8px; color: var(--text-dark); }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none; padding: 12px; border-radius: 8px; font-weight: 600; letter-spacing: .5px;
            transition: all .3s ease;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(99,102,241,.4); }

        .auth-footer { text-align: center; margin-top: 1.25rem; color: var(--text-light); font-size: .9rem; }
        .auth-footer a { color: var(--primary-color); text-decoration: none; font-weight: 500; }

        a { transition: color .2s ease; }
        a:hover { color: var(--primary-dark); }

        @media (max-width: 768px) {
            .auth-container { padding: 1rem; }
            .auth-body { padding: 1.5rem; }
        }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">

            <div class="col-md-8 col-lg-6">

                {{-- Validation alerts --}}
                @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="auth-card mt-3">
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
                        <h1 class="auth-title">Reset Password</h1>
                        <p class="auth-subtitle">Enter your email and new password below to reset your account access.</p>

                        <form method="POST" action="{{ route('password.store') }}">
                            @csrf

                            <!-- Token de redefinição -->
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $request->email) }}"
                                    required autofocus autocomplete="username"
                                    placeholder="Enter your email"
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required autocomplete="new-password"
                                    placeholder="Enter new password"
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    required autocomplete="new-password"
                                    placeholder="Confirm new password"
                                >
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 mb-2">
                                Reset Password
                            </button>

                            <div class="auth-footer">
                                <a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left-long me-1"></i> Back to login</a>
                            </div>
                        </form>
                    </div>
                </div><!-- /auth-card -->

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
