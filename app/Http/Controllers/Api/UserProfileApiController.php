<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Http\Requests\UserProfileRequest;
use App\Http\Resources\UserProfileResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class UserProfileApiController extends Controller
{
    /**
     * Get the authenticated user's profile
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile not found. Please create your profile first.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new UserProfileResource($user->profile)
        ]);
    }

    /**
     * Update the authenticated user's profile
     */
    public function update(UserProfileRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        try {
            // Handle avatar upload if present
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->profile && $user->profile->avatar) {
                    Storage::disk('public')->delete($user->profile->avatar);
                }
                $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            // Update or create profile
            $profile = $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $data
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => new UserProfileResource($profile)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile. Please try again.'
            ], 500);
        }
    }
}
