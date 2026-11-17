
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counsellor Registration - MindLink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center py-10">

<div class="w-full max-w-lg bg-white p-8 rounded-xl shadow-md">

    <h1 class="text-3xl font-bold text-center text-blue-600">
        MindLink
    </h1>

    <h2 class="text-xl font-semibold text-center mt-3">
        Counsellor Registration
    </h2>

    <p class="text-gray-500 text-center text-sm mt-2 mb-6">
        Register as a counsellor and submit your professional certificate.
    </p>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('counsellor.register.submit') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf

        <div class="mb-4">
            <label class="block font-medium mb-1">Full Name</label>
            <input type="text" name="name"
                   value="{{ old('name') }}"
                   required
                   class="w-full border rounded-lg p-3">
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Email</label>
            <input type="email" name="email"
                   value="{{ old('email') }}"
                   required
                   class="w-full border rounded-lg p-3">
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Password</label>
            <input type="password" name="password"
                   required
                   class="w-full border rounded-lg p-3">
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">
                Confirm Password
            </label>
            <input type="password" name="password_confirmation"
                   required
                   class="w-full border rounded-lg p-3">
        </div>

        <div class="mb-6">
            <label class="block font-medium mb-1">
                Professional Certificate
            </label>

            <input type="file" name="certificate"
                   accept=".pdf,.jpg,.jpeg,.png"
                   required
                   class="w-full border rounded-lg p-3">

            <p class="text-xs text-gray-500 mt-2">
                Accepted formats: PDF, JPG, PNG. Maximum size: 2 MB.
            </p>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">
            Submit Registration
        </button>

    </form>

    <p class="text-center text-sm text-gray-500 mt-5">
        Already registered?
        <a href="{{ route('login') }}" class="text-blue-600">
            Login here
        </a>
    </p>

</div>

</body>
</html>
