<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;

class CommentController extends Controller
{
    // Método para listar todos os comentários de um conteúdo específico
    public function index($content_id)
    {
        try {
            $comments = Comment::where('content_id', $content_id)->with('user')->get();

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => $comments
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to retrieve comments',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Método para criar um novo comentário
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content_id' => 'required|exists:contents,id',
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $comment = Comment::create([
                'user_id' => Auth::id(),
                'content_id' => $request['content_id'],
                'comment' => $request['comment'],
                'commentDate' => now(),
            ]);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Comment created successfully',
                'data' => $comment
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to create comment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Método para visualizar um comentário específico
    public function show($id)
    {
        try {
            $comment = Comment::with('user')->find($id);
            if ($comment) {
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'data' => $comment
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Comment not found'
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to retrieve comment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Método para atualizar um comentário
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $comment = Comment::find($id);
            $user = $request->user();
            if ($comment && $comment->user_id == $user->id) {
                $comment->update([
                    'comment' => $request['comment'],
                ]);

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => 'Comment updated successfully',
                    'data' => $comment
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'You are not authorized to update this comment'
                ], Response::HTTP_FORBIDDEN);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update comment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Método para deletar um comentário
    public function destroy(Request $request, $id)
    {
        try {
            $comment = Comment::find($id);
            $user = $request->user();
            if ($comment && $comment->user_id == $user->id) {
                $comment->delete();

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => 'Comment deleted successfully'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'You are not authorized to delete this comment'
                ], Response::HTTP_FORBIDDEN);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete comment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
