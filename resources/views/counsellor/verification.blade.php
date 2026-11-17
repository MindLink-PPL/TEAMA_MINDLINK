
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Status - MindLink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6">

<div class="bg-white max-w-lg w-full rounded-xl shadow-md p-8 text-center">

    <h1 class="text-3xl font-bold text-blue-600 mb-3">
        MindLink
    </h1>

    @if ($user->verification_status === 'pending')

        <div class="text-yellow-500 text-5xl mb-4">◷</div>

        <h2 class="text-2xl font-bold text-gray-800">
            Application Under Review
        </h2>

        <p class="text-gray-500 mt-3">
            Your certificate has been submitted successfully.
            Our administrator will review your application.
        </p>

        <div class="bg-yellow-50 text-yellow-700 rounded-lg p-4 mt-6">
            Status: Pending Verification
        </div>

    @elseif ($user->verification_status === 'rejected')

        <div class="text-red-500 text-5xl mb-4">✕</div>

        <h2 class="text-2xl font-bold text-gray-800">
            Application Rejected
        </h2>

        <p class="text-gray-500 mt-3">
            Unfortunately, your application was not approved.
        </p>

        <div class="bg-red-50 text-red-700 rounded-lg p-4 mt-6 text-left">
            <strong>Reason for rejection:</strong>

            <p class="mt-2">
                {{ $user->rejection_reason ?? 'No reason provided.' }}
            </p>
        </div>

    @elseif ($user->verification_status === 'verified')

        <div class="text-green-500 text-5xl mb-4">✓</div>

        <h2 class="text-2xl font-bold text-gray-800">
            Congratulations!
        </h2>

        <p class="text-gray-500 mt-3">
            Your counsellor account has been verified.
            You can now access your dashboard.
        </p>

        <a href="{{ route('counsellor.dashboard') }}"
           class="block bg-blue-600 text-white rounded-lg py-3 mt-6">
            Go to Dashboard
        </a>

    @else

        <h2 class="text-xl font-bold">Verification Not Submitted</h2>

        <p class="text-gray-500 mt-3">
            Please contact the administrator for assistance.
        </p>

    @endif

    <form action="{{ route('logout') }}" method="POST" class="mt-6">
        @csrf

        <button type="submit" class="text-gray-500 hover:text-red-600">
            Logout
        </button>
    </form>

</div>

</body>
</html>
