<?php

use App\Http\Controllers\API\v2\ArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Rotas protegidas por Sanctum
Route::middleware([EnsureFrontendRequestsAreStateful::class, 'auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [App\Http\Controllers\API\Auth\AuthController::class, 'logout']);
});

// Rotas de autenticação
Route::post('register', [App\Http\Controllers\API\Auth\AuthController::class, 'register']);
Route::post('login', [App\Http\Controllers\API\Auth\AuthController::class, 'login']);

Route::prefix('v1')->group(function () {
    Route::middleware([EnsureFrontendRequestsAreStateful::class, 'auth:sanctum'])->group(function () {
        // Content routes
        Route::post('content', [App\Http\Controllers\API\v1\ContentController::class, 'store']);
        Route::get('contents/{id}', [App\Http\Controllers\API\v1\ContentController::class, 'show']);
        Route::get('user-contents/{userId}', [App\Http\Controllers\API\v1\ContentController::class, 'showByUserId']);
        Route::get('feed-content', [App\Http\Controllers\API\v1\ContentController::class, 'index']);
        Route::delete('content/{id}', [App\Http\Controllers\API\v1\ContentController::class, 'destroy']);
        Route::put('content/{id}', [App\Http\Controllers\API\v1\ContentController::class, 'update']);

        // Comment routes
        Route::post('comment', [App\Http\Controllers\API\v1\CommentController::class, 'store']);
        Route::get('comments', [App\Http\Controllers\API\v1\CommentController::class, 'index']);

        // Friendship routes
        Route::post('friendship', [App\Http\Controllers\API\v1\FriendshipController::class, 'store']);
        Route::delete('unfriendship', [App\Http\Controllers\API\v1\FriendshipController::class, 'destroy']);
        Route::put('accept-friendship-request', [App\Http\Controllers\API\v1\FriendshipController::class, 'update']);
        Route::get('accept-friendship-request', [App\Http\Controllers\API\v1\FriendshipController::class, 'show']);
        Route::get('friendships/requests', [App\Http\Controllers\API\v1\FriendshipController::class, 'getFriendRequests']);
        Route::get('friendships/accepted', [App\Http\Controllers\API\v1\FriendshipController::class, 'acceptedRequests']);
        Route::get('friendships/sent', [App\Http\Controllers\API\v1\FriendshipController::class, 'sentRequests']);
        Route::get('friends', [App\Http\Controllers\API\v1\FriendshipController::class, 'getFriends']);

        // User routes
        Route::put('user/profile', [App\Http\Controllers\API\v1\UserController::class, 'updateProfile']);
        Route::get('users/search/interests', [App\Http\Controllers\API\v1\UserController::class, 'searchByInterests']);
        Route::get('users/search/name', [App\Http\Controllers\API\v1\UserController::class, 'searchByName']);
        Route::get('users/online', [App\Http\Controllers\API\v1\UserController::class, 'getOnlineUsers']);

        // Message routes
        Route::post('messages', [App\Http\Controllers\API\v1\MessageController::class, 'store']);
        Route::get('messages/user/{id}', [App\Http\Controllers\API\v1\MessageController::class, 'messagesByUser']);
        Route::post('messages/between-users', [App\Http\Controllers\API\v1\MessageController::class, 'messagesBetweenUsers']);
        Route::delete('messages/{id}', [App\Http\Controllers\API\v1\MessageController::class, 'destroy']);

        // Like routes
        Route::post('like', [App\Http\Controllers\API\v1\LikeController::class, 'store']);
        Route::delete('unlike', [App\Http\Controllers\API\v1\LikeController::class, 'destroy']);

        // Notification routes
    });
});

// Rotas fallback para não parar a aplicação
Route::get('unauthenticated', [ArticleController::class, 'unauthenticated'])->name('guest');
Route::delete('unauthenticated', [ArticleController::class, 'unauthenticated'])->name('guest');
Route::put('unauthenticated', [ArticleController::class, 'unauthenticated'])->name('guest');

/*
Route::prefix('v1')->group(function () {
    Route::get('list-articles', [App\Http\Controllers\API\v1\ArticleController::class, 'index']);
    Route::post('store-article', [App\Http\Controllers\API\v1\ArticleController::class, 'store']);
    Route::get('read-article/{id}', [App\Http\Controllers\API\v1\ArticleController::class, 'show']);
    Route::put('update-article/{id}', [App\Http\Controllers\API\v1\ArticleController::class, 'update']);
    Route::delete('delete-article/{id}', [App\Http\Controllers\API\v1\ArticleController::class, 'destroy']);
    Route::get('article/search', [App\Http\Controllers\API\v1\ArticleController::class, 'index']);
});

Route::prefix('v2')->group(function () {
    Route::get('list-articles', [App\Http\Controllers\API\v2\ArticleController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function(){
        Route::resource('article', ArticleController::class);
    });

    Route::get('unauthenticated',[App\Http\Controllers\API\v2\ArticleController::class,'unauthenticated'])->name('guest');
});
*/
