<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminFavorite_CourseResource;
use App\Models\Favorite_Course;
use Illuminate\Http\Request;

class AdminFavorite_CourseApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $favorite_courses = Favorite_Course::with('user', 'comment')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminFavorite_CourseResource::collection($favorite_courses),
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
        //
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
}
