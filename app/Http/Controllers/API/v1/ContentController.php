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

class ContentController extends Controller
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
            'user_id' => 'required|exists:users,id',
            'club_id' => 'nullable|exists:clubs,id',
            'content' => 'required|string',
            'img' => 'nullable|string',
            'video' => 'nullable|string',
            'link' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['satus' => Response::HTTP_BAD_REQUEST, $validator->errors()]);
        }

        try {
            $user = $request->user();

            //var_dump($user);

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
            dd($e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed stored data to db'
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
            return response()->json(['satus' => Response::HTTP_BAD_REQUEST, $validator->errors()]);
        }
        try {
            $content = Content::where('id', $id)
                ->with(['likes', 'comments'])
                ->withCount(['likes', 'comments'])
                ->get();
            if ($content->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Post Not Found'
                ], Response::HTTP_NOT_FOUND);

            } else {
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'data' => $content->toArray()
                ], Response::HTTP_OK);
            }
        } catch (Exception $e) {
            dd($e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed stored data to db'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showByUserId(Request $request, string $userId)
    {
        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['satus' => Response::HTTP_BAD_REQUEST, $validator->errors()]);
        }

        try {
            $contents = Content::where('user_id', $userId)
                ->with(['likes', 'comments'])
                ->withCount(['likes', 'comments'])
                ->get();

            if ($contents->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'User Does Not have posts yet'
                ], Response::HTTP_NOT_FOUND);

            } else {
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'data' => $contents->toArray()
                ], Response::HTTP_OK);
            }
        } catch (Exception $e) {
            dd($e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed stored data to db'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
