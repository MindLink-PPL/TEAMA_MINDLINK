<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InformedConsent;

class InformedConsentController extends Controller
{
    public function index()
    {
        $consents = InformedConsent::all();
        return view('admin-consents', compact('consents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'is_agreed' => 'accepted',
        ], [
            'is_agreed.accepted' => 'You must read and agree to the data usage terms before proceeding.',
        ]);

        $consent = new InformedConsent();
        $consent->is_agreed = true;
        $consent->agreed_at = now();
        $consent->save();

        return back()->with('success', 'Success! Informed consent data has been saved to the database.');
    }
}