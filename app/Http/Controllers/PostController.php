<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Auth;
use App\Models\Post;

class PostController extends Controller
{
   public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $posts = Post::with('user:id,name,profile_image')->latest()->paginate($perPage);

        foreach ($posts as $post) {
            $post->likeCount = $post->likes->count();
            $post->commentCount = $post->comments->count();
            $post->isLiked = $post->likes->contains('user_id', Auth::guard('api')->id());

            unset($post->likes);
            unset($post->comments);
        }

        return response()->json([
            'message' => 'Posts retrieved successfully',
            'posts' => $posts
        ], 200);
    }

    public function show($id)
    {
        $post = Post::with('user')->find($id);

        if ($post != null) {
            $post->likeCount = $post->likes->count();
            $post->commentCount = $post->comments->count();
            $post->isLiked = $post->likes->contains('user_id', Auth::guard('api')->id());

            unset($post->likes);
            unset($post->comments);

            return response()->json([
                'post' => $post
            ], 200);
        } else {
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('post_images', 'public');
        }

        $post = Post::create([
            'caption' => $request->caption,
            'image' => $imagePath,
            'user_id' => Auth::guard('api')->id(),  
        ]);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post
        ], 201);
    }

    // public function post($id)
    // {
    //     $post = Post::with('user:id,name,profile_image')->find($id);

    //     if (!$post) {
    //         return response()->json(['message' => 'Post not found'], 404);
    //     }

    //     return response()->json([
    //         'message' => 'Post retrieved successfully',
    //         'post' => $post
    //     ], 200);
    // }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'caption' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('caption')) {
            $post->caption = $request->caption;
        }

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $post->image = $request->file('image')->store('post_images', 'public');
        }

        $post->save();

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post
        ], 200);
    }

    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ], 200);
    }
}
