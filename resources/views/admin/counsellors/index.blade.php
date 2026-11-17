<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MindLink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

<div class="max-w-6xl mx-auto p-6">

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-blue-600">
                MindLink Admin
            </h1>

            <p class="text-gray-500 mt-1">
                Counsellor Verification Dashboard
            </p>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button class="bg-red-500 text-white px-4 py-2 rounded-lg">
                Logout
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-5">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-5">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">

        <h2 class="text-xl font-semibold mb-5">
            Counsellor Applications
        </h2>

        @forelse ($counsellors as $counsellor)

            <div class="border rounded-lg p-5 mb-5">

                <div class="flex justify-between items-start gap-3">

                    <div>
                        <h3 class="font-bold text-lg">
                            {{ $counsellor->name }}
                        </h3>

                        <p class="text-gray-500 text-sm">
                            {{ $counsellor->email }}
                        </p>
                    </div>

                    @if ($counsellor->verification_status === 'pending')
                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">
                            Pending
                        </span>

                    @elseif ($counsellor->verification_status === 'verified')
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                            Verified
                        </span>

                    @elseif ($counsellor->verification_status === 'rejected')
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">
                            Rejected
                        </span>
                    @endif

                </div>

                <div class="mt-4">

                    @if ($counsellor->certificate_path)

                        <a href="{{ route('admin.counsellors.certificate', $counsellor->id) }}"
                           class="text-blue-600 underline text-sm">
                            Download Certificate
                        </a>

                    @else

                        <p class="text-red-500 text-sm">
                            No certificate uploaded.
                        </p>

                    @endif

                </div>


@if ($counsellor->verification_status === 'pending')

    <div class="mt-5 flex flex-wrap items-center gap-4">

        {{-- APPROVE BUTTON --}}
        <form
            action="{{ route('admin.counsellors.approve', $counsellor->id) }}"
            method="POST"
        >
            @csrf

            <button
                type="submit"
                style="background-color: #15803d; color: white;"
                class="font-bold px-6 py-3 rounded-lg border border-green-800 shadow-md hover:opacity-90 cursor-pointer"
                onclick="return confirm('Are you sure you want to approve this counsellor?')"
            >
                ✓ Approve Counsellor
            </button>
        </form>

        {{-- REJECT FORM --}}
        <form
            action="{{ route('admin.counsellors.reject', $counsellor->id) }}"
            method="POST"
            class="flex flex-wrap items-center gap-2"
        >
            @csrf

            <input
                type="text"
                name="rejection_reason"
                required
                maxlength="1000"
                placeholder="Enter rejection reason"
                class="border border-gray-300 rounded-lg px-3 py-3"
            >

            <button
                type="submit"
                style="background-color: #dc2626; color: white;"
                class="font-bold px-6 py-3 rounded-lg border border-red-800 shadow-md hover:opacity-90 cursor-pointer"
                onclick="return confirm('Are you sure you want to reject this counsellor?')"
            >
                ✕ Reject
            </button>

        </form>

    </div>

@elseif ($counsellor->verification_status === 'rejected')

    <div class="bg-red-50 text-red-700 p-3 rounded-lg mt-4 text-sm">
        <strong>Rejection reason:</strong>
        {{ $counsellor->rejection_reason }}
    </div>

@elseif ($counsellor->verification_status === 'verified')

    <div class="bg-green-50 text-green-700 p-3 rounded-lg mt-4">
        ✓ This counsellor has been successfully verified.
    </div>

@endif

            </div>

        @empty

            <p class="text-gray-500 text-center py-8">
                No counsellor applications found.
            </p>

        @endforelse

    </div>

</div>

</body>
</html>
