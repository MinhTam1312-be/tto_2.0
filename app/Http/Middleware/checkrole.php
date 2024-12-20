<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class checkrole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Lấy người dùng đã xác thực
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['message' => 'Người dùng chưa được xác thực.'], 401);
        }
        // Kiểm tra xem người dùng có tồn tại và có quyền phù hợp không
        if ($user &&  in_array($user->role, $roles)) {
            return $next($request);
        }

        // Nếu không, trả về thông báo không được phép
        return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
    }
}
