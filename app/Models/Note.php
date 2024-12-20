<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Note extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($note) {
            if (empty($note->id)) { // Sử dụng id làm ULID
                $note->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'notes';
    protected $fillable = [
        'title_note',
        'content_note',
        'cache_time_note',
        'del_flag',
        'document_id',
        'user_id',

    ];

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
}
