<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
            User &amp; Role Management
        </h1>
    </x-slot>

    <div class="space-y-6">
        <!-- Top Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Role &amp; Access Control</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    View active platform accounts, inspect assigned permissions, and update user roles.
                </p>
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to Overview</span>
                </a>
            </div>
        </div>

        <!-- Metric Cards (MindLink Style) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-slate-500">Total accounts</div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $totalUsers }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
                    All
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-[#fef4ea] border border-[#fed7aa] shadow-2xs flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-[#9a3412]">Administrators</div>
                    <div class="text-3xl font-extrabold text-[#ea580c] mt-1">{{ $adminCount }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#ea580c] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    A
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-[#ebf8f3] border border-[#bbf7d0] shadow-2xs flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-[#065f46]">Counsellors / Staff</div>
                    <div class="text-3xl font-extrabold text-[#059669] mt-1">{{ $managerCount }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#0d9488] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    C
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-slate-500">Students</div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $standardUserCount }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
                    S
                </div>
            </div>
        </div>

        <!-- Directory & Role Switcher Table (MindLink Design) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-5 border-b border-slate-100 bg-[#f8fafc]/80">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                    <div class="flex-1 w-full flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search by name or email address..."
                                class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm bg-white focus:ring-2 focus:ring-[#0d9488] focus:border-[#0d9488] transition"
                            >
                        </div>

                        <div class="w-full sm:w-48">
                            <select
                                name="role"
                                class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs sm:text-sm bg-white focus:ring-2 focus:ring-[#0d9488] focus:border-[#0d9488] transition"
                            >
                                <option value="">All Roles</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->slug }}" {{ request('role') == $r->slug ? 'selected' : '' }}>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#0d9488] hover:bg-[#0f766e] text-white text-xs font-bold rounded-xl transition shadow-2xs">
                            Filter
                        </button>
                        @if (request()->hasAny(['search', 'role']))
                            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 border border-slate-200 text-slate-600 text-xs font-medium rounded-xl hover:bg-slate-100 transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-[#f8fafc] text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th scope="col" class="py-4 px-6">User Account</th>
                            <th scope="col" class="py-4 px-6">Current Role</th>
                            <th scope="col" class="py-4 px-6">Granted Permissions</th>
                            <th scope="col" class="py-4 px-6 text-center">Change Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-[#f8fafc]/60 transition">
                                <!-- User Information -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full {{ $user->isAdmin() ? 'bg-[#ea580c]' : ($user->isCounsellor() ? 'bg-[#0d9488]' : 'bg-slate-200 text-slate-700') }} text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 flex items-center gap-2 text-sm">
                                                {{ $user->name }}
                                                @if ($user->id === Auth::id())
                                                    <span class="text-[10px] px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full font-bold">You</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role Badge (MindLink Palette) -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    @php
                                        $roleSlug = $user->role?->slug ?? 'none';
                                    @endphp
                                    @if ($roleSlug === 'admin')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#fef4ea] text-[#ea580c] border border-[#fed7aa]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#ea580c] me-1.5"></span>
                                            Administrator
                                        </span>
                                    @elseif ($roleSlug === 'counsellor' || $roleSlug === 'manager')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#ebf8f3] text-[#059669] border border-[#bbf7d0]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#059669] me-1.5"></span>
                                            {{ $user->role?->name ?? 'Counsellor' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 me-1.5"></span>
                                            {{ $user->role?->name ?? 'Student' }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Permissions Granted -->
                                <td class="py-4 px-6">
                                    <div class="flex flex-wrap gap-1.5 max-w-md">
                                        @if ($user->isAdmin())
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md bg-[#fef4ea] text-[#ea580c] border border-[#fed7aa]">
                                                <svg class="w-3 h-3 text-[#ea580c]" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Full System Administration (All privileges)
                                            </span>
                                        @elseif ($user->role && $user->role->permissions->isNotEmpty())
                                            @foreach ($user->role->permissions as $p)
                                                <span class="inline-flex items-center text-[11px] font-medium px-2 py-0.5 rounded-md bg-[#f8fafc] text-slate-700 border border-slate-200" title="{{ $p->description }}">
                                                    {{ $p->name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-xs text-slate-400 italic">No permissions assigned</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Change Role Action -->
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    @if ($user->id === Auth::id())
                                        <span class="text-xs text-slate-400 italic">
                                            Locked (Self)
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="flex items-center justify-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select
                                                name="role_id"
                                                class="text-xs py-1.5 px-2.5 border border-slate-200 rounded-lg focus:ring-[#0d9488] focus:border-[#0d9488] bg-white transition"
                                                onchange="if(confirm('Change role for {{ addslashes($user->name) }} to ' + this.options[this.selectedIndex].text + '? This immediately updates their granted permissions.')) { this.form.submit(); } else { this.value = '{{ $user->role_id }}'; }"
                                            >
                                                @foreach ($roles as $r)
                                                    <option value="{{ $r->id }}" {{ $user->role_id == $r->id ? 'selected' : '' }}>
                                                        {{ $r->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button
                                                type="submit"
                                                class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-slate-100 hover:bg-[#0d9488] hover:text-white text-slate-700 transition border border-slate-200 hover:border-[#0d9488]"
                                            >
                                                Update
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-slate-400">
                                    No accounts found matching your query.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="p-4 border-t border-slate-100 bg-[#f8fafc]/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Role & Permissions Specification Matrix (Figma MindLink Palette) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6">
            <div class="mb-5">
                <h3 class="font-bold text-slate-900 text-lg">System Role &amp; Permission Matrix</h3>
                <p class="text-xs text-slate-500 mt-0.5">Reference specification describing privileges attached to each institutional role.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ($roles as $specRole)
                    <div class="p-5 rounded-2xl border {{ $specRole->slug === 'admin' ? 'border-[#fed7aa] bg-[#fef4ea]/30' : ($specRole->slug === 'counsellor' || $specRole->slug === 'manager' ? 'border-[#bbf7d0] bg-[#ebf8f3]/30' : 'border-slate-200 bg-[#f8fafc]/50') }}">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-slate-900">{{ $specRole->name }}</h4>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $specRole->slug === 'admin' ? 'bg-[#fef4ea] text-[#ea580c]' : ($specRole->slug === 'counsellor' || $specRole->slug === 'manager' ? 'bg-[#ebf8f3] text-[#059669]' : 'bg-slate-200 text-slate-700') }}">
                                {{ $specRole->slug }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mb-4 min-h-[36px]">{{ $specRole->description }}</p>

                        <div class="space-y-2 pt-3 border-t {{ $specRole->slug === 'admin' ? 'border-[#fed7aa]' : ($specRole->slug === 'counsellor' || $specRole->slug === 'manager' ? 'border-[#bbf7d0]' : 'border-slate-200') }}">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Assigned Permissions:</span>
                            @if ($specRole->slug === 'admin')
                                <div class="flex items-center gap-1.5 text-xs font-bold text-[#ea580c]">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Full Administrative Clearance (All)
                                </div>
                            @endif

                            <ul class="space-y-1.5 text-xs text-slate-700">
                                @foreach ($specRole->permissions as $p)
                                    <li class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-[#059669] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="font-medium text-slate-800">{{ $p->name }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
