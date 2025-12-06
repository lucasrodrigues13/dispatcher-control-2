<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        \Log::info('Profile update started', [
            'user_id' => $user->id,
            'has_photo' => $request->hasFile('photo'),
            'has_logo' => $request->hasFile('logo'),
            'is_owner' => $user->is_owner,
            'all_files' => $request->allFiles(),
        ]);

        // Handle photo upload FIRST (before fill, so we can set photo manually)
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            
            \Log::info('Photo file received', [
                'user_id' => $user->id,
                'file_name' => $photo->getClientOriginalName(),
                'file_size' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'extension' => $photo->getClientOriginalExtension(),
            ]);
            
            // Validate photo
            try {
                $request->validate([
                    'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Photo validation failed', [
                    'errors' => $e->errors(),
                ]);
                return Redirect::route('profile.edit')->withErrors($e->errors());
            }

            // Create user-specific directory
            $userDir = 'profile-photos/user-' . $user->id;
            
            // Create directory if it doesn't exist
            $storagePath = storage_path('app/public/' . $userDir);
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
                \Log::info('Created directory', ['path' => $storagePath]);
            }

            // Delete old photo if exists
            if ($user->photo) {
                $oldPhotoPath = storage_path('app/public/' . $user->photo);
                if (file_exists($oldPhotoPath)) {
                    @unlink($oldPhotoPath);
                    \Log::info('Deleted old photo', ['path' => $oldPhotoPath]);
                }
            }

            // Generate unique filename
            $filename = 'photo-' . time() . '.' . $photo->getClientOriginalExtension();
            
            // Store photo using Laravel's storage
            // storeAs returns path like: public/profile-photos/user-1/photo-123.jpg
            $storedPath = $photo->storeAs('public/' . $userDir, $filename);
            
            \Log::info('Photo stored', [
                'stored_path' => $storedPath,
                'full_path' => storage_path('app/' . $storedPath),
                'file_exists' => file_exists(storage_path('app/' . $storedPath)),
            ]);
            
            // Save photo path (relative to storage/app/public)
            // Remove 'public/' prefix from stored path
            // storedPath: "public/profile-photos/user-1/photo-123.jpg"
            // user->photo: "profile-photos/user-1/photo-123.jpg"
            $user->photo = preg_replace('/^public\//', '', $storedPath);
            
            \Log::info('Photo path saved to user', [
                'user_id' => $user->id,
                'photo_path' => $user->photo,
                'asset_path' => asset('storage/' . $user->photo),
            ]);
        } else {
            \Log::info('No photo file in request', [
                'user_id' => $user->id,
                'request_keys' => array_keys($request->all()),
            ]);
        }

        // Handle logo upload (only for dispatcher owner)
        if ($user->isOwner() && $request->hasFile('logo')) {
            $logo = $request->file('logo');
            
            \Log::info('Logo file received', [
                'user_id' => $user->id,
                'file_name' => $logo->getClientOriginalName(),
                'file_size' => $logo->getSize(),
                'mime_type' => $logo->getMimeType(),
                'extension' => $logo->getClientOriginalExtension(),
            ]);

            // Validate logo
            try {
                $request->validate([
                    'logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 2MB max
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Logo validation failed', [
                    'errors' => $e->errors(),
                ]);
                return Redirect::route('profile.edit')->withErrors($e->errors());
            }

            // Create user-specific directory for logos
            $logoDir = 'logos/user-' . $user->id;
            
            // Create directory if it doesn't exist
            $storagePath = storage_path('app/public/' . $logoDir);
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
                \Log::info('Created logo directory', ['path' => $storagePath]);
            }

            // Delete old logo if exists
            if ($user->logo) {
                $oldLogoPath = storage_path('app/public/' . $user->logo);
                if (file_exists($oldLogoPath)) {
                    @unlink($oldLogoPath);
                    \Log::info('Deleted old logo', ['path' => $oldLogoPath]);
                }
            }

            // Generate unique filename
            $filename = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();
            
            // Store logo using Laravel's storage
            $storedPath = $logo->storeAs('public/' . $logoDir, $filename);
            
            \Log::info('Logo stored', [
                'stored_path' => $storedPath,
                'full_path' => storage_path('app/' . $storedPath),
                'file_exists' => file_exists(storage_path('app/' . $storedPath)),
            ]);
            
            // Save logo path (relative to storage/app/public)
            $user->logo = preg_replace('/^public\//', '', $storedPath);
            
            \Log::info('Logo path saved to user', [
                'user_id' => $user->id,
                'logo_path' => $user->logo,
                'asset_path' => asset('storage/' . $user->logo),
            ]);
        }

        // Update basic fields (name, email) AFTER photo handling
        $validated = $request->validated();
        // Remove photo and logo from validated data since we handle them separately
        unset($validated['photo']);
        unset($validated['logo']);
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        
        \Log::info('User saved', [
            'user_id' => $user->id,
            'photo' => $user->photo,
            'is_dirty' => $user->isDirty(),
        ]);
        
        // Refresh user to get updated photo
        $user->refresh();

        return Redirect::route('profile.edit')->with('success', 'Profile updated successfully.');
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

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required'],
            'password'         => ['required','string','min:6','confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        return back()->with('success', 'Password updated successfully.');

    }
}
