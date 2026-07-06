<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->put('/users/{id}', [AuthController::class, 'update']);
Route::middleware('auth:api')->delete('/users/{id}', [AuthController::class, 'delete']);
Route::middleware('auth:api')->get('/users', [AuthController::class, 'index']);
Route::middleware('auth:api')->get('/users/{id}', [AuthController::class, 'show']);
Route::middleware('auth:api')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth:api')->get('/loggout/{id}', [AuthController::class, 'loggout']);

Route::middleware('auth:api')->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::post('/posts/{id}', [PostController::class, 'update']); 
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});


Route::middleware('auth:api')->group(function () {
    Route::post('/posts/{postId}/like', [LikeController::class, 'toggle']);   
    Route::get('/posts/{postId}/likes', [LikeController::class, 'index']);    
    Route::delete('/likes/{id}', [LikeController::class, 'destroy']);         
});


Route::middleware('auth:api')->group(function () {
    Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
    Route::get('/comments/{id}', [CommentController::class, 'show']);
    Route::post('/comments/{id}', [CommentController::class, 'update']); // ប្រើ _method=PUT
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});