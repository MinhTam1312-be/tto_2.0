<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Laravel\Socialite\Facades\Socialite;

class SocialApiController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Xử lý callback từ Google
    public function callback()
    {
        try {
            $getInfo = Socialite::driver('google')->user();
            $existingUser = User::where('email', $getInfo->email)->first();

            // Kiểm tra nếu email đã tồn tại
            if ($existingUser) {
                if ($existingUser->provider_id) {
                    // Đăng nhập người dùng và tạo token JWT
                    auth()->login($existingUser);
                    $token = JWTAuth::fromUser($existingUser);

                    return $this->respondWithToken($token);
                }
                return response()->json(['error' => 'Email đã được sử dụng, không thể đăng nhập với Google'], 403);
            }

            $user = $this->createUser($getInfo, 'google');

            // Đăng nhập người dùng và tạo token JWT
            auth()->login($user);
            $token = JWTAuth::fromUser($user);

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            // Xử lý ngoại lệ
            return response()->json(['error' => 'Đăng nhập thất bại, vui lòng thử lại.'], 500);
        }
    }
    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGithubCallback(): JsonResponse
    {
        try {
            // Lấy thông tin người dùng từ GitHub
            $getInfo = Socialite::driver('github')->user();
            $existingUser = User::where('email', $getInfo->email)->first();

            // Kiểm tra xem email đã tồn tại chưa
            if ($existingUser) {
                // Nếu email đã tồn tại, trả về lỗi
                return response()->json(['error' => 'Email đã được sử dụng, không thể đăng nhập với GitHub'], 403);
            }

            // Tạo người dùng mới
            $user = $this->createUser($getInfo, 'github');

            // Đăng nhập người dùng và tạo token JWT
            auth()->login($user);
            $token = JWTAuth::fromUser($user);

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            // Xử lý ngoại lệ
            return response()->json(['error' => 'Đăng nhập thất bại, vui lòng thử lại.'], 500);
        }
    }
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }
    public function handleFacebookCallback(): JsonResponse
    {
        try {
            // Lấy thông tin người dùng từ GitHub
            $getInfo = Socialite::driver('facebook')->user();
            $existingUser = User::where('email', $getInfo->email)->first();

            // Kiểm tra xem email đã tồn tại chưa
            if ($existingUser) {
                // Nếu email đã tồn tại, trả về lỗi
                return response()->json(['error' => 'Email đã được sử dụng, không thể đăng nhập với GitHub'], 403);
            }

            // Tạo người dùng mới
            $user = $this->createUser($getInfo, 'github');

            // Đăng nhập người dùng và tạo token JWT
            auth()->login($user);
            $token = JWTAuth::fromUser($user);

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            // Xử lý ngoại lệ
            return response()->json(['error' => 'Đăng nhập thất bại, vui lòng thử lại.'], 500);
        }
    }
    function createUser($getInfo, $provider)
    {
        $email = User::where('email', $getInfo->email)->first();
        if ($email) {
            // Nếu email đã tồn tại, trả về lỗi
            return response()->json(['error' => 'Email đã được sử dụng, không thể đăng nhập với GitHub'], 403);
        }
        $user = User::where('provider_id', $getInfo->id)->first();

        if (!$user) {
            $user = User::create([
                'fullname'     => $getInfo->name,
                'email'    => $getInfo->email,
                'del_flag'    => true,
                'provider' => $provider,
                'provider_id' => $getInfo->id
            ]);
        }
        return $user;
    }

    // Phương thức trả về token và thông tin hết hạn
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60 // Thời gian hết hạn tính theo giây
        ]);
    }
}
