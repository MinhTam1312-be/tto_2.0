<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminPostResource;
use App\Models\Post;
use App\Services\LogActivityService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class PostsMarketingApiController extends Controller
{
    public function index()
    {
        try {
            $posts = Post::with('user', 'category')->orderBy('created_at', 'DESC')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminPostResource::collection($posts),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function create()
    {
        //
    }

    // upLoad Image
    public function uploadImage($file)
    {
        // Upload file lên Cloudinary và lấy đường dẫn bảo mật
        $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
            'folder' => 'posts', // Thư mục trên Cloudinary
        ])->getSecurePath();

        return $uploadedFileUrl;
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Lấy thông tin người dùng đăng nhập
            $user = auth('api')->user();

            // Xác thực dữ liệu đầu vào
            $validatedData = $request->validate([
                'title_post' => 'required|string|max:255',
                'content_post' => 'required|string',
                'img_post' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'category_id' => 'required|exists:post_categories,id',
            ], [
                'category_id.required' => 'Vui lòng chọn danh mục bài viết.',
                'category_id.exists' => 'Danh mục không tồn tại.',
            ]);

            // Upload ảnh lên Cloudinary
            $uploadedFileUrl = Cloudinary::upload($request->file('img_post')->getRealPath(), [
                'folder' => 'posts',
                'public_id' => pathinfo($request->file('img_post')->getClientOriginalName(), PATHINFO_FILENAME)
            ])->getSecurePath();

            // Tạo bài viết mới
            $post = Post::create([
                'title_post' => $validatedData['title_post'],
                'content_post' => $validatedData['content_post'],
                'img_post' => $uploadedFileUrl,
                'views_post' => 0,
                'del_flag' => true,
                'user_id' => $user->id,
                'category_id' => $validatedData['category_id'],
            ]);

            // Ghi log chỉ bao gồm tiêu đề bài viết và tên danh mục
            $categoryName = $post->category->name_category;  // Truy xuất tên danh mục bài viết
            LogActivityService::log(
                'tao_bai_viet',
                "Bài viết '{$post->title_post}' đã được tạo thành công trong danh mục '{$categoryName}'",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Bài viết được tạo thành công.',
                'data' => new AdminPostResource($post),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ghi log lỗi xác thực
            LogActivityService::log(
                'tao_bai_viet',
                "Lỗi xác thực khi tạo bài viết: " . json_encode($e->errors(), JSON_UNESCAPED_UNICODE),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Ghi log lỗi hệ thống
            LogActivityService::log(
                'tao_bai_viet',
                "Đã xảy ra lỗi khi tạo bài viết: " . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi trong quá trình tạo bài viết.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $posts = Post::with('user', 'category')->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy chi tiết thành công',
                'data' => new AdminPostResource($posts),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bài viết không được tìm thấy.',
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
    public function update(Request $request, $id)
    {
        try {
            // Tìm bài viết
            $post = Post::findOrFail($id);

            // Xác thực dữ liệu đầu vào
            $validatedData = $request->validate([
                'title_post' => 'nullable|string|max:255',
                'content_post' => 'nullable|string',
                'category_id' => 'nullable|exists:post_categories,id',
            ]);

            // Theo dõi các thay đổi
            $changes = [];
            if (isset($validatedData['title_post']) && $validatedData['title_post'] !== $post->title_post) {
                $changes[] = [
                    'field' => 'title_post',
                    'old_value' => $post->title_post,
                    'new_value' => $validatedData['title_post']
                ];
                $post->title_post = $validatedData['title_post'];
            }

            if (isset($validatedData['content_post']) && $validatedData['content_post'] !== $post->content_post) {
                $changes[] = [
                    'field' => 'content_post',
                    'old_value' => $post->content_post,
                    'new_value' => $validatedData['content_post']
                ];
                $post->content_post = $validatedData['content_post'];
            }

            if (isset($validatedData['category_id']) && $validatedData['category_id'] !== $post->category_id) {
                $changes[] = [
                    'field' => 'category_id',
                    'old_value' => $post->category_id,
                    'new_value' => $validatedData['category_id']
                ];
                $post->category_id = $validatedData['category_id'];
            }

            // Lưu bài viết
            $post->save();

            // Ghi log chi tiết các thay đổi
            foreach ($changes as $change) {
                LogActivityService::log(
                    'thao_tac_cap_nhat_bai_viet',
                    "Đã thay đổi trường '{$change['field']}' từ '{$change['old_value']}' thành '{$change['new_value']}' cho bài viết '{$post->title_post}'.",
                    'success'
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật bài viết thành công.',
                'data' => new AdminPostResource($post),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            LogActivityService::log(
                'thao_tac_cap_nhat_bai_viet',
                'Cập nhật bài viết thất bại do lỗi: ' . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi khi cập nhật bài viết: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Cập nhật ảnh của bài viết
    public function updateImagesPost(Request $request, $post_id)
    {
        try {
            // Tìm bài viết
            $post = Post::findOrFail($post_id);

            // Xác thực file ảnh
            $request->validate([
                'img_post' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240', // Chỉ cần 1 ảnh duy nhất
            ]);

            // Lấy ảnh cũ
            $currentImage = $post->img_post;

            // Lấy file ảnh mới
            $uploadedImage = $request->file('img_post');

            if ($uploadedImage) {
                // Upload ảnh mới lên Cloudinary
                $newImageUrl = Cloudinary::upload($uploadedImage->getRealPath(), [
                    'folder' => 'posts', // Thư mục lưu trữ trên Cloudinary
                    'public_id' => pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME) // Tên ảnh tùy chỉnh
                ])->getSecurePath(); // Lấy đường dẫn bảo mật của ảnh

                // Cập nhật ảnh nếu có thay đổi
                if ($newImageUrl !== $currentImage) {
                    $post->update([
                        'img_post' => $newImageUrl, // Lưu ảnh mới
                    ]);

                    // Ghi log chỉ khi có thay đổi
                    LogActivityService::log(
                        'cap_nhat_anh_bai_viet',
                        "Cập nhật ảnh bài viết '{$post->title_post}' thành công, Old Image: {$currentImage}, New Image: {$newImageUrl}",
                        'success'
                    );
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Cập nhật ảnh bài viết thành công.',
                    'data' => $newImageUrl,
                ], 200);
            }

            // Trường hợp không có ảnh mới
            return response()->json([
                'status' => 'fail',
                'message' => 'Không có ảnh mới được gửi lên.',
            ], 422);

        } catch (\Exception $e) {
            // Log lỗi khi thay đổi ảnh không thành công
            LogActivityService::log(
                'cap_nhat_anh_bai_viet',
                "Cập nhật ảnh bài viết '{$post->title_post}' không thành công.",
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi khi cập nhật ảnh: ' . $e->getMessage(),
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

    // Chức năng ẩn hiện bài viết
    public function statusPost($post_id)
    {
        try {
            // Tìm bài viết theo ID, nếu không có sẽ throw ModelNotFoundException
            $post = Post::findOrFail($post_id);

            // Cập nhật trạng thái del_flag (ẩn hoặc hiện bài viết)
            $post->update(['del_flag' => !$post->del_flag]);

            // Ghi log trạng thái thay đổi thành công
            LogActivityService::log(
                'thay_doi_trang_thai_bai_viet',
                "Đã thay đổi trạng thái bài viết '{$post->title_post}  thành " . ($post->del_flag ? 'hiện' : 'ẩn') . ".",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Đã thay đổi trạng thái bài viết thành công.',
                'data' => new AdminPostResource($post),
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
                'thay_doi_trang_thai_bai_viet',
                'Thay đổi trạng thái bài viết thất bại: ' . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi khi thay đổi trạng thái bài viết.',
                'data' => null,
            ], 500);
        }
    }
}
