<?php

namespace App\Http\Controllers;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class LikeController extends Controller
{
   public function toggle(Request $request, $postId)
{
    $userId = Auth::guard('api')->id();

    $post = Post::find($postId);

    if (!$post) {
        return response()->json(['message' => 'Post not found'], 404);
    }

    $like = Like::where('user_id', $userId)
                ->where('post_id', $postId)
                ->first();

    // ❌ remove like
    if ($request->reaction === 'none') {
        if ($like) {
            $like->delete();
        }

        return response()->json([
            'liked' => false,
            'reaction' => null
        ]);
    }

    // 🔁 update existing reaction
    if ($like) {
        $like->update([
            'reaction' => $request->reaction
        ]);
    } 
    // ➕ create new
    else {
        $like = Like::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'reaction' => $request->reaction
        ]);
    }

    return response()->json([
        'liked' => true,
        'reaction' => $like->reaction
    ]);
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
