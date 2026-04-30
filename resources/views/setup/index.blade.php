<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spikster Setup</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0f1117;
            color: #e2e8f0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #1a1d2e;
            border: 1px solid #2d3748;
            border-radius: 12px;
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #6366f1;
            margin-bottom: 0.5rem;
        }
        h1 { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem; }
        p.sub { color: #718096; font-size: 0.875rem; margin-bottom: 2rem; }
        label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.375rem; color: #a0aec0; }
        input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            background: #0f1117;
            border: 1px solid #2d3748;
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 0.9rem;
            margin-bottom: 1.25rem;
            transition: border-color 0.2s;
        }
        input:focus { outline: none; border-color: #6366f1; }
        button {
            width: 100%;
            padding: 0.75rem;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #4f46e5; }
        .error { color: #fc8181; font-size: 0.8rem; margin-top: -1rem; margin-bottom: 1rem; }
        .alert { background: #2d1f1f; border: 1px solid #742a2a; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.875rem; color: #fc8181; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">⚡ Spikster</div>
        <h1>Welcome! Let's set up your panel.</h1>
        <p class="sub">Create your admin account to get started.</p>

        @if ($errors->any())
            <div class="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('setup.complete', $token) }}">
            @csrf
            <label>Your name</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required autofocus>

            <label>Email address</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Min. 8 characters" required>

            <label>Confirm password</label>
            <input type="password" name="password_confirmation" placeholder="Repeat password" required>

            <button type="submit">Create account &amp; open panel →</button>
        </form>
    </div>
</body>
</html>
