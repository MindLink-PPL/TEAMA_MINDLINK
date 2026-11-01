<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    /**
     * Display a listing of users, their roles, and associated permissions.
     */
    public function index(Request $request): View
    {
        $query = User::with('role.permissions');

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('slug', $request->input('role'));
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::with('permissions')->get();

        // Statistics
        $totalUsers = User::count();
        $adminCount = User::whereHas('role', fn($q) => $q->where('slug', 'admin'))->count();
        $managerCount = User::whereHas('role', fn($q) => $q->where('slug', 'manager'))->count();
        $standardUserCount = User::whereHas('role', fn($q) => $q->where('slug', 'user'))->count();

        return view('admin.users.index', compact(
            'users',
            'roles',
            'totalUsers',
            'adminCount',
            'managerCount',
            'standardUserCount'
        ));
    }

    /**
     * Update the role for a specific user.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $newRole = Role::findOrFail($validated['role_id']);

        // Safeguard: Prevent admin from demoting themselves
        if ($user->id === auth()->id() && $user->role_id != $newRole->id) {
            return back()->with('error', 'Security protection: You cannot alter your own Administrator role to prevent lockout.');
        }

        // Safeguard: Ensure at least one administrator exists in the system
        if ($user->isAdmin() && $newRole->slug !== 'admin') {
            $adminCount = User::whereHas('role', fn($q) => $q->where('slug', 'admin'))->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Action denied: The system must always have at least one active Administrator.');
            }
        }

        $previousRoleName = $user->role ? $user->role->name : 'None';
        $user->role_id = $newRole->id;
        $user->save();

        return back()->with('success', "Updated role for {$user->name} from {$previousRoleName} to {$newRole->name}. Permissions have been updated.");
    }
}

