<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FamilyController extends Controller
{
    public function index()
    {
        return view('family.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $family = Family::create([
            'name' => $request->name,
            'family_code' => strtoupper(Str::random(8)),
        ]);

        $request->user()->update(['family_id' => $family->id]);

        return redirect()->route('dashboard')->with('success', 'Family created successfully!');
    }

    public function join(Request $request)
    {
        $request->validate([
            'family_code' => 'required|string|exists:families,family_code',
        ]);

        $family = Family::where('family_code', $request->family_code)->first();

        $request->user()->update(['family_id' => $family->id]);

        return redirect()->route('dashboard')->with('success', 'Joined family successfully!');
    }

    public function show()
    {
        $user = Auth::user();
        $family = $user->family;
        $members = $family->users;

        return view('family.show', compact('family', 'members'));
    }
}
