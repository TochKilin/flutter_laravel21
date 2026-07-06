<?php

namespace App\Http\Controllers;
// use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; 

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // អតិបរមា 2MB
        ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    $imagePath = null;
    if ($request->hasFile('profile_image')) {
        $imagePath = $request->file('profile_image')->store('profile_images', 'public');
    }

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password), // កុំភ្លេចលាក់ Password (Hash)
        'profile_image' => $imagePath,
    ]);

    $token = $user->createToken('salaitapp')->accessToken;

    return response()->json([
        'message' => 'User registered successfully',
        'user' => $user,
        'token' => $token
    ], 201);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password. Please try again.'
            ], 401);
        }
        $token = $user->createToken('salaitapp')->accessToken;

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token
        ], 200);
    }
    public function index()
    {
        $users = User::all();

        return response()->json([
            'message' => 'Users retrieved successfully',
            'users' => $users
        ], 200);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'message' => 'User retrieved successfully',
            'user' => $user
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $imagePath;
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200);
    }


    public function delete(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }

    public function me(){
        return response()->json(["user"=> Auth::user()]);
    }


    public function loggout(){
        $user = Auth::user()->token();
        $user->token();
        return response()->json([
            "message" => "User logged out successfully"
        ],200);
    }


}
