<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counsellor Dashboard - MindLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden" x-data="counsellorDashboard()">
        <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between hidden md:flex">
            <div>
                <div class="p-6 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-teal-600 flex items-center justify-center font-bold text-lg text-white">M</div>
                    <div>
                        <h1 class="font-bold text-lg tracking-tight">MindLink</h1>
                        <p class="text-xs text-slate-400">Counsellor Portal</p>
                    </div>
                </div>

                <nav class="mt-4 px-4 space-y-1">
                    <a href="{{ route('counsellor.dashboard') }}" class="flex items-center px-4 py-3 bg-slate-800 text-white rounded-xl font-medium shadow-sm">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-teal-600 text-xs text-white font-bold">D</span>
                        Requests Dashboard
                    </a>
                    <a href="{{ route('counselling.request') }}" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 rounded-xl transition">
                        <span class="w-6 h-6 mr-3 flex items-center justify-center rounded-full bg-slate-800 text-xs text-orange-400 font-bold">S</span>
                        Student Portal &rarr;
                    </a>
                </nav>
            </div>

            <div class="p-4 m-4 bg-slate-800/60 rounded-2xl border border-slate-700/50 flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center font-bold text-white">C</div>
                <div>
                    <p class="text-sm font-semibold text-white">Counsellor Panel</p>
                    <p class="text-xs text-slate-400">Authorized Access</p>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <header class="bg-white border-b border-slate-200 px-8 py-5 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Student Counselling Requests</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Review incoming student concerns, goals, and determine next steps.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-teal-50 text-teal-800 border border-teal-200 px-4 py-1.5 rounded-full text-xs font-semibold shadow-sm">
                        Total Requests: {{ $requests->count() }}
                    </div>
                </div>
            </header>

            <div class="p-8 max-w-7xl mx-auto w-full space-y-6">
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-3.5 rounded-xl text-sm shadow-sm">
                        <ul class="list-disc pl-5 text-xs space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('success'))
                    <div class="bg-teal-50 border border-teal-200 text-teal-800 px-5 py-3.5 rounded-xl text-sm font-medium shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-2">
                    <template x-for="tab in ['All', 'Under Review', 'Approved', 'Declined']">
                        <button type="button"
                            @click="filterStatus = tab"
                            :class="filterStatus === tab ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                            class="px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer"
                            x-text="tab">
                        </button>
                    </template>
                </div>

                @if($requests->isEmpty())
                    <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center space-y-3 shadow-sm">
                        <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto text-2xl">📭</div>
                        <h3 class="text-lg font-bold text-slate-800">No requests found</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">When students submit requests through the portal, they will appear here instantly for review.</p>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider font-semibold">
                                        <th class="p-4"># ID</th>
                                        <th class="p-4">Counsellor</th>
                                        <th class="p-4">Concern Summary</th>
                                        <th class="p-4">Schedule</th>
                                        <th class="p-4">Status</th>
                                        <th class="p-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                    @foreach($requests as $req)
                                        <tr x-show="filterStatus === 'All' || filterStatus === '{{ $req->status ?? 'Under Review' }}'" class="hover:bg-slate-50/60 transition">
                                            <td class="p-4 font-bold text-slate-900">#{{ $req->request_id }}</td>
                                            <td class="p-4 font-semibold text-teal-700">{{ $req->counsellor_name }}</td>
                                            <td class="p-4 max-w-xs">
                                                <p class="font-medium text-slate-900 truncate">{{ $req->concern }}</p>
                                                @if($req->goals)
                                                    <p class="text-xs text-slate-500 truncate">Goals: {{ $req->goals }}</p>
                                                @endif
                                                @if($req->counsellor_notes)
                                                    <p class="text-[11px] text-teal-700 truncate mt-0.5">Note: {{ $req->counsellor_notes }}</p>
                                                @endif
                                            </td>
                                            <td class="p-4 text-xs space-y-0.5">
                                                <p class="font-semibold text-slate-800">{{ $req->appointment_date }} at {{ $req->appointment_time }}</p>
                                                <span class="inline-block bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-medium">{{ $req->appointment_mode }}</span>
                                            </td>
                                            <td class="p-4">
                                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold border
                                                    {{ $req->status === 'Approved' ? 'bg-teal-50 text-teal-700 border-teal-200' : '' }}
                                                    {{ $req->status === 'Declined' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                                    {{ !in_array($req->status, ['Approved', 'Declined']) ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}">
                                                    {{ $req->status ?? 'Under Review' }}
                                                </span>
                                            </td>
                                            <td class="p-4 text-right">
                                                <button type="button" 
                                                    @click="openModal({{ Js::from($req) }})"
                                                    class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-xl transition cursor-pointer">
                                                    {{ in_array($req->status, ['Approved', 'Declined']) ? 'View Details' : 'Review & Assess' }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </main>

            <div x-show="selectedRequest" 
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto py-8" 
                style="display: none;">
            
            <div @click.away="selectedRequest = null" class="bg-white rounded-3xl border border-slate-200 max-w-lg w-full p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto my-auto">
                <div class="flex justify-between items-start border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-teal-600">Request #<span x-text="selectedRequest?.request_id"></span></span>
                        <h3 class="text-xl font-bold text-slate-900 mt-0.5">Student Concern Assessment</h3>
                    </div>
                    <button @click="selectedRequest = null" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                </div>

                <div class="space-y-4 text-sm text-slate-700">
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 space-y-3">
                        <div>
                            <span class="text-xs font-bold uppercase text-slate-400 block">Full Concern Description</span>
                            <p class="text-slate-900 mt-1 whitespace-pre-line" x-text="selectedRequest?.concern"></p>
                        </div>
                        <template x-if="selectedRequest?.goals">
                            <div class="pt-2 border-t border-slate-200/60">
                                <span class="text-xs font-bold uppercase text-slate-400 block">Student Goals</span>
                                <p class="text-slate-800 mt-1 whitespace-pre-line" x-text="selectedRequest?.goals"></p>
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs bg-teal-50/50 border border-teal-100 rounded-xl p-3.5">
                        <div>
                            <span class="text-slate-400 font-semibold uppercase block">Requested Slot</span>
                            <span class="font-bold text-slate-800" x-text="selectedRequest?.appointment_date + ' • ' + selectedRequest?.appointment_time"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold uppercase block">Session Mode</span>
                            <span class="font-bold text-teal-700" x-text="selectedRequest?.appointment_mode"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                            Counsellor Assessment / Referral Notes
                            <span class="text-slate-400 normal-case font-normal">(Required if declining)</span>
                        </label>
                        <textarea rows="3"
                            x-model="notes"
                            @input="modalError = ''"
                            :disabled="isFinalized()"
                            class="w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-800 focus:ring-2 focus:ring-teal-600 disabled:bg-slate-100 disabled:text-slate-500"
                            placeholder="Write session preparation notes or referral instructions for the student..."></textarea>
                        <p x-show="modalError" x-text="modalError" class="text-xs font-semibold text-red-600 mt-2 bg-red-50 border border-red-200 px-3 py-2 rounded-lg"></p>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <template x-if="isFinalized()">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-semibold text-slate-500">Status finalized as <strong x-text="selectedRequest?.status"></strong>.</span>
                            <button type="button" @click="selectedRequest = null" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold">Close</button>
                        </div>
                    </template>

                    <template x-if="!isFinalized()">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 mb-3">Determine Next Steps:</p>
                            <div class="flex items-center justify-end space-x-3">
                                <form x-ref="declineForm" :action="'/counsellor/requests/' + selectedRequest?.request_id + '/status'" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Declined">
                                    <input type="hidden" name="counsellor_notes" :value="notes">
                                    <button type="button" @click="submitDecline()" class="px-4 py-2.5 rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 text-xs font-bold transition cursor-pointer">
                                        Decline / Refer
                                    </button>
                                </form>

                                <form :action="'/counsellor/requests/' + selectedRequest?.request_id + '/status'" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Approved">
                                    <input type="hidden" name="counsellor_notes" :value="notes">
                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition cursor-pointer">
                                        Approve & Confirm Session
                                    </button>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function counsellorDashboard() {
            return {
                filterStatus: 'All',
                selectedRequest: null,
                notes: '',
                modalError: '',

                openModal(req) {
                    this.selectedRequest = req;
                    this.notes = req.counsellor_notes || '';
                    this.modalError = '';
                },

                isFinalized() {
                    return ['Approved', 'Declined'].includes(this.selectedRequest?.status);
                },

                submitDecline() {
                    if (!this.notes.trim()) {
                        this.modalError = 'Please provide assessment or referral notes before declining a request.';
                        return;
                    }
                    this.$refs.declineForm.submit();
                }
            }
        }
    </script>
</body>
</html>