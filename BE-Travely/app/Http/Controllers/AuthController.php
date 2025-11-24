<?php

namespace App\Http\Controllers;

use App\Models\Users;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register a new user with JWT
     * UUID userID, bcrypt password
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userName' => 'required|string|max:32',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:6|confirmed',
            'phoneNumber' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create user with default role (role_id = 2 for User, role_id = 1 for Admin)
        $user = Users::create([
            'userName' => $request->userName,
            'email' => $request->email,
            'passWord' => $request->password, // Will be bcrypted by model mutator
            'phoneNumber' => $request->phoneNumber,
            'address' => $request->address,
            'role_id' => 2, // Default: User role
        ]);

        // Generate JWT token
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }

        // Set created_by to self (user created their own account)
        $user->created_by = $user->userName;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'access_token' => $token
            ]
        ], 201);
    }

    /**
     * Login with username/email and password
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string', // Can be username or email
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by username or email
        $user = Users::where('userName', $request->login)
            ->orWhere('email', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->passWord)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Generate JWT token
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token
            ]
        ]);
    }

    /**
     * Login with Google
     * Receives google_id and email from frontend (after Google OAuth)
     */
    public function loginWithGoogle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'google_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by google_id
        $user = Users::where('google_id', $request->google_id)->first();

        // If not found, create new user
        if (!$user) {
            // Generate unique username
            $username = $this->generateUniqueUsername($request->name ?? 'user');

            // Create user with default role (role_id = 2 for User)
            $user = Users::create([
                'userName' => $username,
                'email' => $request->email,
                'google_id' => $request->google_id,
                'passWord' => Hash::make(Str::random(16)), // Random password for social login
                'role_id' => 2, // Default: User role

            ]);

            // Set created_by to self (user created their own account via Google)
            $user->created_by = $user->userName;
            $user->save();
        }

        // Generate JWT token
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Google login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token
            ]
        ]);
    }

    /**
     * Login with Facebook
     * Receives facebook_id and email from frontend (after Facebook OAuth)
     */
    public function loginWithFacebook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'facebook_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by facebook_id
        $user = Users::where('facebook_id', $request->facebook_id)->first();

        // If not found, create new user
        if (!$user) {
            // Generate unique username
            $username = $this->generateUniqueUsername($request->name ?? 'user');

            // Create user with default role (role_id = 2 for User)
            $user = Users::create([
                'userName' => $username,
                'email' => $request->email,
                'facebook_id' => $request->facebook_id,
                'passWord' => Hash::make(Str::random(16)), // Random password for social login
                'role_id' => 2, // Default: User role
            ]);

            // Set created_by to self (user created their own account via Facebook)
            $user->created_by = $user->userName;
            $user->save();
        }

        // Generate JWT token
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Facebook login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token
            ]
        ]);
    }

    /**
     * Facebook OAuth Callback
     * Handles the OAuth code exchange and creates/logs in user
     */
    public function facebookCallback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Exchange code for access token
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://graph.facebook.com/v18.0/oauth/access_token', [
                'form_params' => [
                    'client_id' => config('services.facebook.client_id'),
                    'client_secret' => config('services.facebook.client_secret'),
                    'redirect_uri' => config('app.frontend_url') . '/auth/facebook/callback',
                    'code' => $request->code,
                ]
            ]);

            $tokenData = json_decode($response->getBody(), true);
            $accessToken = $tokenData['access_token'];

            // Get user info from Facebook
            $userInfoResponse = $client->get('https://graph.facebook.com/me', [
                'query' => [
                    'fields' => 'id,name,email',
                    'access_token' => $accessToken,
                ]
            ]);

            $fbUser = json_decode($userInfoResponse->getBody(), true);

            // Find or create user
            $user = Users::where('facebook_id', $fbUser['id'])->first();

            if (!$user) {
                // Check if email already exists
                if (isset($fbUser['email'])) {
                    $existingUser = Users::where('email', $fbUser['email'])->first();
                    if ($existingUser) {
                        // Link Facebook account to existing user
                        $existingUser->facebook_id = $fbUser['id'];
                        $existingUser->save();
                        $user = $existingUser;
                    }
                }

                // Create new user if not found
                if (!$user) {
                    $username = $this->generateUniqueUsername($fbUser['name'] ?? 'user');

                    $user = Users::create([
                        'userName' => $username,
                        'email' => $fbUser['email'] ?? $fbUser['id'] . '@facebook.com',
                        'facebook_id' => $fbUser['id'],
                        'passWord' => Hash::make(Str::random(16)),
                        'role_id' => 2,
                    ]);

                    $user->created_by = $user->userName;
                    $user->save();
                }
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Facebook authentication successful',
                'data' => [
                    'user' => $user,
                    'access_token' => $token
                ]
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Facebook authentication failed',
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during authentication',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout - Invalidate JWT token
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout, please try again'
            ], 500);
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'data' => [
                    'access_token' => $newToken
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token cannot be refreshed'
            ], 401);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function profile()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'userName' => 'sometimes|string|max:32|unique:users,userName,' . $user->userID . ',userID',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $user->userID . ',userID',
                'phoneNumber' => 'nullable|string|max:15',
                'address' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update only provided fields
            $user->update($request->only(['userName', 'email', 'phoneNumber', 'address']));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->passWord)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update password (will be bcrypted by model mutator)
            $user->passWord = $request->new_password;
            $user->save();

            // Invalidate old token and generate new one
            JWTAuth::invalidate(JWTAuth::getToken());
            $newToken = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
                'data' => [
                    'access_token' => $newToken
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }
    }

    /**
     * Generate unique username from name
     */
    private function generateUniqueUsername($name)
    {
        // Remove special characters and spaces
        $baseUsername = preg_replace('/[^a-zA-Z0-9]/', '', $name);
        $baseUsername = strtolower($baseUsername);

        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }

        // Check if username exists
        $username = $baseUsername;
        $counter = 1;

        while (Users::where('userName', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Check if user is admin (role_id = 1)
     */
    public function isAdmin()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'is_admin' => $user->role_id === 1
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }
    }
}
