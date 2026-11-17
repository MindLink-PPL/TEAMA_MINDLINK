<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MindLink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">

    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-md">

        <h1 class="text-3xl font-bold text-center text-blue-600">
            MindLink
        </h1>

        <h2 class="text-xl font-semibold text-center mt-4">
            Welcome Back
        </h2>

        <p class="text-gray-500 text-center mt-2">
            Login to your account
        </p>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mt-4">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="mt-6">
            @csrf

            <div class="mb-4">
    <label class="block font-medium mb-2">Login as</label>

    <select name="role"
        class="w-full border rounded-lg p-3"
        required>

        <option value="admin">Admin</option>

        <option value="student"
            {{ old('role') == 'student' ? 'selected' : '' }}>
            Student
        </option>

        <option value="counsellor"
            {{ old('role') == 'counsellor' ? 'selected' : '' }}>
            Counsellor
        </option>

    </select>
</div>

            <div class="mb-4">
                <label class="block mb-2 font-medium">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full border rounded-lg p-3"
                    placeholder="Enter your email"
                >
            </div>

            <div class="mb-4">
                <label class="block mb-2 font-medium">Password</label>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full border rounded-lg p-3"
                    placeholder="Enter your password"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700"
            >
                Login
            </button>
        </form>

        <p class="text-center mt-6 text-gray-600">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-blue-600 font-semibold">
                Register
            </a>
        </p>

    </div>

</body>
</html>
