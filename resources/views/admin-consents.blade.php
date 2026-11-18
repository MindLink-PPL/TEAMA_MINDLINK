<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-[#0f2a4a] tracking-tight">
            Reports &amp; Consents
        </h1>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-2xs">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-[#0f2a4a]">Student Consent Records</h2>
                <p class="text-xs text-slate-500 mt-1">Read-only overview of informed consent agreements accepted before counselling sessions.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] border-b border-slate-200 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">Student ID</th>
                            <th class="py-3 px-4">Consent Status</th>
                            <th class="py-3 px-4">Agreed At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($consents as $consent)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-semibold text-[#0f2a4a]">{{ $consent->id }}</td>
                            <td class="py-3 px-4">{{ $consent->student_id ?? 'N/A' }}</td>
                            <td class="py-3 px-4">
                                @if($consent->is_agreed)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#e6f5f0] text-[#1c9c8c]">Agreed</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-600">Pending</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500">{{ $consent->agreed_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
