<?php

namespace App\Http\Controllers;

use App\Models\InformedConsent;
use App\Models\User;
use Illuminate\Http\Request;

class InformedConsentController extends Controller
{
    public function index()
    {
        $consents = InformedConsent::with('user')->latest('agreed_at')->get();
        return view('admin-consents', compact('consents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'is_agreed' => 'accepted',
        ], [
            'is_agreed.accepted' => 'You must read and agree to the data usage terms before proceeding.',
        ]);

        $userId = auth()->id() ?? User::value('id') ?? 1;

        InformedConsent::updateOrCreate(
            [
                'user_id' => $userId,
                'policy_version' => InformedConsent::CURRENT_VERSION,
            ],
            [
                'agreed_at' => now(),
            ]
        );

        return back()->with('success', 'Success! Informed consent data has been saved to the database.');
    }
}
