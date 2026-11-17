
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MindLink - Student Appointments</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 text-gray-800">

    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">MindLink</h1>

        <div class="flex items-center gap-4">
            <span>{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="bg-white text-blue-600 px-4 py-2 rounded-lg">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto p-6">

        <h2 class="text-3xl font-bold">Student Dashboard</h2>
        <p class="text-gray-500 mt-2">
            Book and manage your counselling appointments.
        </p>

        <div id="message" class="hidden mt-5 p-4 rounded-lg"></div>

        <!-- Booking form -->
        <section class="bg-white rounded-xl shadow p-6 mt-8">
            <h3 class="text-xl font-bold mb-5">Book an Appointment</h3>

            <form id="bookingForm" class="space-y-4">

                <div>
                    <label class="block font-medium mb-2">Select Counsellor</label>
                    <select id="counsellor" required
                        class="w-full border rounded-lg p-3">
                        <option value="">Loading counsellors...</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium mb-2">Select Date</label>
                    <input type="date" id="date" required
                        class="w-full border rounded-lg p-3">
                </div>

                <div>
                    <label class="block font-medium mb-2">Available Time</label>
                    <select id="time" required
                        class="w-full border rounded-lg p-3">
                        <option value="">Select a counsellor and date first</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium mb-2">Appointment Mode</label>
                    <select id="mode" required
                        class="w-full border rounded-lg p-3">
                        <option value="In person">In person</option>
                        <option value="Online">Online</option>
                    </select>
                </div>

                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                    Confirm Booking
                </button>
            </form>
        </section>

        <!-- Appointment list -->
        <section class="mt-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">My Appointments</h3>
                <button onclick="loadAppointments()"
                    class="text-blue-600 font-medium">
                    Refresh
                </button>
            </div>

            <div id="appointmentList" class="space-y-4">
                <p class="text-gray-500">Loading appointments...</p>
            </div>
        </section>

    </main>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const counsellorSelect = document.getElementById('counsellor');
const dateInput = document.getElementById('date');
const timeSelect = document.getElementById('time');
const bookingForm = document.getElementById('bookingForm');
const appointmentList = document.getElementById('appointmentList');
const messageBox = document.getElementById('message');

function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[char]);
}

function showMessage(message, success = false) {
    messageBox.textContent = message;
    messageBox.className = success
        ? 'mt-5 p-4 rounded-lg bg-green-100 text-green-700'
        : 'mt-5 p-4 rounded-lg bg-red-100 text-red-700';
}

async function api(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            ...options.headers
        }
    });

    if (response.status === 204) return null;

    const data = await response.json();

    if (!response.ok) {
        const error = data.message ||
            Object.values(data.errors || {}).flat().join(' ') ||
            'Something went wrong.';

        throw new Error(error);
    }

    return data;
}

// Load counsellors
async function loadCounsellors() {
    try {
        const counsellors = await api('/api/counsellors');

        counsellorSelect.innerHTML =
            '<option value="">Choose a counsellor</option>';

        counsellors.forEach(counsellor => {
            const option = document.createElement('option');
            option.value = counsellor.id;
            option.textContent = counsellor.name;
            counsellorSelect.appendChild(option);
        });

    } catch (error) {
        showMessage(error.message);
    }
}

// Load available time slots
async function loadSlots() {
    const counsellorId = counsellorSelect.value;
    const date = dateInput.value;

    timeSelect.innerHTML = '<option value="">Loading...</option>';

    if (!counsellorId || !date) {
        timeSelect.innerHTML =
            '<option value="">Select a counsellor and date first</option>';
        return;
    }

    try {
        const slots = await api(
            `/api/slots?counsellor_id=${counsellorId}&date=${date}`
        );

        timeSelect.innerHTML = '';

        if (slots.length === 0) {
            timeSelect.innerHTML =
                '<option value="">No available slots</option>';
            return;
        }

        timeSelect.innerHTML =
            '<option value="">Choose a time</option>';

        slots.forEach(time => {
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time;
            timeSelect.appendChild(option);
        });

    } catch (error) {
        timeSelect.innerHTML =
            '<option value="">Unable to load slots</option>';
        showMessage(error.message);
    }
}

// Book appointment
bookingForm.addEventListener('submit', async event => {
    event.preventDefault();

    const booking = {
        counsellor_id: counsellorSelect.value,
        date: dateInput.value,
        time: timeSelect.value,
        mode: document.getElementById('mode').value
    };

    try {
        await api('/api/appointments', {
            method: 'POST',
            body: JSON.stringify(booking)
        });

        showMessage('Appointment booked successfully!', true);

        bookingForm.reset();
        timeSelect.innerHTML =
            '<option value="">Select a counsellor and date first</option>';

        await loadAppointments();

    } catch (error) {
        showMessage(error.message);
    }
});

// Display appointments
async function loadAppointments() {
    try {
        const appointments = await api('/api/appointments');

        appointmentList.innerHTML = '';

        if (appointments.length === 0) {
            appointmentList.innerHTML =
                '<p class="bg-white p-6 rounded-xl text-gray-500">You have no confirmed appointments yet.</p>';
            return;
        }

        appointments.forEach(appointment => {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-xl shadow p-6';

            card.innerHTML = `
                <div class="flex flex-col sm:flex-row justify-between gap-4">

                    <div>
                        <h4 class="font-bold text-lg">
                            ${escapeHTML(appointment.counsellor?.name || 'Counsellor')}
                        </h4>

                        <p class="text-gray-500 mt-2">
                            Date: ${escapeHTML(String(appointment.date).substring(0, 10))}
                        </p>

                        <p class="text-gray-500">
                            Time: ${escapeHTML(appointment.time)}
                        </p>

                        <p class="text-gray-500">
                            Mode: ${escapeHTML(appointment.mode)}
                        </p>

                        <span class="inline-block mt-3 bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                            Confirmed
                        </span>
                    </div>

                    <div class="flex gap-2 items-start">
                        <button
                            onclick="rescheduleAppointment(${appointment.id}, ${appointment.counsellor_id}, '${escapeHTML(appointment.date).substring(0,10)}', '${escapeHTML(appointment.time)}', '${escapeHTML(appointment.mode)}')"
                            class="bg-yellow-500 text-white px-4 py-2 rounded-lg">
                            Reschedule
                        </button>

                        <button
                            onclick="cancelAppointment(${appointment.id})"
                            class="bg-red-600 text-white px-4 py-2 rounded-lg">
                            Cancel
                        </button>
                    </div>

                </div>
            `;

            appointmentList.appendChild(card);
        });

    } catch (error) {
        appointmentList.innerHTML =
            '<p class="text-red-600">Unable to load appointments.</p>';
        showMessage(error.message);
    }
}

// Reschedule appointment
async function rescheduleAppointment(id, counsellorId, oldDate, oldTime, mode) {
    const newDate = prompt('Enter the new date (YYYY-MM-DD):', oldDate);

    if (!newDate) return;

    try {
        const slots = await api(
            `/api/slots?counsellor_id=${counsellorId}&date=${newDate}&ignore=${id}`
        );

        if (slots.length === 0) {
            showMessage('No available slots on that date.');
            return;
        }

        const newTime = prompt(
            'Available times: ' + slots.join(', ') + '\nEnter your preferred time:',
            slots.includes(oldTime) ? oldTime : slots[0]
        );

        if (!newTime) return;

        if (!slots.includes(newTime)) {
            showMessage('Please choose one of the available times.');
            return;
        }

        await api(`/api/appointments/${id}`, {
            method: 'PUT',
            body: JSON.stringify({
                counsellor_id: counsellorId,
                date: newDate,
                time: newTime,
                mode: mode
            })
        });

        showMessage('Appointment rescheduled successfully!', true);
        await loadAppointments();

    } catch (error) {
        showMessage(error.message);
    }
}

// Cancel appointment
async function cancelAppointment(id) {
    if (!confirm('Are you sure you want to cancel this appointment?')) {
        return;
    }

    try {
        await api(`/api/appointments/${id}`, {
            method: 'DELETE'
        });

        showMessage('Appointment cancelled successfully.', true);
        await loadAppointments();

    } catch (error) {
        showMessage(error.message);
    }
}

// Event listeners
counsellorSelect.addEventListener('change', loadSlots);
dateInput.addEventListener('change', loadSlots);

// Set minimum booking date to tomorrow (local date)
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
const minDate = [
    tomorrow.getFullYear(),
    String(tomorrow.getMonth() + 1).padStart(2, '0'),
    String(tomorrow.getDate()).padStart(2, '0')
].join('-');

dateInput.min = minDate;

// Initial loading
loadCounsellors();
loadAppointments();
</script>

</body>
</html>
