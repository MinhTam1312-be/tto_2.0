<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Kiểm tra và xác thực token
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Token không hợp lệ'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token không tồn tại hoặc không thể xác thực'], 401);
        }

        // Gán thông tin người dùng vào request để có thể sử dụng ở những nơi khác
        $request->attributes->set('user', $user);

        return $next($request);
    }
}
