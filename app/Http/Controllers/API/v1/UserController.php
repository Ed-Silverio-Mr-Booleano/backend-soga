<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Update user profile data.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'cover_photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB Max
            'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB Max
            'bio' => 'string|max:1000',
            'interesses' => 'string|max:1000',
            'gender' => 'string|max:10',
            'birthdate' => 'date',
            'phone_number' => 'string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            if ($request->hasFile('cover_photo')) {
                // Remove o arquivo anterior, se existir
                if ($user->cover_photo) {
                    Storage::disk('public')->delete($user->cover_photo);
                }
                $coverPhotoPath = $request->file('cover_photo')->store('cover_photos', 'public');
                $user->cover_photo = $coverPhotoPath;
            }

            if ($request->hasFile('avatar')) {
                // Remove o arquivo anterior, se existir
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            $user->update($request->except(['cover_photo', 'avatar']));

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Profile updated successfully',
                'data' => $user
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search users by interests.
     */
    public function searchByInterests(Request $request)
    {
        $interests = $request->query('interesses');

        $users = User::where('interesses', 'like', '%' . $interests . '%')->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $users
        ], Response::HTTP_OK);
    }

    /**
     * Search users by full name.
     */
    public function searchByName(Request $request)
    {
        $name = $request->query('name');

        $users = User::where('name', 'like', '%' . $name . '%')->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $users
        ], Response::HTTP_OK);
    }

    /**
     * Get online users.
     */
    public function getOnlineUsers()
    {
        $onlineUsers = User::whereNotNull('last_online_at')
            ->where('last_online_at', '>', now()->subMinutes(5)) // assuming 5 minutes of inactivity means offline
            ->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $onlineUsers
        ], Response::HTTP_OK);
    }
}
