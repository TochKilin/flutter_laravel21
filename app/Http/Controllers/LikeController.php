<?php

namespace App\Http\Controllers;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle($postId)
    {
        $userId = Auth::guard('api')->id();

        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $existingLike = Like::where('user_id', $userId)
                             ->where('post_id', $postId)
                             ->first();

        if ($existingLike) {
            $existingLike->delete();
            return response()->json([
                'message' => 'Post unliked successfully',
                'liked' => false
            ], 200);
        } else {
            Like::create([
                'user_id' => $userId,
                'post_id' => $postId,
            ]);
            return response()->json([
                'message' => 'Post liked successfully',
                'liked' => true
            ], 201);
        }
    }

     public function index($postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $likes = Like::with('user:id,name,profile_image')
                      ->where('post_id', $postId)
                      ->latest()
                      ->get();

        return response()->json([
            'message' => 'Likes retrieved successfully',
            'total_likes' => $likes->count(),
            'likes' => $likes
        ], 200);
    }

    public function destroy($id)
    {
        $like = Like::find($id);

        if (!$like) {
            return response()->json(['message' => 'Like not found'], 404);
        }

        if ($like->user_id != Auth::guard('api')->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $like->delete();

        return response()->json([
            'message' => 'Like removed successfully'
        ], 200);
    }
}
