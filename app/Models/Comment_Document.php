<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Comment_Document extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($comment_doc) {
            if (empty($comment_doc->id)) { // Sử dụng id làm ULID
                $comment_doc->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'comments_document';
    protected $fillable = [
        'comment_title',
        'comment_text',
        'del_flag',
        'document_id',
        'user_id',
        'comment_to'
    ];

    //COMMENT - BÌNH LUẬN
    public function comments()
    {
        return $this->hasMany(Comment_Document::class);
    }

    //DOCUMENT
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    //USER
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //COMMENT_TO - BÌNH LUẬN TRẢ LỜI
    public function comment()
    {
        return $this->belongsTo(Comment_Document::class);
    }

}
