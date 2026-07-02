<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index($postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comments = Comment::with('user:id,name,profile_image')
                            ->where('post_id', $postId)
                            ->latest()
                            ->get();

        return response()->json([
            'message' => 'Comments retrieved successfully',
            'total_comments' => $comments->count(),
            'comments' => $comments
        ], 200);
    }

    public function store(Request $request, $postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $comment = Comment::create([
            'user_id' => Auth::guard('api')->id(),
            'post_id' => $postId,
            'text' => $request->text,
        ]);

        $comment->load('user:id,name,profile_image');

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment
        ], 201);
    }

    public function show($id)
    {
        $comment = Comment::with('user:id,name,profile_image')->find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        return response()->json([
            'message' => 'Comment retrieved successfully',
            'comment' => $comment
        ], 200);
    }

    // កែប្រែ comment
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        // ត្រូវប្រាកដថាតែម្ចាស់ comment ប៉ុណ្ណោះអាចកែ
        if ($comment->user_id != Auth::guard('api')->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $comment->text = $request->text;
        $comment->save();

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment
        ], 200);
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        if ($comment->user_id != Auth::guard('api')->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully'
        ], 200);
    }

}
