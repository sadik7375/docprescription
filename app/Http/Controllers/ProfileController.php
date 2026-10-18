<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $user->load('doctorProfile');

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'doctorProfile' => $user->doctorProfile,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->role === 'doctor') {
            // Process degrees & experiences
            $degrees = $validated['degrees'] ?? null;
            if (is_string($degrees)) {
                $degrees = array_values(array_filter(array_map('trim', explode(',', $degrees))));
            }

            $experiences = $validated['experiences'] ?? null;
            if (is_string($experiences)) {
                $experiences = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $experiences))));
            }

            $user->doctorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'title' => $validated['title'] ?? 'Dr.',
                    'specialty' => $validated['specialty'] ?? null,
                    'bmdc_reg_no' => $validated['bmdc_reg_no'] ?? null,
                    'degrees' => $degrees,
                    'experiences' => $experiences,
                    'signature' => $validated['signature'] ?? null,
                ]
            );
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
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
