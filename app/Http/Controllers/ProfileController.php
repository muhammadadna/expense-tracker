<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's theme preference.
     */
    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark'],
        ]);

        $request->user()->update($validated);

        return back()->with('status', 'theme-updated');
    }

    /**
     * Generate a pairing token for connecting Telegram.
     */
    public function generateTelegramToken(Request $request): RedirectResponse
    {
        $token = 'EXP-' . strtoupper(Str::random(6));

        $request->user()->update([
            'telegram_connect_token' => $token,
            'telegram_connect_token_expires_at' => now()->addMinutes(15),
        ]);

        return back()->with('status', 'telegram-token-generated');
    }

    /**
     * Disconnect Telegram account.
     */
    public function disconnectTelegram(Request $request): RedirectResponse
    {
        $request->user()->update([
            'telegram_chat_id' => null,
            'telegram_connect_token' => null,
            'telegram_connect_token_expires_at' => null,
        ]);

        return back()->with('status', 'telegram-disconnected');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
