<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /**
     * Display a listing of all messages.
     */
    public function index()
    {
        $messages = Message::with(['sender', 'receiver'])->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $messages
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiverID' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $message = Message::create([
                'senderID' => Auth::id(),
                'receiverID' => $request->input('receiverID'),
                'message' => $request->input('message'),
                'sentDate' => now(),
            ]);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Message sent successfully',
                'data' => $message
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $message = Message::with(['sender', 'receiver'])->find($id);

        if (!$message) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Message not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $message
        ], Response::HTTP_OK);
    }

    /**
     * Display all messages between two users.
     */
    public function messagesBetweenUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userID1' => 'required|exists:users,id',
            'userID2' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $messages = Message::where(function ($query) use ($request) {
            $query->where('senderID', $request->input('userID1'))
                ->where('receiverID', $request->input('userID2'));
        })->orWhere(function ($query) use ($request) {
            $query->where('senderID', $request->input('userID2'))
                ->where('receiverID', $request->input('userID1'));
        })->with(['sender', 'receiver'])->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $messages
        ], Response::HTTP_OK);
    }

    /**
     * Display all messages sent or received by a user.
     */
    public function messagesByUser(Request $request, string $id)
    {
        $messages = Message::where('senderID', $id)
            ->orWhere('receiverID', $id)
            ->with(['sender', 'receiver'])
            ->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $messages
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $message = Message::findOrFail($id);

            if ($message->senderID != Auth::id()) {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'You are not authorized to delete this message'
                ], Response::HTTP_FORBIDDEN);
            }

            $message->delete();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Message deleted successfully'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete message',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
