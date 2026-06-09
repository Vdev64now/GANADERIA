<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>GanadoFlow | Iniciar Sesión</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--bg-main);
            overflow: hidden;
            position: relative;
        }

        /* Ambient Glowing Background Blobs */
        .glow-blob {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            z-index: 1;
            pointer-events: none;
        }

        .glow-blob-primary {
            background-color: var(--primary);
            top: -100px;
            left: -100px;
            animation: float-slow 15s infinite alternate;
        }

        .glow-blob-info {
            background-color: var(--info);
            bottom: -100px;
            right: -100px;
            animation: float-slow 20s infinite alternate-reverse;
        }

        @keyframes float-slow {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 30px) scale(1.1); }
            100% { transform: translate(-20px, -50px) scale(0.9); }
        }

        /* Glassmorphism Card */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: rgba(17, 24, 39, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 40px 30px;
            box-shadow: var(--shadow-lg), 0 0 40px rgba(16, 185, 129, 0.03);
            text-align: center;
            transition: var(--transition);
        }

        .login-card:hover {
            border-color: rgba(16, 185, 129, 0.2);
            box-shadow: var(--shadow-lg), 0 0 50px rgba(16, 185, 129, 0.06);
        }

        /* Brand & Logo */
        .login-logo {
            font-size: 44px;
            margin-bottom: 15px;
            display: inline-block;
            filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.3));
            animation: pulse-glow 3s infinite alternate;
        }

        @keyframes pulse-glow {
            0% { transform: scale(1); filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.3)); }
            100% { transform: scale(1.05); filter: drop-shadow(0 0 20px rgba(6, 182, 212, 0.5)); }
        }

        .login-brand-name {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff, #9ca3af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .login-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 35px;
        }

        /* Form Controls */
        .form-group-login {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label-login {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-control-login {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            outline: none;
            font-family: inherit;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control-login:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
            background-color: rgba(255, 255, 255, 0.06);
        }

        /* Remember Checkbox */
        .remember-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            cursor: pointer;
            user-select: none;
        }

        .remember-checkbox {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .remember-label {
            font-size: 13px;
            color: var(--text-secondary);
            margin: 0;
            cursor: pointer;
        }

        /* Error Banner */
        .login-error-alert {
            background-color: var(--danger-light);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            text-align: left;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .login-error-alert span {
            font-size: 16px;
            line-height: 1;
        }

        /* Success Toast/Alert */
        .login-success-alert {
            background-color: var(--primary-light);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #a7f3d0;
            padding: 12px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            text-align: left;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
    </style>
</head>
<body>

    <!-- Background Ambience -->
    <div class="glow-blob glow-blob-primary"></div>
    <div class="glow-blob glow-blob-info"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <!-- Brand -->
            <div class="login-logo">🐂</div>
            <h1 class="login-brand-name">GanadoFlow</h1>
            <p class="login-subtitle">Gestión Ganadera y de Venta</p>

            <!-- Feedback Success -->
            @if(session('success'))
                <div class="login-success-alert">
                    <span>✅</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <!-- Global Validation Errors -->
            @if($errors->any())
                <div class="login-error-alert">
                    <span>⚠️</span>
                    <div>
                        <ul style="margin: 0; padding-left: 14px; list-style-type: disc;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="form-group-login">
                    <label for="email" class="form-label-login">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control-login" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group-login">
                    <label for="password" class="form-label-login">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control-login" placeholder="••••••••" required>
                </div>

                <label class="remember-container" for="remember">
                    <input type="checkbox" name="remember" id="remember" class="remember-checkbox" {{ old('remember') ? 'checked' : '' }}>
                    <span class="remember-label">Recordar mi sesión</span>
                </label>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px 20px; font-size: 15px;">
                    Iniciar Sesión
                </button>
            </form>
        </div>
    </div>

</body>
</html>
