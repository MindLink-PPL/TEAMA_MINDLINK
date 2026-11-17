<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MindLink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">

    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-md">

        <h1 class="text-3xl font-bold text-center text-blue-600">
            MindLink
        </h1>

        <h2 class="text-xl font-semibold text-center mt-4">
            Create an Account
        </h2>

        <p class="text-gray-500 text-center mt-2">
            Register as a student
        </p>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mt-4">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" class="mt-6">
            @csrf

            <div class="mb-4">
                <label class="block mb-2 font-medium">Full Name</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="w-full border rounded-lg p-3"
                    placeholder="Enter your name"
                >
            </div>

            <div class="mb-4">
                <label class="block mb-2 font-medium">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
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
                    minlength="8"
                    class="w-full border rounded-lg p-3"
                    placeholder="Minimum 8 characters"
                >
            </div>

            <div class="mb-4">
                <label class="block mb-2 font-medium">Confirm Password</label>
                <input
                    type="password"
                    name="password_confirmation"
                    required
                    class="w-full border rounded-lg p-3"
                    placeholder="Confirm your password"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700"
            >
                Register
            </button>
        </form>

        <p class="text-center mt-6 text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="text-blue-600 font-semibold">
                Login
            </a>
        </p>


<div class="text-center mt-5">

    <p class="text-sm text-gray-500">
        Registering as a counsellor?
        <a href="{{ route('counsellor.register') }}"
           class="text-blue-600 font-semibold hover:text-blue-800">
            Apply here
        </a>
    </p>

</div>


    </div>

</body>
</html>
