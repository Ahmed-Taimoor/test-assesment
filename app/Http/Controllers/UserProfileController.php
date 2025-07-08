<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserProfileRequest;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', UserProfile::class);

        $profiles = UserProfile::with('user')
            ->paginate(10);

        return Inertia::render('Admin/UserProfiles/Index', [
            'profiles' => $profiles,
        ]);
    }

    public function show(User $user)
    {
        // If profile doesn't exist, redirect to create profile page
        if (!$user->profile) {
            if ($user->id !== auth()->id() && !auth()->user()->isAdmin()) {
                abort(403, 'Profile not found');
            }
            
            // If it's the current user or admin, redirect to profile creation
            return redirect()->route('profile.edit', $user);
        }

        $this->authorize('view', $user->profile);
        $user->load('profile');

        return Inertia::render('Profile/Show', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    public function edit(User $user)
    {
        // Allow editing if the user is creating a new profile
        if (!$user->profile) {
            // Only the user themselves or admin can create a profile
            if ($user->id !== auth()->id() && !auth()->user()->isAdmin()) {
                abort(403, 'Unauthorized action.');
            }
            
            return Inertia::render('Profile/Edit', [
                'user' => $user,
                'profile' => null,
            ]);
        }

        // For existing profiles, check update authorization
        $this->authorize('update', $user->profile);
        $user->load('profile');

        return Inertia::render('Profile/Edit', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    public function store(UserProfileRequest $request)
    {
        $user = auth()->user();

        $profileData = $request->validated();

        if ($request->hasFile('avatar')) {
            $profileData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return redirect()->route('profile.show', $user)
            ->with('success', 'Profile updated successfully.');
    }

    public function update(UserProfileRequest $request, User $user)
    {
        // For existing profiles, check update authorization
        if ($user->profile) {
            $this->authorize('update', $user->profile);
        } 
        // For new profiles, only allow if it's the user themselves or an admin
        elseif ($user->id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $profileData = $request->validated();

        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists
            if ($user->profile && $user->profile->avatar) {
                Storage::disk('public')->delete($user->profile->avatar);
            }
            $profileData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Update or create the profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return redirect()->route('profile.show', $user)
            ->with('success', 'Profile updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user->profile);

        if ($user->profile) {
            // Delete avatar file
            if ($user->profile->avatar) {
                Storage::disk('public')->delete($user->profile->avatar);
            }

            $user->profile->delete();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Profile deleted successfully.');
    }
}
