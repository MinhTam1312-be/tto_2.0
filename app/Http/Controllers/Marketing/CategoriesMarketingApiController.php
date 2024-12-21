<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminPost_CategoryResource;
use App\Models\Post_Category;
use App\Services\LogActivityService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CategoriesMarketingApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $user; // Định nghĩa thuộc tính cho class

    public function __construct()
    {
        $this->user = auth('api')->user(); // Khởi tạo thuộc tính trong constructor
    }

    public function index()
    {
        try {
            $post_categories = Post_Category::get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminPost_CategoryResource::collection($post_categories),
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
        try {
            // Xác thực dữ liệu đầu vào
            $validatedData = $request->validate([
                'name_category' => 'required|unique:post_categories,name_category|max:255',
                'tags' => 'required|unique:post_categories,tags',
            ], [
                'name_category.required' => 'Danh mục không được bỏ trống',
                'name_category.unique' => 'Danh mục bị trùng',
                'name_category.max' => 'Danh mục quá dài',
                'tags.required' => 'Hashtag không được bỏ trống',
                'tags.unique' => 'Hashtag bị trùng',
            ]);

            // Kiểm tra xem danh mục có tồn tại chưa
            $existingCategory = Post_Category::where('name_category', $request->name_category)
                ->orWhere('tags', $request->tags)
                ->first();

            if ($existingCategory) {
                // Nếu danh mục đã tồn tại, không cần tạo mới và trả về thông báo lỗi
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Danh mục hoặc hashtag đã tồn tại.',
                ], 400);
            }

            // Tạo mới danh mục
            $category = Post_Category::create([
                'name_category' => $request->name_category,
                'tags' => $request->tags,
                'del_flag' => true
            ]);

            // Ghi log chỉ khi có sự thay đổi (tạo mới danh mục thành công)
            LogActivityService::log(
                'thay_doi_danh_muc',
                "Đã thêm danh mục '{$category->name_category}' với hashtag '{$category->tags}'",
                'success'
            );

            // Trả về phản hồi
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được thêm thành công',
                'data' => new AdminPost_CategoryResource($category),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            // Xử lý lỗi xác thực
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi
            LogActivityService::log(
                'thay_doi_danh_muc',
                'Thêm danh mục thất bại: ' . $e->getMessage(),
                'fail'
            );
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $post_categories = Post_Category::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => new AdminPost_CategoryResource($post_categories),
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
        try {
            $category = Post_Category::findOrFail($id);

            // Kiểm tra sự thay đổi và xác thực dữ liệu
            $validatedData = $request->validate([
                'name_category' => 'unique:post_categories,name_category|max:255',
            ], [
                'name_category.unique' => 'Danh mục bị trùng',
                'name_category.max' => 'Danh mục quá dài',
            ]);

            $isUpdated = false;

            // Kiểm tra nếu có thay đổi
            if ($request->has('name_category') && $category->name_category != $request->name_category) {
                $category->name_category = $request->name_category;
                $isUpdated = true;
            }

            if ($request->has('tags') && $category->tags != $request->tags) {
                $category->tags = $request->tags;
                $isUpdated = true;
            }

            if ($isUpdated) {
                $category->save();
                LogActivityService::log(
                    'thay_doi_danh_muc',
                    "Đã chỉnh sửa danh mục '{$category->name_category}' với hashtag '{$category->tags}'",
                    'success'
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => $isUpdated ? 'Cập nhật danh mục thành công' : 'Không có thay đổi nào',
                'data' => new AdminPost_CategoryResource($category),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Danh mục không được tìm thấy.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(),
            ], 422);
        } catch (\Exception $e) {
            LogActivityService::log(
                'thay_doi_danh_muc',
                "Chỉnh sửa danh mục thất bại: " . $e->getMessage(),
                'fail'
            );
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Ẩn, hiện danh mục bài viết
    public function statusCategoryPost($category_id)
    {
        try {
            // Tìm danh mục theo ID, nếu không có sẽ throw ModelNotFoundException
            $category = Post_Category::findOrFail($category_id);

            // Kiểm tra nếu trạng thái có thay đổi
            $newDelFlag = !$category->del_flag;

            // Nếu có thay đổi trạng thái
            if ($category->del_flag !== $newDelFlag) {
                // Cập nhật trạng thái del_flag
                $category->update(['del_flag' => $newDelFlag]);

                // Ghi log hoạt động thay đổi trạng thái
                LogActivityService::log(
                    'thay_doi_trang_thai_danh_muc',
                    "Đã thay đổi trạng thái danh mục thành" . ($newDelFlag ? 'hiện' : 'ẩn') . " danh mục '{$category->name_category}",
                    'success'
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Đã thay đổi trạng thái danh mục bài viết thành công',
                'data' => new AdminPost_CategoryResource($category),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi
            LogActivityService::log(
                'thay_doi_trang_thai_danh_muc',
                "Thay đổi trạng thái danh mục '{$category->name_category}' thất bại: " . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
