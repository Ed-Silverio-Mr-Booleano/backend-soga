<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Like;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    // Método para adicionar um like
    public function store(Request $request)
    {
        $likeableTypes = [
            'content' => 'App\Models\Content',
            'comment' => 'App\Models\Comment',
        ];

        $request['likeable_type'] = $likeableTypes[$request['likeable_type']] ?? $request['likeable_type'];

        $validator = Validator::make($request->all(), [
            'likeable_id' => 'required|numeric',
            'likeable_type' => 'required|string|in:App\Models\Content,App\Models\Comment',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {

            $is_like = Like::where(['user_id'=> Auth::id(),'likeable_id'=> $request['likeable_id'], 'likeable_type' => $request['likeable_type']])->first();
            if ($is_like) { 
                return response()->json(['message'=> 'user already like'], Response::HTTP_FORBIDDEN);
            }
            $like = Like::create([
                'user_id' => Auth::id(),
                'likeable_id' => $request['likeable_id'],
                'likeable_type' => $request['likeable_type'],
            ]);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Like added successfully',
                'data' => $like
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to add like',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Método para remover um like
    public function destroy(Request $request)
    {
        $likeableTypes = [
            'content' => 'App\Models\Content',
            'comment' => 'App\Models\Comment',
        ];

        $request['likeable_type'] = $likeableTypes[$request['likeable_type']] ?? $request['likeable_type'];

        $validator = Validator::make($request->all(), [
            'likeable_id' => 'required|numeric',
            'likeable_type' => 'required|string|in:App\Models\Content,App\Models\Comment',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $like = Like::where([
                ['user_id', '=', Auth::id()],
                ['likeable_id', '=', $request['likeable_id']],
                ['likeable_type', '=', $request['likeable_type']],
            ])->first();

            if (!$like) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Like not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $like->delete();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Like removed successfully'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to remove like',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
