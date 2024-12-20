<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ImageApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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



    public function uploadImage(Request $request): JsonResponse
    {
        try {
            // Xác thực dữ liệu đầu vào
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            ], [
                'image.required' => 'Ảnh đại diện là bắt buộc.',
                'image.image' => 'Tệp tải lên phải là một hình ảnh.',
                'image.mimes' => 'Ảnh đại diện chỉ được phép có định dạng jpeg, png, jpg, hoặc gif.',
                'image.max' => 'Kích thước ảnh đại diện tối đa là 2MB.',
            ]);

            // Xử lý file ảnh
            if ($request->hasFile('image')) {
                // Lấy file ảnh
                $file = $request->file('image');

                // Upload ảnh lên Cloudinary
                $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                    'folder' => 'fe_image', // Thư mục lưu trữ trên Cloudinary
                    'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) // Sử dụng tên gốc
                ])->getSecurePath(); // Lấy URL của ảnh đã upload

                return response()->json([
                    'message' => 'Tải ảnh đại diện lên thành công.',
                    'image' => $uploadedFileUrl, // Trả về URL ảnh đại diện mới
                ], 200);
            }

            return response()->json([
                'message' => 'Không có ảnh để tải lên.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getImage($filename)
    {
        // Các đuôi ảnh có thể có
        $extensions = ['jpeg', 'png', 'jpg', 'gif'];

        // Đường dẫn gốc tới Cloudinary
        $baseUrl = "https://res.cloudinary.com/dnmc89c8b/image/upload/v1730795431/fe_image/";

        // Tạo các URL cho tất cả các đuôi ảnh
        $urls = [];
        foreach ($extensions as $extension) {
            $urls[] = $baseUrl . "{$filename}.{$extension}";
        }

        // Giả định rằng ít nhất một ảnh sẽ tồn tại
        // Trả về URL đầu tiên trong danh sách
        return response()->json(['url' => $urls[0]]);
    }

}