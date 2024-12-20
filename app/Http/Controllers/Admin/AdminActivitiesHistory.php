<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminAllHistory;
use App\Models\Activity_History;
use Illuminate\Http\Request;

class AdminActivitiesHistory extends Controller
{
    public function getAllHistory() {
        try {
            $comments_post = Activity_History::all();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminAllHistory::collection($comments_post),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
