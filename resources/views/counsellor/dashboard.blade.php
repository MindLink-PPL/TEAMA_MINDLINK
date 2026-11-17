
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MindLink - Counsellor Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

<div class="max-w-6xl mx-auto p-8">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-8">

        <div>
            <h1 class="text-3xl font-bold">
                MindLink Counsellor
            </h1>

            <p class="text-gray-600">
                Welcome, {{ auth()->user()->name }}!
            </p>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button class="bg-red-500 text-white px-4 py-2 rounded">
                Logout
            </button>
        </form>

    </div>


    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))

        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-5">
            {{ session('success') }}
        </div>

    @endif


    {{-- ERROR MESSAGE --}}
    @if ($errors->any())

        <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-5">

            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach

        </div>

    @endif


    {{-- MY AVAILABILITY --}}
    <div class="bg-white rounded-lg shadow p-6 mb-8">

        <h2 class="text-xl font-bold mb-2">
            My Availability
        </h2>

        <p class="text-gray-500 mb-6">
            Set the dates and times when students can book appointments with you.
        </p>


        {{-- ADD AVAILABILITY FORM --}}
        <form action="{{ route('counsellor.availability.store') }}"
              method="POST"
              class="border rounded-lg p-5 mb-6">

            @csrf

            <h3 class="font-semibold text-lg mb-4">
                Add New Availability
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- DATE --}}
                <div>

                    <label class="block font-medium mb-2">
                        Available Date
                    </label>

                    <input
                        type="date"
                        name="date"
                        min="{{ date('Y-m-d') }}"
                        value="{{ old('date') }}"
                        required
                        class="w-full border rounded-lg p-3"
                    >

                </div>


                {{-- START TIME --}}
                <div>

                    <label class="block font-medium mb-2">
                        Start Time
                    </label>

                    <input
                        type="time"
                        name="start_time"
                        value="{{ old('start_time') }}"
                        required
                        class="w-full border rounded-lg p-3"
                    >

                </div>


                {{-- END TIME --}}
                <div>

                    <label class="block font-medium mb-2">
                        End Time
                    </label>

                    <input
                        type="time"
                        name="end_time"
                        value="{{ old('end_time') }}"
                        required
                        class="w-full border rounded-lg p-3"
                    >

                </div>

            </div>


            <p class="text-gray-500 text-sm mt-3">
                Each availability slot lasts one hour.
            </p>


            <button
                type="submit"
                class="bg-blue-600 text-white px-6 py-3 rounded-lg mt-5 hover:bg-blue-700"
            >
                + Add Availability
            </button>

        </form>


        {{-- EXISTING AVAILABILITY --}}
        <h3 class="font-semibold text-lg mb-4">
            Your Available Slots
        </h3>


        @forelse ($availabilities as $availability)

            <div class="border rounded-lg p-4 mb-3 flex flex-wrap justify-between items-center gap-3">

                <div>

                    <p class="font-semibold">

                        {{ \Carbon\Carbon::parse($availability->date)->format('d M Y') }}

                    </p>

                    <p class="text-gray-500 text-sm">

                        {{ \Carbon\Carbon::parse($availability->time)->format('h:i A') }}

                    </p>

                </div>


                <div class="flex items-center gap-3">

                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                        Available
                    </span>


                    <form
                        action="{{ route('counsellor.availability.destroy', $availability->id) }}"
                        method="POST"
                    >

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            onclick="return confirm('Remove this availability slot?')"
                            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600"
                        >
                            Remove
                        </button>

                    </form>

                </div>

            </div>

        @empty

            <p class="text-gray-500">
                You haven't added any availability yet.
            </p>

        @endforelse

    </div>



    {{-- MY APPOINTMENTS --}}
    <div class="bg-white rounded-lg shadow p-6">

        <h2 class="text-xl font-bold mb-4">
            My Appointments
        </h2>


        @forelse ($appointments as $appointment)

            <div class="border rounded-lg p-4 mb-4">

                <h3 class="font-bold text-lg">
                    {{ $appointment->student->name ?? 'Unknown student' }}
                </h3>

                <p class="text-gray-600">
                    Email: {{ $appointment->student->email ?? 'N/A' }}
                </p>

                <p>
                    Date:
                    {{ $appointment->date->format('d M Y') }}
                </p>

                <p>
                    Time: {{ $appointment->time }}
                </p>

                <p>
                    Meeting mode: {{ $appointment->mode }}
                </p>

                <p>
                    Status:
                    <span class="font-semibold">
                        {{ ucfirst($appointment->status) }}
                    </span>
                </p>

            </div>

        @empty

            <p class="text-gray-500">
                No appointments have been booked yet.
            </p>

        @endforelse

    </div>

</div>

</body>
</html>
