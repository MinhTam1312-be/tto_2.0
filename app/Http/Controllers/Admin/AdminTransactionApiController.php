<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminTransactionResource;
use App\Models\ActivityLog;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AdminTransactionApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $transactions = Transaction::with('user', 'enrollment')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminTransactionResource::collection($transactions),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $transation = Transaction::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => new AdminTransactionResource($transation),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Danh mục không được tìm thấy.',
            ], 404); // Trả về mã lỗi 404
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function getActivity($role = null, $status = null, $orderByDate = null)
    {
        try {
            $query = ActivityLog::query(); // Start a query builder instance

            // Filter by role
            switch ($role) {
                case 'marketing':
                    $query->where('role', 'marketing');
                    break;
                case 'instructor':
                    $query->where('role', 'instructor');
                    break;
                case 'accountant':
                    $query->where('role', 'accountant');
                    break;
                case 'admin':
                    $query->where('role', 'admin');
                    break;
            }

            // Filter by status
            if ($status === 'success') {
                $query->where('status', 'success');
            } elseif ($status === 'fail') {
                $query->where('status', 'fail');
            }

            // Order by date
            if ($orderByDate === 'asc') {
                $query->orderBy('created_at', 'asc');
            } elseif ($orderByDate === 'desc') {
                $query->orderBy('created_at', 'desc');
            }

            // Execute query
            $activity = $query->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => $activity,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Danh mục không được tìm thấy.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    public function getActivitySearch($search)
    {
        try {
            $activity = ActivityLog::where('fullname', 'ilike', '%' . $search . '%')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => $activity,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Danh mục không được tìm thấy.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
