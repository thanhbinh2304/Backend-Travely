<?php

namespace App\Http\Controllers;

use App\Models\Users;
use App\Models\Role;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        // Generate verification token
        $verificationToken = Str::random(64);

        // Create user with default role (role_id = 2 for User, role_id = 1 for Admin)
        $user = Users::create([
            'userName' => $request->userName,
            'email' => $request->email,
            'passWord' => $request->password, // Will be bcrypted by model mutator
            'phoneNumber' => $request->phoneNumber,
            'address' => $request->address,
            'role_id' => 2, // Default: User role
            'email_verified' => false,
            'verification_token' => $verificationToken,
            'verification_token_expires_at' => now()->addHours(24), // Token expires in 24 hours
        ]);

        // Set created_by to self (user created their own account)
        $user->created_by = $user->userName;
        $user->save();

        // Send verification email
        try {
            $verificationUrl = config('app.frontend_url') . '/auth/verify-email?token=' . $verificationToken;
            $user->notify(new VerifyEmailNotification($verificationUrl));
            Log::info('Verification email sent to: ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Please check your email to verify your account.',
            'data' => [
                'user' => $user,
                'requires_verification' => true,
                // Remove in production:
                'debug' => [
                    'verification_url' => $verificationUrl ?? null,
                    'token' => $verificationToken
                ]
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

        // Check if email is verified
        if (!$user->email_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before logging in. Check your email for the verification link.',
                'requires_verification' => true,
                'email' => $user->email
            ], 403);
        }

        // Update last login timestamp
        $user->last_login = now();

        // Generate JWT token
        try {
            $token = JWTAuth::fromUser($user);

            // Generate and store refresh token
            $refreshToken = Str::random(64);
            $user->refresh_token = $refreshToken;
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'refresh_token' => $refreshToken
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
                'email_verified' => true, // Auto-verify email for Google OAuth

            ]);

            // Set created_by to self (user created their own account via Google)
            $user->created_by = $user->userName;
            $user->save();
        } else {
            // If user exists but email is not verified, verify it
            if (!$user->email_verified) {
                $user->email_verified = true;
                $user->save();
            }
        }

        // Update last login timestamp
        $user->last_login = now();

        // Generate JWT token
        try {
            $token = JWTAuth::fromUser($user);

            // Generate and store refresh token
            $refreshToken = Str::random(64);
            $user->refresh_token = $refreshToken;
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Google login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'refresh_token' => $refreshToken
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
                'email_verified' => true, // Auto-verify email for Facebook OAuth
            ]);

            // Set created_by to self (user created their own account via Facebook)
            $user->created_by = $user->userName;
            $user->save();
        } else {
            // If user exists but email is not verified, verify it
            if (!$user->email_verified) {
                $user->email_verified = true;
                $user->save();
            }
        }

        // Update last login timestamp
        $user->last_login = now();

        // Generate JWT token
        try {
            $token = JWTAuth::fromUser($user);

            // Generate and store refresh token
            $refreshToken = Str::random(64);
            $user->refresh_token = $refreshToken;
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Facebook login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'refresh_token' => $refreshToken
            ]
        ]);
    }

    /**
     * Refresh access token using refresh token
     */
    public function refresh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by refresh token
        $user = Users::where('refresh_token', $request->refresh_token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid refresh token'
            ], 401);
        }

        // Generate new JWT access token
        try {
            $token = JWTAuth::fromUser($user);

            // Generate new refresh token
            $newRefreshToken = Str::random(64);
            $user->refresh_token = $newRefreshToken;
            $user->save();
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'access_token' => $token,
                'refresh_token' => $newRefreshToken
            ]
        ]);
    }

    /**
     * Google OAuth Callback
     * Handles the OAuth code exchange and creates/logs in user
     */
    public function googleCallback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
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
            $response = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'redirect_uri' => config('app.frontend_url') . '/auth/google/callback',
                    'code' => $request->code,
                    'grant_type' => 'authorization_code',
                ]
            ]);

            $tokenData = json_decode($response->getBody(), true);
            $accessToken = $tokenData['access_token'];

            // Get user info from Google
            $userInfoResponse = $client->get('https://www.googleapis.com/oauth2/v2/userinfo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ]
            ]);

            $googleUser = json_decode($userInfoResponse->getBody(), true);

            // Find or create user
            $user = Users::where('google_id', $googleUser['id'])->first();

            if (!$user) {
                // Check if email already exists
                if (isset($googleUser['email'])) {
                    $existingUser = Users::where('email', $googleUser['email'])->first();
                    if ($existingUser) {
                        // Link Google account to existing user
                        $existingUser->google_id = $googleUser['id'];
                        $existingUser->email_verified = true;
                        $existingUser->save();
                        $user = $existingUser;
                    }
                }

                // Create new user if not found
                if (!$user) {
                    $username = $this->generateUniqueUsername($googleUser['name'] ?? 'user');

                    $user = Users::create([
                        'userName' => $username,
                        'email' => $googleUser['email'] ?? $googleUser['id'] . '@google.com',
                        'google_id' => $googleUser['id'],
                        'passWord' => Hash::make(Str::random(16)),
                        'role_id' => 2,
                        'email_verified' => true, // Auto-verify for Google OAuth
                    ]);

                    $user->created_by = $user->userName;
                    $user->save();
                }
            } else {
                // If user exists but email is not verified, verify it
                if (!$user->email_verified) {
                    $user->email_verified = true;
                    $user->save();
                }
            }

            // Update last login
            $user->last_login = now();

            // Generate JWT token and refresh token
            $token = JWTAuth::fromUser($user);
            $refreshToken = Str::random(64);
            $user->refresh_token = $refreshToken;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Google authentication successful',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'refresh_token' => $refreshToken
                ]
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed',
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
     * Forgot Password - Send reset token via email
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by email
        $user = Users::where('email', $request->email)->first();

        if (!$user) {
            // Return success even if user not found (security best practice)
            return response()->json([
                'success' => true,
                'message' => 'If the email exists, a password reset link has been sent'
            ]);
        }

        // Generate reset token
        $resetToken = Str::random(64);
        $user->verification_token = $resetToken;
        $user->verification_token_expires_at = now()->addHours(1); // Token expires in 1 hour
        $user->save();

        // Send email with reset link
        try {
            $resetUrl = config('app.frontend_url') . '/auth/reset-password?token=' . $resetToken;
            $user->notify(new ResetPasswordNotification($resetUrl));
            Log::info('Password reset email sent to: ' . $user->email);

            return response()->json([
                'success' => true,
                'message' => 'If the email exists, a password reset link has been sent',
                // Remove in production:
                'debug' => [
                    'reset_url' => $resetUrl,
                    'token' => $resetToken
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send reset email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset email'
            ], 500);
        }
    }

    /**
     * Verify Email - Confirm user's email address
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by verification token
        $user = Users::where('verification_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification token'
            ], 400);
        }

        // Check if token is expired
        if ($user->verification_token_expires_at && $user->verification_token_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Verification token has expired. Please request a new one.',
                'expired' => true
            ], 400);
        }

        // Verify email
        $user->email_verified = true;
        $user->verification_token = null;
        $user->verification_token_expires_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully! You can now log in.'
        ]);
    }

    /**
     * Resend Verification Email
     */
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by email
        $user = Users::where('email', $request->email)->first();

        if (!$user) {
            // Return success even if user not found (security best practice)
            return response()->json([
                'success' => true,
                'message' => 'If the email exists, a new verification link has been sent'
            ]);
        }

        // Check if already verified
        if ($user->email_verified) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already verified'
            ], 400);
        }

        // Generate new verification token
        $verificationToken = Str::random(64);
        $user->verification_token = $verificationToken;
        $user->verification_token_expires_at = now()->addHours(24);
        $user->save();

        // Send verification email
        try {
            $verificationUrl = config('app.frontend_url') . '/auth/verify-email?token=' . $verificationToken;
            $user->notify(new VerifyEmailNotification($verificationUrl));
            Log::info('Verification email resent to: ' . $user->email);

            return response()->json([
                'success' => true,
                'message' => 'If the email exists, a new verification link has been sent',
                // Remove in production:
                'debug' => [
                    'verification_url' => $verificationUrl,
                    'token' => $verificationToken
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email'
            ], 500);
        }
    }

    /**
     * Reset Password - Verify token and update password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by reset token
        $user = Users::where('verification_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token'
            ], 400);
        }

        // Check if token is expired
        if ($user->verification_token_expires_at && $user->verification_token_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Reset token has expired'
            ], 400);
        }

        // Update password
        $user->passWord = $request->password; // Will be bcrypted by model mutator
        $user->verification_token = null;
        $user->verification_token_expires_at = null;
        $user->email_verified = true; // Auto-verify email when resetting password
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. You can now login with your new password'
        ]);
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
