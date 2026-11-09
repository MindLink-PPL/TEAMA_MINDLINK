<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InformedConsent;

class InformedConsentController extends Controller
{
    public function store(Request $request)
    {
        $consent = new InformedConsent();
        $consent->is_agreed = $request->has('is_agreed') ? true : false;
        $consent->agreed_at = now();
        $consent->save();

        return back()->with('success', 'Success! Informed consent data has been saved to the database.');
    }
}