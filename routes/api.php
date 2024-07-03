<?php

use App\Http\Controllers\API\v2\ArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [App\Http\Controllers\API\Auth\AuthController::class, 'logout']);
    Route::post('content', [App\Http\Controllers\API\v1\ContentController::class, 'store']);
});

//Auth routes
Route::post('register', [App\Http\Controllers\API\Auth\AuthController::class, 'register']);
Route::post('login', [App\Http\Controllers\API\Auth\AuthController::class, 'login']);

//Content routes

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('content', [App\Http\Controllers\API\v1\ContentController::class, 'store']);
        Route::get('contents/{id}', [App\Http\Controllers\API\v1\ContentController::class, 'show']);
        Route::get('user-contents/{userId}', [App\Http\Controllers\API\v1\ContentController::class, 'showByUserId']);
        Route::get('feed-content', [App\Http\Controllers\API\v1\ContentController::class, 'index']);
        Route::delete('content/{id}', [App\Http\Controllers\API\v1\ContentController::class, 'destroy']);
        Route::put('content/{id}', [App\Http\Controllers\API\v1\ContentController::class, 'update']);
    });

});

//grantir que a aplicação não pare
Route::get('unauthenticated', [App\Http\Controllers\API\v2\ArticleController::class, 'unauthenticated'])->name('guest');
Route::delete('unauthenticated', [App\Http\Controllers\API\v2\ArticleController::class, 'unauthenticated'])->name('guest');
Route::put('unauthenticated', [App\Http\Controllers\API\v2\ArticleController::class, 'unauthenticated'])->name('guest');

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
});*/
