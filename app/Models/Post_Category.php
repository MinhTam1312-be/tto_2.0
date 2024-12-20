<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Post_Category extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        
        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($post_category) {
            if (empty($post_category->id)) { // Giả sử bạn có cột uuid
                $post_category->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'post_categories';
    protected $fillable = [
        'name_category',
        'tags',
        'del_flag',
    ];

    //POST
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
