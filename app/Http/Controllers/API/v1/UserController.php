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
use App\Models\Friendship;

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
        try {
            $userId = Auth::id();
            $interests = $request->query('interesses');

            // Obter IDs de usuários com amizades pendentes envolvendo o usuário logado
            $pendingFriendships = Friendship::where(function ($query) use ($userId) {
                $query->where('userID1', $userId)
                    ->orWhere('userID2', $userId);
            })
                ->where('status', 'pending')
                ->get()
                ->flatMap(function ($friendship) use ($userId) {
                    return [$friendship->userID1, $friendship->userID2];
                })
                ->unique()
                ->toArray();

            // Excluir o usuário logado e os usuários com amizades pendentes dos resultados da pesquisa
            $users = User::where('interesses', 'like', '%' . $interests . '%')
                ->where('id', '!=', $userId)
                ->whereNotIn('id', $pendingFriendships)
                ->get(['id', 'name', 'email']); // Ajuste os campos conforme necessário

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $users
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to search users by interests',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Search users by full name.
     */
    public function searchByName(Request $request)
    {
        try {
            $userId = Auth::id();
            $name = $request->query('name');

            // Obter IDs de usuários com amizades pendentes envolvendo o usuário logado
            $pendingFriendships = Friendship::where(function ($query) use ($userId) {
                $query->where('userID1', $userId)
                    ->orWhere('userID2', $userId);
            })
                ->where('status', 'pending')
                ->get()
                ->flatMap(function ($friendship) use ($userId) {
                    return [$friendship->userID1, $friendship->userID2];
                })
                ->unique()
                ->toArray();

            // Excluir o usuário logado e os usuários com amizades pendentes dos resultados da pesquisa
            $users = User::where('name', 'like', '%' . $name . '%')
                ->where('id', '!=', $userId)
                ->whereNotIn('id', $pendingFriendships)
                ->get(['id', 'name', 'email']); // Ajuste os campos conforme necessário

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $users
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to search users by name',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Get online users.
     */
    public function getOnlineUsers()
    {
        $onlineUsers = User::whereNotNull('id')
            // assuming 5 minutes of inactivity means offline
            ->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $onlineUsers
        ], Response::HTTP_OK);
    }
}
