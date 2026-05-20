<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Printa Signages</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-black via-gray-900 to-black text-white">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="flex items-center justify-center mb-8">
                <div class="w-16 h-16 bg-yellow-500 rounded-xl flex items-center justify-center">
                    <i data-lucide="package" class="w-10 h-10 text-black"></i>
                </div>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2">Printa Signages</h1>
                <p class="text-gray-400">Management System</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.post') }}" class="bg-gray-800 rounded-2xl p-8 border border-gray-700">
                @csrf

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-500/10 border border-red-500 rounded-lg">
                        <p class="text-red-400 text-sm">{{ $errors->first() }}</p>
                    </div>
                @endif

                <!-- Email Field -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium mb-2">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" placeholder="your@email.com">
                    @error('email')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium mb-2">Password</label>
                    <input type="password" id="password" name="password" required class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" placeholder="••••••••">
                    @error('password')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 accent-yellow-500 cursor-pointer">
                        <span class="text-sm text-gray-300">Remember me for 7 days</span>
                    </label>
                </div>

                <!-- Login Button -->
                <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-3 rounded-lg transition-colors mb-4">
                    Sign In
                </button>

                <!-- Demo Credentials -->
                <div class="p-4 bg-gray-700 rounded-lg text-sm">
                    <p class="text-gray-300 mb-2"><span class="font-semibold">Demo Admin:</span></p>
                    <p class="text-gray-400 mb-3">Email: <code class="bg-gray-800 px-2 py-1 rounded">admin@printa.local</code></p>
                    <p class="text-gray-400 mb-3">Password: <code class="bg-gray-800 px-2 py-1 rounded">password</code></p>
                    <hr class="border-gray-600 my-3">
                    <p class="text-gray-300 mb-2"><span class="font-semibold">Demo Staff:</span></p>
                    <p class="text-gray-400 mb-3">Email: <code class="bg-gray-800 px-2 py-1 rounded">staff@printa.local</code></p>
                    <p class="text-gray-400">Password: <code class="bg-gray-800 px-2 py-1 rounded">password</code></p>
                </div>
            </form>

            <!-- Footer -->
            <p class="text-center text-gray-500 text-sm mt-8">
                © {{ date('Y') }} Printa Signages. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
