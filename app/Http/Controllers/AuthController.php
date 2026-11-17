<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Show login page
    public function showLogin()
    {
        return view('auth.login');
    }


    // Show counsellor registration page
    public function showCounsellorRegister()
    {
        return view('auth.counsellorregister');
    }

    // Process counsellor registration
    public function registerCounsellor(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'certificate' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048'
            ],
        ]);

        // Store certificate privately
        $certificatePath = $request->file('certificate')
            ->store('counsellor-certificates');

        // Create counsellor account
        $user = new User();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = 'counsellor';
        $user->certificate_path = $certificatePath;
        $user->verification_status = 'pending';
        $user->save();

        // Log in the newly registered counsellor
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('counsellor.verification');
    }

    // Show counsellor verification status
    public function counsellorVerificationStatus()
    {
        $user = Auth::user();

        return view('counsellor.verification', compact('user'));
    }


    // Process login
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
        'role' => ['required', 'in:student,counsellor,admin'],
    ]);

    $selectedRole = $credentials['role'];

    if (Auth::attempt([
        'email' => $credentials['email'],
        'password' => $credentials['password'],
    ])) {

        $request->session()->regenerate();

        $user = Auth::user();

        // Verify that the selected role matches the account
        if ($user->role !== $selectedRole) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your account does not belong to the selected role.',
            ])->onlyInput('email', 'role');
        }

        if ($user->role === 'admin') {
    return redirect()->route('admin.dashboard');
}

if ($user->role === 'counsellor') {

    if ($user->verification_status !== 'verified') {
        return redirect()->route('counsellor.verification');
    }

    return redirect()->route('counsellor.dashboard');
}

        return redirect()->route('student.appointments');
    }

    return back()->withErrors([
        'email' => 'Email or password is incorrect.',
    ])->onlyInput('email', 'role');
}

    // Show registration page
    public function showRegister()
    {
        return view('auth.register');
    }

    // Process registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('student.appointments');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
