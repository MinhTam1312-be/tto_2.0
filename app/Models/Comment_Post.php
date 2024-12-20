<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Comment_Post extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($comment_post) {
            if (empty($comment_post->id)) { // Sử dụng id làm ULID
                $comment_post->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'comments_post';
    protected $fillable = [
        'comment_text',
        'user_id',
        'post_id',
        'comment_to',
        'del_flag'
    ];
    
    //COMMENT - BÌNH LUẬN
    public function comments()
    {
        return $this->hasMany(Comment_Post::class);
    }

    //USER
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //POST
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    //COMMENT_TO - BÌNH LUẬN TRẢ LỜI
    public function comment()
    {
        return $this->belongsTo(Comment_Post::class);
    }
}
