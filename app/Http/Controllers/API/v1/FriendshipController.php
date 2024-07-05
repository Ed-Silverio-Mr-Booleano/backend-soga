<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FriendshipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id2' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $comment = Friendship::create([
                'userID1' => Auth::id(),
                'userID2' => $request['user_id2'],
                'status' => 'pending',
            ]);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Friendship sent successfully',
                'data' => $comment
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to send Friendship',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,accepted,rejected,blocked',
            'user_id1' => 'required|exists:users,id',
            'filter' => 'required|in:pending,accepted,rejected,blocked',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => Response::HTTP_BAD_REQUEST, 'errors' => $validator->errors()]);
        }




        try {
            $friendship = Friendship::where([
                'userID1' => $request['user_id1'],
                'userID2' => Auth::id(),
                'status' => $request->query('filter'),
            ])->first();

            if (!$friendship) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Friend not found'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($friendship->userID2 != Auth::id()) {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'You are not authorized to update this content'
                ], Response::HTTP_FORBIDDEN);
            }

            $friendship->update([
                'status' => $request['status'],
            ]);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Friendship accepted successfull',
                'data' => $friendship
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to accepted Friendship',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get all accepted friendship requests for the authenticated user.
     */

    public function acceptedRequests()
    {
        try {
            $userId = Auth::id();

            $acceptedFriendships = Friendship::where('userID1', $userId)
                ->orWhere('userID2', $userId)
                ->where('status', 'accepted')
                ->get();

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $acceptedFriendships
            ], Response::HTTP_OK);


        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to getc accepted Friendship',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Get all sent friendship requests by the authenticated user.
     */

    public function sentRequests()
    {
        try {
            $userId = Auth::id();

            $sentFriendships = Friendship::where('userID1', $userId)
                ->where('status', '!=', 'accepted')
                ->with('user2')
                ->get();

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $sentFriendships
            ], Response::HTTP_OK);


        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to getc accepted Friendship',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all friendship requests for the authenticated user with optional status filter.
     */

    public function getFriendRequests(Request $request)
    {
        try {
            $status = $request->query('status', 'pending'); // Obtém o status da query string, padrão para 'pending' se não fornecido

            $friendRequests = Friendship::where('userID2', Auth::id())
                ->where('status', $status)
                ->with('user1') // Inclui dados do usuário que enviou o pedido
                ->get();

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $friendRequests
            ], Response::HTTP_OK);


        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to get request Friendship',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all friends for the authenticated user.
     */

    public function getFriends()
    {
        try {
            $userId = Auth::id();

            $friends = Friendship::where(function ($query) use ($userId) {
                $query->where('userID1', $userId)
                    ->orWhere('userID2', $userId);
            })
                ->where('status', 'accepted')
                ->with(['user1', 'user2']) // Inclui dados dos usuários associados
                ->get()
                ->map(function ($friendship) use ($userId) {
                    // Determina qual usuário é o amigo
                    return $friendship->userID1 == $userId ? $friendship->user2 : $friendship->user1;
                });

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $friends
            ], Response::HTTP_OK);


        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to get request Friendship',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
