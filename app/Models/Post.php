<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Xử lý khi tạo mới
        static::creating(function ($post) {
            // Tạo ULID nếu chưa có
            if (empty($post->id)) {
                $post->id = (string) Str::ulid();
            }

            // Tạo slug khi tạo bài viết mới
            if (empty($post->slug_post) && !empty($post->title_post)) {
                $post->slug_post = self::generateUniqueSlug($post->title_post);
            }
        });

        // Xử lý khi cập nhật
        static::updating(function ($post) {
            if ($post->isDirty('title_post')) {
                $post->slug_post = self::generateUniqueSlug($post->title_post, $post->id);
            }
        });
    }

    /**
     * Hàm tạo slug duy nhất cho bài viết
     *
     * @param string $title_post
     * @param string|null $ignoreId
     * @return string
     */
    public static function generateUniqueSlug($title_post, $ignoreId = null)
    {
        $slug = Str::slug($title_post, '-');
        $originalSlug = $slug;
        $count = 1;

        // Kiểm tra trùng lặp slug
        while (
            self::where('slug_post', $slug)
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
    use HasFactory;
    protected $table = 'posts';
    protected $fillable = [
        'title_post',
        'slug_post',
        'content_post',
        'img_post',
        'views_post',
        'status_post',
        'del_flag',
        'user_id',
        'category_id',
    ];

    //COMMENT_POST
    public function comments_post()
    {
        return $this->hasMany(Comment_Post::class);
    }

    //USER
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //CATEGORY
    public function category()
    {
        return $this->belongsTo(Post_Category::class);
    }
}
