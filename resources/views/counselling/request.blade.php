<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Counselling Request - MindLink</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased h-full bg-[#f4f7fa] text-slate-800">
    <div class="min-h-screen flex" x-data="counsellingWizard()">
        <!-- Unified MindLink Sidebar -->
        @include('layouts.navigation')

        <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
            <!-- Top Bar -->
            <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        onclick="window.dispatchEvent(new CustomEvent('toggle-sidebar'))"
                        class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition focus:outline-none"
                        aria-label="Toggle Navigation"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
                        {{ !empty($isAppointmentManager) ? 'Counselling Appointments' : 'Counselling Request' }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-semibold border border-slate-200 bg-white text-slate-600 shadow-2xs">
                        {{ Auth::user()?->portal_name ?? 'Student portal' }}
                    </span>
                </div>
            </header>

            <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto space-y-8">
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl text-sm shadow-2xs space-y-1">
                        <p class="font-bold">Submission Failed — Please fix the following error(s):</p>
                        <ul class="list-disc pl-5 text-xs space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl text-sm font-medium shadow-2xs">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="bg-[#e6f5f0] border border-[#cdece2] text-[#0f766e] px-5 py-4 rounded-xl text-sm font-medium shadow-2xs flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <span class="text-xs bg-teal-200/70 text-teal-900 px-2.5 py-0.5 rounded-full font-bold">Saved</span>
                    </div>
                @endif

                @if(!empty($isAppointmentManager))
                    <!-- Direct Appointment Slot Booking & Reschedule/Cancel Manager (/student/appointments) -->
                    <div class="space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h2 class="text-2xl font-bold text-[#0f2a4a]">Book &amp; Manage Appointments</h2>
                                <p class="text-sm text-slate-500 mt-1">Book verified counsellor availability slots, reschedule, or cancel appointments.</p>
                            </div>
                            <a href="{{ route('counselling.request') }}" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-[#0f2a4a] hover:bg-slate-50 transition shadow-2xs">
                                &larr; Use 4-Step Counselling Wizard
                            </a>
                        </div>

                        <div id="message" class="hidden p-4 rounded-xl text-sm font-medium"></div>

                        <section class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
                            <h3 class="text-lg font-bold text-[#0f2a4a] mb-5">Book an Appointment</h3>

                            <form id="bookingForm" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Select Counsellor</label>
                                    <select id="counsellor" required class="w-full border border-slate-300 rounded-xl p-3 text-sm bg-white">
                                        <option value="">Loading counsellors...</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Select Date</label>
                                    <input type="date" id="date" required class="w-full border border-slate-300 rounded-xl p-3 text-sm bg-white">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Available Time</label>
                                    <select id="time" required class="w-full border border-slate-300 rounded-xl p-3 text-sm bg-white">
                                        <option value="">Select a counsellor and date first</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Appointment Mode</label>
                                    <select id="mode" required class="w-full border border-slate-300 rounded-xl p-3 text-sm bg-white">
                                        <option value="In person">In person</option>
                                        <option value="Online">Online</option>
                                    </select>
                                </div>

                                <button type="submit" class="bg-[#1c9c8c] text-white font-semibold px-6 py-3 rounded-xl hover:bg-[#157a6e] transition shadow-2xs">
                                    Confirm Booking
                                </button>
                            </form>
                        </section>

                        <section class="space-y-4">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-bold text-[#0f2a4a]">My Appointments</h3>
                                <button type="button" onclick="loadAppointments()" class="text-xs font-bold text-[#1c9c8c] hover:underline">
                                    Refresh
                                </button>
                            </div>

                            <div id="appointmentList" class="space-y-4">
                                <p class="text-sm text-slate-500">Loading appointments...</p>
                            </div>
                        </section>
                    </div>
                @else
                    <!-- Unified 4-Step Counselling Request & Informed Consent Wizard -->
                    <div>
                        <h3 class="text-xl font-bold text-[#0f2a4a]">Request private support</h3>
                        <p class="text-sm text-slate-500 mt-1">Complete each step—including Informed Consent—before submitting your counselling request.</p>

                        <div class="flex items-center max-w-2xl mt-6 relative">
                            <div class="absolute left-6 right-6 top-1/2 -translate-y-1/2 h-0.5 bg-slate-200 z-0"></div>

                            <div class="flex flex-col items-center relative z-10 flex-1">
                                <div :class="step >= 1 ? 'bg-[#1c9c8c] text-white shadow-md' : 'bg-slate-200 text-slate-500'" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition">1</div>
                                <span class="text-xs font-medium text-slate-700 mt-2">Concern</span>
                            </div>
                            <div class="flex flex-col items-center relative z-10 flex-1">
                                <div :class="step >= 2 ? 'bg-[#1c9c8c] text-white shadow-md' : 'bg-slate-200 text-slate-500'" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition">2</div>
                                <span class="text-xs font-medium text-slate-700 mt-2">Consent</span>
                            </div>
                            <div class="flex flex-col items-center relative z-10 flex-1">
                                <div :class="step >= 3 ? 'bg-[#1c9c8c] text-white shadow-md' : 'bg-slate-200 text-slate-500'" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition">3</div>
                                <span class="text-xs font-medium text-slate-700 mt-2">Schedule</span>
                            </div>
                            <div class="flex flex-col items-center relative z-10 flex-1">
                                <div :class="step >= 4 ? 'bg-[#0f2a4a] text-white ring-4 ring-slate-200 shadow-md' : 'bg-slate-200 text-slate-500'" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition">4</div>
                                <span class="text-xs font-medium text-slate-500 mt-2">Review</span>
                            </div>
                        </div>
                    </div>

                    <form id="counsellingForm" action="{{ route('counselling.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        @csrf

                        <input type="hidden" name="concern" :value="concern">
                        <input type="hidden" name="goals" :value="goals">
                        <input type="hidden" name="consent_agreed" :value="consentAgreed ? '1' : ''">
                        <input type="hidden" name="counsellor_id" :value="selectedCounsellorId">
                        <input type="hidden" name="appointment_mode" :value="appointmentMode">
                        <input type="hidden" name="appointment_date" :value="selectedDate">
                        <input type="hidden" name="appointment_time" :value="selectedTime">

                        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6 space-y-6">

                            <template x-if="step === 1">
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-lg font-bold text-[#0f2a4a]">Step 1: Describe your concern</h4>
                                        <p class="text-xs text-slate-500 mt-1">Please provide details regarding your concern (10–1000 characters) and optional goals.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Concern Description *</label>
                                        <textarea rows="3" maxlength="1000" x-model="concern" @input="stepError = ''" class="w-full rounded-xl border border-slate-300 p-3 text-sm focus:ring-2 focus:ring-[#1c9c8c] text-slate-800" placeholder="Describe what you are currently going through..."></textarea>
                                        <div class="flex justify-between items-center mt-1 text-[11px]">
                                            <span :class="concern.trim().length >= 10 ? 'text-[#1c9c8c] font-medium' : 'text-slate-400'">
                                                <span x-text="concern.trim().length"></span> / 10 min characters
                                            </span>
                                            <span class="text-slate-400">
                                                <span x-text="concern.length"></span> / 1000 max
                                            </span>
                                        </div>
                                        <p x-show="stepError" x-text="stepError" class="text-xs font-semibold text-red-600 mt-2 bg-red-50 border border-red-200 px-3 py-2 rounded-lg"></p>
                                    </div>
                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Counselling Goals (Optional)</label>
                                            <span class="text-[11px] text-slate-400"><span x-text="goals.length"></span> / 500 max</span>
                                        </div>
                                        <textarea rows="2" maxlength="500" x-model="goals" class="w-full rounded-xl border border-slate-300 p-3 text-sm focus:ring-2 focus:ring-[#1c9c8c] text-slate-800" placeholder="What do you hope to achieve from this session? (Optional)"></textarea>
                                    </div>
                                    <div class="pt-2 flex justify-end">
                                        <button type="button" @click="validateStep1()" class="bg-[#1c9c8c] hover:bg-[#157a6e] text-white font-medium px-6 py-2.5 rounded-xl transition shadow-2xs cursor-pointer">
                                            Continue to Consent &rarr;
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="step === 2">
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-lg font-bold text-[#0f2a4a]">Step 2: Informed Consent Agreement</h4>
                                        <p class="text-xs font-semibold text-[#1c9c8c] mt-0.5">Informed Consent &amp; Data Usage Policy</p>
                                        <p class="text-xs text-slate-500 mt-1">Please review how your personal and counselling information will be handled before proceeding.</p>
                                    </div>

                                    <div id="declineAlert" x-show="showDeclineAlert" style="display: none;" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-xs font-semibold">
                                        Counselling services cannot proceed without your informed consent. You may review the terms again when you are ready.
                                    </div>

                                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-700 space-y-3 max-h-64 overflow-y-auto leading-relaxed border-l-4 border-l-[#1c9c8c]">
                                        <h5 class="font-bold text-slate-900 text-sm">MindLink Telkom University Support Platform</h5>

                                        <p class="font-semibold text-slate-800">1. Purpose of Data Collection</p>
                                        <p>MindLink collects your personal identification (Name, Student ID) and wellbeing concerns solely to facilitate scheduling and short-term emotional, academic, and peer support sessions with authorized Telkom University counsellors.</p>

                                        <p class="font-semibold text-slate-800">2. Confidentiality &amp; Privacy Boundaries</p>
                                        <p>All session notes, disclosures, and specific concerns are strictly confidential and accessible only to your assigned counsellor. University administrators can only view anonymized operational metrics and consent timestamps; they cannot view session notes or specific concern descriptions.</p>

                                        <p class="font-semibold text-slate-800">3. Data Sharing &amp; Legal Exceptions</p>
                                        <p>Your personal data and counselling records are protected under Indonesia Law No. 27 of 2022 on Personal Data Protection (PDP Law) and will never be shared with third parties, faculty members, or parents without explicit consent, except in cases involving imminent risk of harm to yourself or others, reported abuse of minors/vulnerable persons, or a formal court order.</p>

                                        <p class="font-semibold text-slate-800">4. Non-Emergency &amp; Non-Diagnostic Scope</p>
                                        <p>MindLink provides educational mental health literacy and supportive counselling referrals, and is not an emergency crisis hotline or clinical psychiatric diagnosis service. For immediate emergencies, contact campus security or national emergency hotlines.</p>
                                    </div>

                                    <label class="flex items-start space-x-3 cursor-pointer pt-2">
                                        <input
                                            type="checkbox"
                                            id="is_agreed"
                                            x-model="consentAgreed"
                                            @change="stepError = ''; if (consentAgreed) showDeclineAlert = false;"
                                            class="mt-0.5 w-4 h-4 text-[#1c9c8c] rounded border-slate-300 focus:ring-[#1c9c8c]"
                                        >
                                        <span class="text-xs font-medium text-slate-800">I have carefully read, understood, and voluntarily accept the Informed Consent &amp; Data Usage Policy. *</span>
                                    </label>

                                    <p x-show="stepError" x-text="stepError" class="text-xs font-semibold text-red-600 bg-red-50 border border-red-200 px-3 py-2 rounded-lg"></p>

                                    <div class="pt-3 flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="stepError = ''; step = 1" class="border border-slate-300 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Back</button>
                                            <button type="button" @click="declineConsent()" class="border border-slate-300 bg-slate-100 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-200 transition">Decline</button>
                                        </div>

                                        <button type="button" @click="validateStep2()" class="bg-[#1c9c8c] hover:bg-[#157a6e] text-white font-medium px-6 py-2.5 rounded-xl transition shadow-2xs cursor-pointer">
                                            Continue to Schedule &rarr;
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="step === 3">
                                <div class="space-y-5">
                                    <div>
                                        <h4 class="text-lg font-bold text-[#0f2a4a]">Choose an available time &amp; format</h4>
                                        <p class="text-xs text-slate-500 mt-1">Select your preferred counsellor, format, date, and available time slot.</p>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Session Format *</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button type="button" @click="appointmentMode = 'In person'" :class="appointmentMode === 'In person' ? 'bg-[#1c9c8c] text-white shadow-2xs' : 'border border-slate-300 text-slate-700 bg-white hover:bg-slate-50'" class="py-2.5 rounded-xl font-semibold text-sm transition">
                                                🏢 In Person
                                            </button>
                                            <button type="button" @click="appointmentMode = 'Online Meeting'" :class="appointmentMode === 'Online Meeting' ? 'bg-[#1c9c8c] text-white shadow-2xs' : 'border border-slate-300 text-slate-700 bg-white hover:bg-slate-50'" class="py-2.5 rounded-xl font-semibold text-sm transition">
                                                💻 Online Meeting
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <div class="flex justify-between items-center">
                                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Select Counsellor *</label>
                                            <span class="text-[11px] text-slate-400">Click again to deselect</span>
                                        </div>
                                        <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                                            @foreach($counsellors as $c)
                                                <div @click="toggleCounsellor('{{ $c->counsellor_id }}', '{{ $c->name }}')"
                                                     :class="selectedCounsellorId == '{{ $c->counsellor_id }}' ? 'border-[#1c9c8c] bg-[#e6f5f0]/50 ring-2 ring-[#1c9c8c]/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'"
                                                     class="border rounded-xl p-3 flex items-center justify-between cursor-pointer transition select-none">
                                                    <div class="flex items-center space-x-3 text-slate-800 font-medium">
                                                        <span class="text-[#1c9c8c] font-bold text-sm">{{ $c->name }}</span>
                                                        <span class="text-slate-300">•</span>
                                                        <span class="text-slate-600 text-xs">{{ $c->specialization }}</span>
                                                    </div>
                                                    <div :class="selectedCounsellorId == '{{ $c->counsellor_id }}' ? 'border-[#1c9c8c] bg-[#1c9c8c]' : 'border-slate-300 bg-white'" class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0">
                                                        <div x-show="selectedCounsellorId == '{{ $c->counsellor_id }}'" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Select Date *</label>
                                        <div class="relative">
                                            <input type="date" x-model="selectedDate" @input="onDateOrCounsellorChange()" @change="onDateOrCounsellorChange()" min="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-800 shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#1c9c8c] focus:border-[#1c9c8c]">
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <div class="flex justify-between items-center">
                                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Select Time Slot *</label>
                                            <span class="text-[11px] text-slate-400">Greyed slots are already booked</span>
                                        </div>
                                        <div class="grid grid-cols-4 gap-2">
                                            <template x-for="time in ['09:00', '10:00', '13:30', '15:00']">
                                                <button type="button"
                                                    :disabled="isSlotBooked(time)"
                                                    @click="toggleTime(time)"
                                                    :class="isSlotBooked(time)
                                                        ? 'bg-slate-100 border border-slate-200 text-slate-400 cursor-not-allowed line-through opacity-75'
                                                        : (selectedTime === time
                                                            ? 'bg-[#1c9c8c] text-white shadow-2xs cursor-pointer'
                                                            : 'border border-[#1c9c8c] text-[#157a6e] bg-white hover:bg-[#e6f5f0] cursor-pointer')"
                                                    class="py-2 rounded-xl font-semibold text-sm transition flex flex-col items-center justify-center">
                                                    <span x-text="time"></span>
                                                    <span x-show="isSlotBooked(time)" class="text-[9px] font-bold uppercase tracking-wider text-slate-400 no-underline">Booked</span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <p x-show="stepError" x-text="stepError" class="text-xs font-semibold text-red-600 bg-red-50 border border-red-200 px-3 py-2 rounded-lg"></p>

                                    <div class="pt-2 flex justify-between">
                                        <button type="button" @click="stepError = ''; step = 2" class="border border-slate-300 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium">Back</button>
                                        <button type="button" @click="validateStep3()" class="bg-[#1c9c8c] hover:bg-[#157a6e] text-white font-medium px-6 py-2.5 rounded-xl transition shadow-2xs cursor-pointer">
                                            Review Request &rarr;
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="step === 4">
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-lg font-bold text-[#0f2a4a]">Step 4: Final Review</h4>
                                        <p class="text-xs text-slate-500 mt-1">Review your details before submitting your request.</p>
                                    </div>

                                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2.5 text-sm text-slate-700">
                                        <div>
                                            <span class="font-bold text-xs uppercase text-slate-400 block">Concern:</span>
                                            <p x-text="concern" class="mt-0.5 text-slate-900 font-medium break-all"></p>
                                        </div>
                                        <template x-if="goals">
                                            <div>
                                                <span class="font-bold text-xs uppercase text-slate-400 block">Counselling Goals:</span>
                                                <p x-text="goals" class="mt-0.5 text-slate-900 font-medium break-all"></p>
                                            </div>
                                        </template>
                                        <div>
                                            <span class="font-bold text-xs uppercase text-slate-400 block">Chosen Counsellor:</span>
                                            <p x-text="selectedCounsellorName" class="mt-0.5 text-[#157a6e] font-bold"></p>
                                        </div>
                                        <div>
                                            <span class="font-bold text-xs uppercase text-slate-400 block">Format &amp; Schedule:</span>
                                            <p class="mt-0.5 text-slate-900 font-medium"><span x-text="appointmentMode"></span> — <span x-text="formattedDate"></span>, <span x-text="selectedTime"></span></p>
                                        </div>
                                        <div>
                                            <span class="font-bold text-xs uppercase text-slate-400 block">Consent Status:</span>
                                            <p class="mt-0.5 text-[#157a6e] font-bold">Accepted ✓</p>
                                        </div>
                                    </div>

                                    <div class="pt-4 flex justify-between">
                                        <button type="button" @click="step = 3" class="border border-slate-300 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium">Back</button>
                                        <button type="button" @click="showModal = true" class="bg-[#1c9c8c] hover:bg-[#157a6e] text-white font-medium px-6 py-2.5 rounded-xl transition shadow-2xs cursor-pointer flex items-center space-x-2">
                                            <span>Confirm &amp; Submit Request</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6 space-y-5 flex flex-col justify-between overflow-hidden">
                            <div class="space-y-4">
                                <h4 class="text-lg font-bold text-[#0f2a4a]">Your request</h4>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium uppercase">Concern</p>
                                    <p class="text-sm font-medium text-slate-800 mt-0.5 break-all" x-text="concern || 'Pending input...'"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium uppercase">Consent</p>
                                    <p class="text-sm font-medium text-slate-800 mt-0.5" x-text="consentAgreed ? 'Accepted' : 'Pending agreement'"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium uppercase">Format &amp; Counsellor</p>
                                    <p class="text-sm font-medium text-[#157a6e] mt-0.5"><span x-text="appointmentMode"></span> with <span x-text="selectedCounsellorName || 'None selected'"></span></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium uppercase">Schedule</p>
                                    <p class="text-sm font-medium text-slate-800 mt-0.5" x-text="(formattedDate || 'No date') + ', ' + (selectedTime || 'No time')"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium uppercase">Status</p>
                                    <p class="text-sm font-medium text-slate-800 mt-0.5" x-text="step === 4 ? 'Ready for submission' : 'Draft – not yet submitted'"></p>
                                </div>
                            </div>
                            <div class="bg-[#fff3e6] border border-[#fde2c8] rounded-xl p-3 text-xs text-orange-800 font-medium">
                                <span x-text="step < 4 ? 'Review the request before submission.' : 'Ready for final submission.'"></span>
                            </div>
                        </div>
                    </form>

                    @if(!empty($appointments) && $appointments->isNotEmpty())
                        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-4">
                            <div>
                                <h3 class="text-base font-bold text-[#0f2a4a]">Your counselling requests &amp; scheduled appointments</h3>
                                <p class="text-xs text-slate-500 mt-0.5">You may cancel a request while it is still awaiting counsellor confirmation.</p>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @foreach($appointments as $appt)
                                    @php
                                        $canCancel = !in_array($appt->status, ['Confirmed', 'Approved', 'Declined', 'Cancelled', 'Completed', 'Completed & Notified'], true);
                                        $statusBadge = match (true) {
                                            in_array($appt->status, ['Declined', 'Cancelled'], true) => 'bg-red-50 text-red-600 border border-red-200',
                                            in_array($appt->status, ['Confirmed', 'Approved', 'Completed', 'Completed & Notified'], true) => 'bg-[#e6f5f0] text-[#1c9c8c]',
                                            default => 'bg-amber-50 text-amber-700 border border-amber-200',
                                        };
                                    @endphp
                                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                                        <div>
                                            <div class="font-bold text-[#0f2a4a]">{{ $appt->counsellor_name }} &bull; <span class="font-medium text-slate-600">{{ $appt->concern }}</span></div>
                                            <div class="text-slate-500 mt-0.5">{{ $appt->scheduled_date ?? $appt->appointment_date }} at {{ $appt->scheduled_time ?? $appt->appointment_time }} &bull; {{ $appt->session_mode ?? $appt->appointment_mode }}</div>
                                        </div>
                                        <div class="flex items-center gap-2.5 self-start sm:self-center shrink-0">
                                            <span class="px-3 py-1 rounded-full font-semibold {{ $statusBadge }}">
                                                {{ $appt->status }}
                                            </span>
                                            @if($canCancel)
                                                <form action="{{ route('counselling.cancel', $appt) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        dusk="cancel-request-{{ $appt->id }}"
                                                        class="px-3 py-1 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-bold text-xs transition cursor-pointer"
                                                    >
                                                        Cancel Request
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </main>
        </div>

        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             style="display: none;">

            <div @click.away="showModal = false" class="bg-white rounded-3xl border border-slate-200 max-w-md w-full p-6 space-y-5 shadow-2xl relative text-center">
                <div class="w-16 h-16 rounded-full bg-[#e6f5f0] border border-[#cdece2] text-[#1c9c8c] flex items-center justify-center mx-auto text-2xl shadow-2xs">
                    💬
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-[#0f2a4a]">Request Submitted for Review</h3>
                    <p class="text-xs text-slate-500 leading-relaxed px-2">
                        Your counselling request will be sent to <span class="font-bold text-[#157a6e]" x-text="selectedCounsellorName"></span>.
                        Our team will review your details shortly. Once your schedule is confirmed, updates will appear in your Student Dashboard!
                    </p>
                </div>

                <div class="bg-slate-50 border border-slate-200/70 rounded-2xl p-4 text-xs text-left space-y-1.5 text-slate-700">
                    <p><strong>Counsellor:</strong> <span x-text="selectedCounsellorName"></span></p>
                    <p><strong>Scheduled:</strong> <span x-text="formattedDate"></span> at <span x-text="selectedTime"></span></p>
                    <p><strong>Format:</strong> <span x-text="appointmentMode"></span></p>
                    <p><strong>Status:</strong> <span class="text-amber-600 font-semibold">Under Review</span></p>
                </div>

                <div class="pt-2 flex flex-col space-y-2">
                    <button type="button" @click="submitForm()" class="w-full bg-[#1c9c8c] hover:bg-[#157a6e] text-white font-semibold py-3 rounded-xl shadow-md transition cursor-pointer">
                        Understand &amp; Proceed
                    </button>
                    <button type="button" @click="showModal = false" class="w-full border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium py-2.5 rounded-xl transition text-xs">
                        Back to Review
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function counsellingWizard() {
            return {
                step: {{ $errors->any() ? 4 : ($initialStep ?? 1) }},
                stepError: '',
                showDeclineAlert: false,
                showModal: false,
                todayDate: '{{ date("Y-m-d") }}',
                bookedSlots: @json($bookedSlots),
                concern: @json(old('concern', '')),
                goals: @json(old('goals', '')),
                consentAgreed: {{ old('consent_agreed') ? 'true' : 'false' }},
                appointmentMode: @json(old('appointment_mode', 'In person')),
                selectedCounsellorId: '{{ old("counsellor_id", "") }}',
                selectedCounsellorName: '',
                selectedDate: '{{ old("appointment_date", date("Y-m-d")) }}',
                selectedTime: '{{ old("appointment_time", "") }}',

                isSlotBooked(time) {
                    if (!this.selectedCounsellorName || !this.selectedDate) return false;
                    return this.bookedSlots.some(slot =>
                        slot.counsellor_name === this.selectedCounsellorName &&
                        slot.appointment_date === this.selectedDate &&
                        slot.appointment_time === time
                    );
                },

                onDateOrCounsellorChange() {
                    this.stepError = '';
                    if (this.selectedTime && this.isSlotBooked(this.selectedTime)) {
                        this.selectedTime = '';
                    }
                },

                toggleCounsellor(id, name) {
                    if (this.selectedCounsellorId == id) {
                        this.selectedCounsellorId = '';
                        this.selectedCounsellorName = '';
                    } else {
                        this.selectedCounsellorId = id;
                        this.selectedCounsellorName = name;
                    }
                    this.onDateOrCounsellorChange();
                },

                toggleTime(time) {
                    if (this.isSlotBooked(time)) return;
                    this.stepError = '';
                    this.selectedTime = (this.selectedTime === time) ? '' : time;
                },

                declineConsent() {
                    this.consentAgreed = false;
                    this.stepError = '';
                    this.showDeclineAlert = true;
                    const cb = document.getElementById('is_agreed');
                    if (cb) cb.checked = false;
                },

                submitStandaloneConsent() {
                    const cb = document.getElementById('is_agreed');
                    if (!this.consentAgreed && !(cb && cb.checked)) return;
                    document.getElementById('standaloneConsentForm').submit();
                },

                validateStep1() {
                    if (!this.concern.trim()) {
                        this.stepError = 'Please describe your concern before continuing.';
                        return;
                    }
                    if (this.concern.trim().length < 10) {
                        this.stepError = 'Your concern description must be at least 10 characters.';
                        return;
                    }
                    this.stepError = '';
                    this.step = 2;
                },

                validateStep2() {
                    const cb = document.getElementById('is_agreed');
                    if (!this.consentAgreed && !(cb && cb.checked)) {
                        this.stepError = 'You must accept the Informed Consent Agreement (UU PDP) to proceed.';
                        return;
                    }
                    this.consentAgreed = true;
                    this.stepError = '';
                    this.step = 3;
                },

                validateStep3() {
                    if (!this.selectedCounsellorId) {
                        this.stepError = 'Please select a counsellor before continuing.';
                        return;
                    }
                    if (!this.selectedDate) {
                        this.stepError = 'Please select an appointment date.';
                        return;
                    }
                    if (this.selectedDate < this.todayDate) {
                        this.stepError = 'Appointment date cannot be in the past.';
                        return;
                    }
                    if (!this.selectedTime) {
                        this.stepError = 'Please select an available time slot.';
                        return;
                    }
                    if (this.isSlotBooked(this.selectedTime)) {
                        this.stepError = 'This time slot is already booked. Please choose another.';
                        return;
                    }
                    this.stepError = '';
                    this.step = 4;
                },

                get formattedDate() {
                    if (!this.selectedDate) return '';
                    const parts = this.selectedDate.split('-');
                    if (parts.length !== 3) return this.selectedDate;
                    const year = parts[0];
                    const monthIndex = parseInt(parts[1], 10) - 1;
                    const day = parseInt(parts[2], 10);

                    const months = [
                        'January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ];

                    return `${day} ${months[monthIndex]} ${year}`;
                },

                submitForm() {
                    document.getElementById('counsellingForm').submit();
                }
            }
        }
    </script>

    @if(!empty($isAppointmentManager))
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
                ? 'p-4 rounded-xl bg-green-100 text-green-700 text-sm font-medium'
                : 'p-4 rounded-xl bg-red-100 text-red-700 text-sm font-medium';
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

        async function loadCounsellors() {
            try {
                const counsellors = await api('/api/counsellors');
                counsellorSelect.innerHTML = '<option value="">Choose a counsellor</option>';
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

        async function loadSlots() {
            const counsellorId = counsellorSelect.value;
            const date = dateInput.value;
            timeSelect.innerHTML = '<option value="">Loading...</option>';

            if (!counsellorId || !date) {
                timeSelect.innerHTML = '<option value="">Select a counsellor and date first</option>';
                return;
            }

            try {
                const slots = await api(`/api/slots?counsellor_id=${counsellorId}&date=${date}`);
                timeSelect.innerHTML = '';
                if (slots.length === 0) {
                    timeSelect.innerHTML = '<option value="">No available slots</option>';
                    return;
                }
                timeSelect.innerHTML = '<option value="">Choose a time</option>';
                slots.forEach(time => {
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = time;
                    timeSelect.appendChild(option);
                });
            } catch (error) {
                timeSelect.innerHTML = '<option value="">Unable to load slots</option>';
                showMessage(error.message);
            }
        }

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
                timeSelect.innerHTML = '<option value="">Select a counsellor and date first</option>';
                await loadAppointments();
            } catch (error) {
                showMessage(error.message);
            }
        });

        async function loadAppointments() {
            try {
                const appointments = await api('/api/appointments');
                appointmentList.innerHTML = '';

                if (appointments.length === 0) {
                    appointmentList.innerHTML = '<p class="bg-white p-6 rounded-2xl border border-slate-200/90 text-slate-500 text-sm">You have no confirmed appointments yet.</p>';
                    return;
                }

                appointments.forEach(appointment => {
                    const card = document.createElement('div');
                    card.className = 'bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6';
                    card.innerHTML = `
                        <div class="flex flex-col sm:flex-row justify-between gap-4">
                            <div>
                                <h4 class="font-bold text-lg text-[#0f2a4a]">${escapeHTML(appointment.counsellor?.name || 'Counsellor')}</h4>
                                <p class="text-slate-500 text-xs mt-2">Date: ${escapeHTML(String(appointment.date).substring(0, 10))}</p>
                                <p class="text-slate-500 text-xs">Time: ${escapeHTML(appointment.time)}</p>
                                <p class="text-slate-500 text-xs">Mode: ${escapeHTML(appointment.mode)}</p>
                                <span class="inline-block mt-3 bg-[#e6f5f0] text-[#1c9c8c] px-3 py-1 rounded-full text-xs font-semibold">Confirmed</span>
                            </div>
                            <div class="flex gap-2 items-start">
                                <button onclick="rescheduleAppointment(${appointment.id}, ${appointment.counsellor_id}, '${escapeHTML(appointment.date).substring(0,10)}', '${escapeHTML(appointment.time)}', '${escapeHTML(appointment.mode)}')" class="bg-amber-500 text-white px-4 py-2 rounded-xl text-xs font-semibold">Reschedule</button>
                                <button onclick="cancelAppointment(${appointment.id})" class="bg-rose-600 text-white px-4 py-2 rounded-xl text-xs font-semibold">Cancel</button>
                            </div>
                        </div>
                    `;
                    appointmentList.appendChild(card);
                });
            } catch (error) {
                appointmentList.innerHTML = '<p class="text-red-600 text-sm">Unable to load appointments.</p>';
                showMessage(error.message);
            }
        }

        async function rescheduleAppointment(id, counsellorId, oldDate, oldTime, mode) {
            const newDate = prompt('Enter the new date (YYYY-MM-DD):', oldDate);
            if (!newDate) return;
            try {
                const slots = await api(`/api/slots?counsellor_id=${counsellorId}&date=${newDate}&ignore=${id}`);
                if (slots.length === 0) {
                    showMessage('No available slots on that date.');
                    return;
                }
                const newTime = prompt('Available times: ' + slots.join(', ') + '\nEnter your preferred time:', slots.includes(oldTime) ? oldTime : slots[0]);
                if (!newTime) return;
                if (!slots.includes(newTime)) {
                    showMessage('Please choose one of the available times.');
                    return;
                }
                await api(`/api/appointments/${id}`, {
                    method: 'PUT',
                    body: JSON.stringify({ counsellor_id: counsellorId, date: newDate, time: newTime, mode: mode })
                });
                showMessage('Appointment rescheduled successfully!', true);
                await loadAppointments();
            } catch (error) {
                showMessage(error.message);
            }
        }

        async function cancelAppointment(id) {
            if (!confirm('Are you sure you want to cancel this appointment?')) return;
            try {
                await api(`/api/appointments/${id}`, { method: 'DELETE' });
                showMessage('Appointment cancelled successfully.', true);
                await loadAppointments();
            } catch (error) {
                showMessage(error.message);
            }
        }

        counsellorSelect.addEventListener('change', loadSlots);
        dateInput.addEventListener('change', loadSlots);

        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minDate = [
            tomorrow.getFullYear(),
            String(tomorrow.getMonth() + 1).padStart(2, '0'),
            String(tomorrow.getDate()).padStart(2, '0')
        ].join('-');
        dateInput.min = minDate;

        loadCounsellors();
        loadAppointments();
    </script>
    @endif
</body>
</html>
