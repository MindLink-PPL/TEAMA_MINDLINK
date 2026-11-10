<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselling Request - MindLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden" x-data="counsellingWizard()">
        <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between hidden md:flex">
            <div>
                <div class="p-6 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-teal-600 flex items-center justify-center font-bold text-lg text-white">M</div>
                    <div>
                        <h1 class="font-bold text-lg tracking-tight">MindLink</h1>
                        <p class="text-xs text-slate-400">Support platform</p>
                    </div>
                </div>

                <nav class="mt-4 px-4 space-y-1">
                    <a href="#" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 rounded-xl transition">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-slate-800 text-xs text-teal-400 font-bold">D</span>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 rounded-xl transition">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-slate-800 text-xs text-teal-400 font-bold">L</span>
                        Learn
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 rounded-xl transition">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-slate-800 text-xs text-teal-400 font-bold">W</span>
                        Wellbeing
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 bg-slate-800 text-white rounded-xl font-medium shadow-sm">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-teal-600 text-xs text-white font-bold">C</span>
                        Counselling
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 rounded-xl transition">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-slate-800 text-xs text-teal-400 font-bold">N</span>
                        Notifications
                    </a>
                </nav>
            </div>

            <div class="p-4 m-4 bg-slate-800/60 rounded-2xl border border-slate-700/50 flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center font-bold text-white">S</div>
                <div>
                    <p class="text-sm font-semibold text-white">Student</p>
                    <p class="text-xs text-slate-400">Secure account</p>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <header class="bg-slate-50 border-b border-slate-200 px-8 py-5 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-slate-900">Counselling Request</h2>
                <div class="bg-slate-100 text-slate-600 px-4 py-1.5 rounded-full text-xs font-semibold shadow-sm border border-slate-200">
                    Student portal
                </div>
            </header>

            <div class="p-8 max-w-7xl mx-auto w-full space-y-8">
                @if(session('success'))
                    <div class="bg-teal-50 border border-teal-200 text-teal-800 px-5 py-4 rounded-xl text-sm font-medium shadow-sm flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <span class="text-xs bg-teal-200 text-teal-900 px-2 py-0.5 rounded font-bold">Saved to MySQL</span>
                    </div>
                @endif

                <div>
                    <h3 class="text-xl font-bold text-slate-900">Request private support</h3>
                    <p class="text-sm text-slate-500 mt-1">Complete each step before submitting your request.</p>

                    <div class="flex items-center max-w-2xl mt-6 relative">
                        <div class="absolute left-6 right-6 top-1/2 -translate-y-1/2 h-0.5 bg-slate-200 z-0"></div>
                        
                        <div class="flex flex-col items-center relative z-10 flex-1">
                            <div :class="step >= 1 ? 'bg-teal-600 text-white shadow-md' : 'bg-slate-200 text-slate-500'" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition">1</div>
                            <span class="text-xs font-medium text-slate-700 mt-2">Concern</span>
                        </div>
                        <div class="flex flex-col items-center relative z-10 flex-1">
                            <div :class="step >= 2 ? 'bg-teal-600 text-white shadow-md' : 'bg-slate-200 text-slate-500'" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition">2</div>
                            <span class="text-xs font-medium text-slate-700 mt-2">Consent</span>
                        </div>
                        <div class="flex flex-col items-center relative z-10 flex-1">
                            <div :class="step >= 3 ? 'bg-teal-600 text-white shadow-md' : 'bg-slate-200 text-slate-500'" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition">3</div>
                            <span class="text-xs font-medium text-slate-700 mt-2">Schedule</span>
                        </div>
                        <div class="flex flex-col items-center relative z-10 flex-1">
                            <div :class="step >= 4 ? 'bg-slate-900 text-white ring-4 ring-slate-200 shadow-md' : 'bg-slate-200 text-slate-500'" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition">4</div>
                            <span class="text-xs font-medium text-slate-400 mt-2">Review</span>
                        </div>
                    </div>
                </div>

                <form id="counsellingForm" action="{{ route('counselling.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    @csrf
                    
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
                        
                        <template x-if="step === 1">
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900">Step 1: Describe your concern</h4>
                                    <p class="text-xs text-slate-500 mt-1">Please provide details regarding your concern and optional goals.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Concern Description *</label>
                                    <textarea name="concern" rows="3" x-model="concern" required class="w-full rounded-xl border border-slate-300 p-3 text-sm focus:ring-2 focus:ring-teal-600 text-slate-800" placeholder="Describe what you are currently going through..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Counselling Goals (Optional)</label>
                                    <textarea name="goals" rows="2" x-model="goals" class="w-full rounded-xl border border-slate-300 p-3 text-sm focus:ring-2 focus:ring-teal-600 text-slate-800" placeholder="What do you hope to achieve from this session? (Optional)"></textarea>
                                </div>
                                <div class="pt-2 flex justify-end">
                                    <button type="button" :disabled="!concern.trim()" @click="if(concern.trim()) step = 2" :class="concern.trim() ? 'bg-teal-600 hover:bg-teal-700 cursor-pointer' : 'bg-slate-300 cursor-not-allowed'" class="text-white font-medium px-6 py-2.5 rounded-xl transition shadow-sm">
                                        Continue to Consent &rarr;
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="step === 2">
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900">Step 2: Informed Consent Agreement</h4>
                                    <p class="text-xs text-slate-500 mt-1">Please read the terms regarding privacy boundaries, confidentiality, and data protection.</p>
                                </div>

                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-700 space-y-3 max-h-64 overflow-y-auto leading-relaxed border-l-4 border-l-teal-600">
                                    <h5 class="font-bold text-slate-900 text-sm">MindLink Telkom University Support Platform</h5>
                                    
                                    <p class="font-semibold text-slate-800">1. Nature and Scope of Services</p>
                                    <p>The counselling services provided through MindLink offer short-term emotional, academic, and peer supportive counselling. MindLink is designed for educational and supportive purposes and is <strong>not an emergency hotline or clinical psychiatric service</strong>.</p>

                                    <p class="font-semibold text-slate-800">2. Confidentiality & Role Boundaries</p>
                                    <p>All information, session disclosures, and personal notes shared during counselling are strictly confidential between you and your assigned counsellor. University administrators can only view anonymized operational data and service metrics; they cannot view session notes or specific problem descriptions.</p>

                                    <p class="font-semibold text-slate-800">3. Exceptions to Confidentiality</p>
                                    <p>Confidentiality may be broken only under strict legal and safety conditions: (a) explicit risk or threat of severe physical harm to yourself or others, (b) reported abuse involving minors or vulnerable individuals, or (c) a formal order by a court of law.</p>

                                    <p class="font-semibold text-slate-800">4. Data Protection & Privacy Compliance</p>
                                    <p>Your personal data and counselling records are protected in compliance with <strong>Indonesia Law No. 27 of 2022 on Personal Data Protection (PDP Law)</strong>. Data is encrypted and accessible only to authorized counsellors for record-keeping and follow-up care.</p>
                                </div>

                                <label class="flex items-start space-x-3 cursor-pointer pt-2">
                                    <input type="checkbox" x-model="consentAgreed" class="mt-0.5 w-4 h-4 text-teal-600 rounded border-slate-300 focus:ring-teal-500">
                                    <span class="text-xs font-medium text-slate-800">I have carefully read, understood, and accept the Informed Consent Agreement and Privacy Boundaries. *</span>
                                </label>

                                <div class="pt-3 flex justify-between">
                                    <button type="button" @click="step = 1" class="border border-slate-300 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Back</button>
                                    <button type="button" :disabled="!consentAgreed" @click="if(consentAgreed) step = 3" :class="consentAgreed ? 'bg-teal-600 hover:bg-teal-700 cursor-pointer' : 'bg-slate-300 cursor-not-allowed'" class="text-white font-medium px-6 py-2.5 rounded-xl transition shadow-sm">
                                        Continue to Schedule &rarr;
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="step === 3">
                            <div class="space-y-5">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900">Choose an available time & format</h4>
                                    <p class="text-xs text-slate-500 mt-1">Select your preferred counsellor, format, date, and time slot.</p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Session Format *</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button type="button" @click="appointmentMode = 'In person'" :class="appointmentMode === 'In person' ? 'bg-teal-600 text-white shadow-sm' : 'border border-slate-300 text-slate-700 bg-white hover:bg-slate-50'" class="py-2.5 rounded-xl font-semibold text-sm transition">
                                            🏢 In Person
                                        </button>
                                        <button type="button" @click="appointmentMode = 'Online Meeting'" :class="appointmentMode === 'Online Meeting' ? 'bg-teal-600 text-white shadow-sm' : 'border border-slate-300 text-slate-700 bg-white hover:bg-slate-50'" class="py-2.5 rounded-xl font-semibold text-sm transition">
                                            💻 Online Meeting
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Select Counsellor *</label>
                                    <div class="space-y-2 max-h-36 overflow-y-auto pr-1">
                                        @foreach($counsellors as $c)
                                            <div @click="selectedCounsellorId = '{{ $c->counsellor_id }}'; selectedCounsellorName = '{{ $c->name }}'" 
                                                 :class="selectedCounsellorId == '{{ $c->counsellor_id }}' ? 'border-teal-600 bg-teal-50/50 ring-2 ring-teal-600/20' : 'border-slate-200 bg-slate-50 hover:border-slate-300'" 
                                                 class="border rounded-xl p-3 flex items-center justify-between cursor-pointer transition">
                                                <div class="flex items-center space-x-3 text-slate-800 font-medium">
                                                    <span class="text-teal-600 font-bold text-sm">{{ $c->name }}</span> 
                                                    <span class="text-slate-300">•</span> 
                                                    <span class="text-slate-600 text-xs">{{ $c->specialization }}</span>
                                                </div>
                                                <div :class="selectedCounsellorId == '{{ $c->counsellor_id }}' ? 'border-teal-600 bg-teal-600' : 'border-slate-300 bg-white'" class="w-4 h-4 rounded-full border flex items-center justify-center">
                                                    <div x-show="selectedCounsellorId == '{{ $c->counsellor_id }}'" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Select Date *</label>
                                    <div class="relative">
                                        <input type="date" x-model="selectedDate" min="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600">
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Select Time Slot *</label>
                                    <div class="grid grid-cols-4 gap-2">
                                        <template x-for="time in ['09:00', '10:00', '13:30', '15:00']">
                                            <button type="button" @click="selectedTime = time" :class="selectedTime === time ? 'bg-teal-600 text-white shadow-sm' : 'border border-teal-600 text-teal-700 bg-white hover:bg-teal-50'" class="py-2 rounded-xl font-semibold text-sm transition" x-text="time"></button>
                                        </template>
                                    </div>
                                </div>

                                <div class="pt-2 flex justify-between">
                                    <button type="button" @click="step = 2" class="border border-slate-300 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium">Back</button>
                                    <button type="button" :disabled="!selectedCounsellorId || !selectedDate" @click="if(selectedCounsellorId && selectedDate) step = 4" :class="(selectedCounsellorId && selectedDate) ? 'bg-teal-600 hover:bg-teal-700 cursor-pointer' : 'bg-slate-300 cursor-not-allowed'" class="text-white font-medium px-6 py-2.5 rounded-xl transition shadow-sm">
                                        Review Request &rarr;
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="step === 4">
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900">Step 4: Final Review</h4>
                                    <p class="text-xs text-slate-500 mt-1">Review your details before submitting your request.</p>
                                </div>

                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2.5 text-sm text-slate-700">
                                    <div>
                                        <span class="font-bold text-xs uppercase text-slate-400 block">Concern:</span>
                                        <p x-text="concern" class="mt-0.5 text-slate-900 font-medium"></p>
                                    </div>
                                    <template x-if="goals">
                                        <div>
                                            <span class="font-bold text-xs uppercase text-slate-400 block">Counselling Goals:</span>
                                            <p x-text="goals" class="mt-0.5 text-slate-900 font-medium"></p>
                                        </div>
                                    </template>
                                    <div>
                                        <span class="font-bold text-xs uppercase text-slate-400 block">Chosen Counsellor:</span>
                                        <p x-text="selectedCounsellorName" class="mt-0.5 text-teal-700 font-bold"></p>
                                    </div>
                                    <div>
                                        <span class="font-bold text-xs uppercase text-slate-400 block">Format & Schedule:</span>
                                        <p class="mt-0.5 text-slate-900 font-medium"><span x-text="appointmentMode"></span> — <span x-text="formattedDate"></span>, <span x-text="selectedTime"></span></p>
                                    </div>
                                    <div>
                                        <span class="font-bold text-xs uppercase text-slate-400 block">Consent Status:</span>
                                        <p class="mt-0.5 text-teal-700 font-bold">Accepted ✓</p>
                                    </div>
                                </div>

                                <!-- REAL FORM INPUTS (Ensures values are sent to Laravel) -->
                                <input type="hidden" name="concern" x-model="concern">
                                <input type="hidden" name="goals" x-model="goals">
                                <input type="hidden" name="counsellor_id" x-model="selectedCounsellorId">
                                <input type="hidden" name="appointment_mode" x-model="appointmentMode">
                                <input type="hidden" name="appointment_date" x-model="selectedDate">
                                <input type="hidden" name="appointment_time" x-model="selectedTime">

                                <div class="pt-4 flex justify-between">
                                    <button type="button" @click="step = 3" class="border border-slate-300 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium">Back</button>
                                    <button type="button" @click="showModal = true" class="bg-teal-600 hover:bg-teal-700 text-white font-medium px-6 py-2.5 rounded-xl transition shadow-sm cursor-pointer flex items-center space-x-2">
                                        <span>Confirm & Submit Request</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5 flex flex-col justify-between">
                        <div class="space-y-4">
                            <h4 class="text-lg font-bold text-slate-900">Your request</h4>
                            <div>
                                <p class="text-xs text-slate-400 font-medium uppercase">Concern</p>
                                <p class="text-sm font-medium text-slate-800 mt-0.5 truncate" x-text="concern || 'Pending input...'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium uppercase">Consent</p>
                                <p class="text-sm font-medium text-slate-800 mt-0.5" x-text="consentAgreed ? 'Accepted' : 'Pending agreement'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium uppercase">Format & Counsellor</p>
                                <p class="text-sm font-medium text-teal-700 mt-0.5"><span x-text="appointmentMode"></span> with <span x-text="selectedCounsellorName || 'None'"></span></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium uppercase">Schedule</p>
                                <p class="text-sm font-medium text-slate-800 mt-0.5" x-text="formattedDate ? formattedDate + ', ' + selectedTime : 'Select a date'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium uppercase">Status</p>
                                <p class="text-sm font-medium text-slate-800 mt-0.5" x-text="step === 4 ? 'Ready for submission' : 'Draft – not yet submitted'"></p>
                            </div>
                        </div>
                        <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 text-xs text-orange-800 font-medium">
                            <span x-text="step < 4 ? 'Review the request before submission.' : 'Ready for final submission.'"></span>
                        </div>
                    </div>
                </form>
            </div>
        </main>

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
                <div class="w-16 h-16 rounded-full bg-teal-50 border border-teal-100 text-teal-600 flex items-center justify-center mx-auto text-2xl shadow-sm">
                    💬
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-slate-900">Request Submitted for Review</h3>
                    <p class="text-xs text-slate-500 leading-relaxed px-2">
                        Your counselling request has been sent to <span class="font-bold text-teal-700" x-text="selectedCounsellorName"></span>. 
                        Our team will review your details shortly. We’ll notify you via your student portal and email once your schedule is confirmed!
                    </p>
                </div>

                <div class="bg-slate-50 border border-slate-200/70 rounded-2xl p-4 text-xs text-left space-y-1.5 text-slate-700">
                    <p><strong>Counsellor:</strong> <span x-text="selectedCounsellorName"></span></p>
                    <p><strong>Scheduled:</strong> <span x-text="formattedDate"></span> at <span x-text="selectedTime"></span></p>
                    <p><strong>Format:</strong> <span x-text="appointmentMode"></span></p>
                    <p><strong>Status:</strong> <span class="text-amber-600 font-semibold">Under Review</span></p>
                </div>

                <div class="pt-2 flex flex-col space-y-2">
                    <button type="button" @click="submitForm()" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3 rounded-xl shadow-md transition cursor-pointer">
                        Understand & Proceed
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
                step: 1,
                showModal: false,
                concern: '',
                goals: '',
                consentAgreed: false,
                appointmentMode: 'In person',
                selectedCounsellorId: '{{ $counsellors->first()->counsellor_id ?? "" }}',
                selectedCounsellorName: '{{ $counsellors->first()->name ?? "" }}',
                selectedDate: '{{ date("Y-m-d") }}',
                selectedTime: '10:00',
                
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
</body>
</html>