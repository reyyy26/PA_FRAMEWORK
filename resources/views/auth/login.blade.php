<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Nyxx Agrisupply</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        body {
            min-height: 100vh;
            background: radial-gradient(circle at top left, rgba(37,99,235,0.45), transparent 55%),
                        radial-gradient(circle at bottom right, rgba(14,165,233,0.45), transparent 55%),
                        #f8fafc;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.82);
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 20px 60px -30px rgba(37, 99, 235, 0.35);
            backdrop-filter: blur(18px);
        }
    </style>
</head>
<body class="flex items-center justify-center px-4 py-12">
<div class="w-full max-w-md glass-panel p-10 space-y-8">
    <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-600 text-white text-2xl font-semibold">NA</div>
        <h1 class="text-2xl font-semibold text-gray-800">Masuk ke Nyxx Agrisupply</h1>
        <p class="text-sm text-gray-500">Gunakan kredensial yang diberikan tim keamanan.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg px-4 py-3 space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf
        <div class="space-y-2">
            <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="you@example.com">
        </div>
        <div class="space-y-2">
            <label for="password" class="text-sm font-semibold text-gray-700">Password</label>
            <input id="password" type="password" name="password" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="••••••••">
        </div>
        <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold shadow hover:bg-blue-700 transition">Masuk</button>
    </form>

    <p class="text-xs text-center text-gray-400">Nyxx Agrisupply &copy; {{ date('Y') }} · Keamanan internal</p>
</div>
</body>
</html>
