<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CounsellorVerificationController extends Controller
{
    // Show counsellor applications
    public function index()
    {
        $counsellors = User::where('role', 'counsellor')
            ->whereIn('verification_status', [
                'pending',
                'verified',
                'rejected'
            ])
            ->latest()
            ->get();

        return view('admin.counsellors.index', compact('counsellors'));
    }

    // View uploaded certificate
    public function certificate(User $user)
    {
        abort_unless($user->role === 'counsellor', 404);

        abort_unless($user->certificate_path, 404);

        abort_unless(
            Storage::exists($user->certificate_path),
            404
        );

        return Storage::download($user->certificate_path);
    }

    // Approve counsellor
    public function approve(User $user)
    {
        abort_unless($user->role === 'counsellor', 404);

        $user->verification_status = 'verified';
        $user->rejection_reason = null;
        $user->save();

        return back()->with(
            'success',
            'Counsellor successfully verified!'
        );
    }

    // Reject counsellor
    public function reject(Request $request, User $user)
    {
        abort_unless($user->role === 'counsellor', 404);

        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:1000'
            ],
        ]);

        $user->verification_status = 'rejected';
        $user->rejection_reason = $validated['rejection_reason'];
        $user->save();

        return back()->with(
            'success',
            'Counsellor application rejected.'
        );
    }
}
