<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spikster — Setup</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #0b0d14;
            color: #e2e8f0;
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .wrapper {
            width: 100%;
            max-width: 460px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-bottom: 2rem;
        }

        .brand-icon {
            width: 2.25rem;
            height: 2.25rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .brand-name {
            font-size: 1.375rem;
            font-weight: 700;
            color: #f8fafc;
            letter-spacing: -0.02em;
        }

        .card {
            background: #131624;
            border: 1px solid #1e2438;
            border-radius: 16px;
            padding: 2.25rem;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.03);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #f1f5f9;
            letter-spacing: -0.02em;
            margin-bottom: 0.375rem;
        }

        .card-subtitle {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 1.75rem;
        }

        .form-group { margin-bottom: 1.125rem; }

        label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #94a3b8;
            margin-bottom: 0.375rem;
        }

        input {
            width: 100%;
            padding: 0.6875rem 0.875rem;
            background: #0b0d14;
            border: 1.5px solid #1e2438;
            border-radius: 10px;
            color: #f1f5f9;
            font-size: 0.9375rem;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
            line-height: 1.5;
        }

        input::placeholder { color: #334155; }

        input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
        }

        .field-error {
            font-size: 0.78rem;
            color: #f87171;
            margin-top: 0.375rem;
        }

        .error-alert {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 10px;
            padding: 0.875rem 1rem;
            margin-bottom: 1.5rem;
        }

        .error-alert p {
            font-size: 0.8125rem;
            color: #fca5a5;
            line-height: 1.5;
        }

        .error-alert p + p { margin-top: 0.25rem; }

        .divider {
            border: none;
            border-top: 1px solid #1e2438;
            margin: 1.5rem 0;
        }

        button[type="submit"] {
            width: 100%;
            padding: 0.8125rem 1.5rem;
            background: linear-gradient(135deg, #6366f1, #5b21b6);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.15s, transform 0.1s;
            letter-spacing: -0.01em;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
            margin-top: 0.5rem;
        }

        button[type="submit"]:hover { opacity: 0.9; transform: translateY(-1px); }
        button[type="submit"]:active { transform: translateY(0); opacity: 1; }

        .hint {
            font-size: 0.75rem;
            color: #475569;
            margin-top: 0.375rem;
        }

        .footer-note {
            text-align: center;
            font-size: 0.75rem;
            color: #334155;
            margin-top: 1.75rem;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="brand">
            <div class="brand-icon">⚡</div>
            <span class="brand-name">Spikster</span>
        </div>

        <div class="card">
            <h1 class="card-title">Welcome! Let's get started.</h1>
            <p class="card-subtitle">Create your admin account to manage your servers and sites.</p>

            @if ($errors->any())
                <div class="error-alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('setup.complete', $token) }}">
                @csrf

                <div class="form-group">
                    <label for="name">Your name</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        placeholder="John Doe"
                        required
                        autofocus
                        class="{{ $errors->has('name') ? 'error' : '' }}"
                    >
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        required
                        class="{{ $errors->has('email') ? 'error' : '' }}"
                    >
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <hr class="divider">

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Min. 8 characters"
                        required
                        class="{{ $errors->has('password') ? 'error' : '' }}"
                    >
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @else
                        <p class="hint">At least 8 characters</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        placeholder="Repeat password"
                        required
                        class="{{ $errors->has('password_confirmation') ? 'error' : '' }}"
                    >
                    @error('password_confirmation') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit">Create account &amp; open panel →</button>
            </form>
        </div>

        <p class="footer-note">Spikster &mdash; VPS &amp; Server Management Panel</p>
    </div>
</body>
</html>
