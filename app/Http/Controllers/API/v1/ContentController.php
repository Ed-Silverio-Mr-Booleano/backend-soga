<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\API\Auth\AuthController;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $contents = Content::with(['likes', 'comments'])
                ->withCount(['likes', 'comments'])
                ->get();

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $contents->toArray()
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            dd($e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to retrieve contents',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'club_id' => 'nullable|exists:clubs,id',
            'content' => 'required|string',
            'img' => 'nullable|string',
            'video' => 'nullable|string',
            'link' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => Response::HTTP_BAD_REQUEST, 'errors' => $validator->errors()]);
        }

        try {
            $user = $request->user();

            $content = Content::create([
                'user_id' => $user->id,
                'club_id' => $request['club_id'],
                'content' => $request['content'],
                'img' => $request['img'],
                'video' => $request['video'],
                'link' => $request['link'],
                'contentDate' => now(),
            ]);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data stored to db',
                'data' => $content
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to store data to db',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => Response::HTTP_BAD_REQUEST, 'errors' => $validator->errors()]);
        }

        try {
            $content = Content::where('id', $id)
                ->with(['likes', 'comments'])
                ->withCount(['likes', 'comments'])
                ->first();

            if (!$content) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Post Not Found'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $content
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showByUserId(Request $request, string $userId)
    {
        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => Response::HTTP_BAD_REQUEST, 'errors' => $validator->errors()]);
        }

        try {
            $contents = Content::where('user_id', $userId)
                ->with(['likes', 'comments'])
                ->withCount(['likes', 'comments'])
                ->get();

            if ($contents->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'User does not have posts yet'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $contents
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|numeric|exists:contents,id',
            'content' => 'nullable|string',
            'img' => 'nullable|string',
            'video' => 'nullable|string',
            'link' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => Response::HTTP_BAD_REQUEST, 'errors' => $validator->errors()]);
        }

        try {
            $content = Content::find($id);
            $user = $request->user();

            if (!$content) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Content not found'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($content->user_id != $user->id) {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'You are not authorized to update this content'
                ], Response::HTTP_FORBIDDEN);
            }

            $content->update($request->all());

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Content updated successfully',
                'data' => $content
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update content',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:contents,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => Response::HTTP_BAD_REQUEST, 'errors' => $validator->errors()]);
        }

        try {
            $content = Content::find($id);
            $user = $request->user();

            if (!$content) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Content not found'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($content->user_id != $user->id) {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'You are not authorized to delete this content'
                ], Response::HTTP_FORBIDDEN);
            }

            $content->delete();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Content deleted successfully'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete content',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
